<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_items', function (Blueprint $table) {
            $table->id('billing_item_id')->primary();
            $table->foreignId('billing_id')
                ->constrained('billings', 'billing_id')
                ->onDelete('cascade');
            $table->enum('charge_category', ['recurring', 'conditional', 'move_in', 'move_out']);
            $table->string('charge_type'); // e.g. rent, electricity_share, water_share, late_fee, advance, security_deposit
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_items');
    }
};
