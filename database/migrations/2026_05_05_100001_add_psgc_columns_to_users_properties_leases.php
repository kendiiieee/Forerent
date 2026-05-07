<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('permanent_province_id')->nullable()->after('permanent_address')
                ->constrained('provinces')->nullOnDelete();
            $table->foreignId('permanent_city_id')->nullable()->after('permanent_province_id')
                ->constrained('cities')->nullOnDelete();
            $table->foreignId('permanent_barangay_id')->nullable()->after('permanent_city_id')
                ->constrained('barangays')->nullOnDelete();
            $table->string('permanent_street', 255)->nullable()->after('permanent_barangay_id');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->after('address')
                ->constrained('provinces')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->after('province_id')
                ->constrained('cities')->nullOnDelete();
            $table->foreignId('barangay_id')->nullable()->after('city_id')
                ->constrained('barangays')->nullOnDelete();
            $table->string('street', 255)->nullable()->after('barangay_id');
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->foreignId('forwarding_province_id')->nullable()->after('forwarding_address')
                ->constrained('provinces')->nullOnDelete();
            $table->foreignId('forwarding_city_id')->nullable()->after('forwarding_province_id')
                ->constrained('cities')->nullOnDelete();
            $table->foreignId('forwarding_barangay_id')->nullable()->after('forwarding_city_id')
                ->constrained('barangays')->nullOnDelete();
            $table->string('forwarding_street', 255)->nullable()->after('forwarding_barangay_id');
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropForeign(['forwarding_province_id']);
            $table->dropForeign(['forwarding_city_id']);
            $table->dropForeign(['forwarding_barangay_id']);
            $table->dropColumn([
                'forwarding_province_id',
                'forwarding_city_id',
                'forwarding_barangay_id',
                'forwarding_street',
            ]);
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['barangay_id']);
            $table->dropColumn(['province_id', 'city_id', 'barangay_id', 'street']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['permanent_province_id']);
            $table->dropForeign(['permanent_city_id']);
            $table->dropForeign(['permanent_barangay_id']);
            $table->dropColumn([
                'permanent_province_id',
                'permanent_city_id',
                'permanent_barangay_id',
                'permanent_street',
            ]);
        });
    }
};
