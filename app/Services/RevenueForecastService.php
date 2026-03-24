<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RevenueForecastService
{
    private $fastApiUrl;

    public function __construct()
    {
        $this->fastApiUrl = env('FASTAPI_URL', 'http://price_api:8000');
    }

    public function generateMonthlyForecast($year = null)
    {
        if (!$year) {
            $year = Carbon::now()->year;
        }

        Log::info("Starting revenue forecast generation for year: {$year}");

        try {
            // Export transaction data to CSV string
            $csvData = $this->exportTransactionDataAsCsv();

            Log::info("Transaction data exported, CSV length: " . strlen($csvData));

            // Call FastAPI endpoint
            $response = Http::timeout(120)
                ->asJson() // 👈 force Laravel to send JSON
                ->post("{$this->fastApiUrl}/api/forecast/revenue", [
                    'csv_data' => $csvData,
                    'year' => $year
                ]);

            if (!$response->successful()) {
                Log::error('FastAPI forecast request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('Forecast service unavailable. Status: ' . $response->status());
            }

            $forecastData = $response->json();

            if (!isset($forecastData['success']) || !$forecastData['success']) {
                $error = $forecastData['detail'] ?? $forecastData['error'] ?? 'Forecast failed';
                throw new \Exception($error);
            }

            Log::info("Forecast generated successfully", [
                'forecast_year' => $forecastData['forecast_year'],
                'total_annual' => $forecastData['total_annual_revenue'],
                'data_points' => $forecastData['data_points_used'] ?? 0
            ]);

            return $forecastData;
        } catch (\Exception $e) {
            Log::error('Revenue forecast generation failed', [
                'error' => $e->getMessage(),
                'year' => $year
            ]);
            throw $e;
        }
    }

    private function exportTransactionDataAsCsv()
    {
        $transactions = Transaction::select([
            'transaction_id',
            'transaction_type',
            'category',
            'transaction_date',
            'amount',
            'reference_number'
        ])
            ->whereRaw('UPPER(transaction_type) = ?', ['CREDIT'])
            ->orderBy('transaction_date')
            ->get();

        Log::info("Exporting {$transactions->count()} inflow transactions for forecasting");

        $output = fopen('php://temp', 'r+');

        // Write header
        fputcsv($output, [
            'transaction_id',
            'transaction_type',
            'category',
            'transaction_date',
            'amount',
            'reference_number'
        ]);

        // Write data
        foreach ($transactions as $transaction) {
            fputcsv($output, [
                $transaction->transaction_id,
                $transaction->transaction_type,
                $transaction->category,
                $transaction->transaction_date->format('Y-m-d'),
                $transaction->amount,
                $transaction->reference_number
            ]);
        }

        rewind($output);
        $csvData = stream_get_contents($output);
        fclose($output);

        return $csvData;
    }
}
