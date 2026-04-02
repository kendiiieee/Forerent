<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsProfileController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'firstName' => 'nullable|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'phoneNumber' => ['required', 'digits:10', Rule::unique('users', 'contact')->ignore($user->user_id, 'user_id')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')],
            'profilePicture' => 'nullable|image|max:10240',
            'governmentIdImage' => 'nullable|image|max:10240',
        ]);

        $phoneNumber = $this->normalizePhone((string) ($validated['phoneNumber'] ?? ''));

        $updateData = [
            'first_name' => trim((string) ($validated['firstName'] ?? '')),
            'last_name' => trim((string) ($validated['lastName'] ?? '')),
            'email' => trim((string) ($validated['email'] ?? '')),
            'contact' => $phoneNumber,
        ];

        try {
            if ($request->hasFile('profilePicture')) {
                if ($user->profile_img) {
                    $this->deleteStoredImage($user->profile_img);
                }

                $updateData['profile_img'] = $request->file('profilePicture')->store('profile-photos', 'public');
            }

            if ($request->hasFile('governmentIdImage')) {
                if ($user->government_id_image) {
                    $this->deleteStoredImage($user->government_id_image);
                }

                $updateData['government_id_image'] = $request->file('governmentIdImage')->store('government-ids', 'public');
            }

            $user->update($updateData);
        } catch (\Throwable $exception) {
            Log::error('Fallback settings update failed.', [
                'user_id' => $user->user_id,
                'error' => $exception->getMessage(),
            ]);

            return back()
                ->withErrors(['settings' => 'Unable to save profile right now. Please try again.'])
                ->withInput();
        }

        return redirect()
            ->route('settings')
            ->with('success', 'Settings Saved Successfully! Your personal information has been updated.');
    }

    private function normalizePhone(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';

        if ($digits === '') {
            return '';
        }

        if (strlen($digits) > 10) {
            return substr($digits, -10);
        }

        return $digits;
    }

    private function deleteStoredImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        try {
            $normalized = $this->normalizeStoragePath($path);

            if ($normalized !== '' && Storage::disk('public')->exists($normalized)) {
                Storage::disk('public')->delete($normalized);
            }
        } catch (\Throwable $exception) {
            // File may not exist on Render ephemeral filesystem after redeploy
            Log::debug('Could not delete stored image (may be expected on Render redeploy).', [
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function normalizeStoragePath(string $path): string
    {
        $normalized = ltrim(trim($path), '/');

        if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
            $normalized = ltrim((string) parse_url($normalized, PHP_URL_PATH), '/');
        }

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }

        return $normalized;
    }
}
