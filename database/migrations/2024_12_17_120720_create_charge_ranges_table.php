<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('charge_ranges', function (Blueprint $table) {
            $table->id();
            // Foreign Keys
            $table->foreignId('transaction_type_id')
                ->constrained()
                ->onDelete('cascade');

            // Amount Range
            $table->decimal('min_amount', 15, 2);
            $table->decimal('max_amount', 15, 2);

            // Service Charge Fields
            $table->enum('charge_type', ['flat', 'percentage', 'both']);
            $table->decimal('flat_charge_amount', 15, 2)->nullable();
            $table->decimal('percentage_charge_amount', 8, 2)->nullable();

            // Government Tax Fields
            $table->enum('tax_type', ['flat', 'percentage', 'both']);
            $table->decimal('flat_tax_amount', 15, 2)->nullable();
            $table->decimal('percentage_tax_amount', 8, 2)->nullable();

            // Approval Workflow Fields
            $table->enum('approval_status', [
                'draft',
                'pending_finance',
                'pending_ceo',
                'approved',
                'rejected'
            ])->default('draft');
            $table->text('rejection_reason')->nullable();

            // Approval Users
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users');
            $table->foreignId('finance_approved_by')
                ->nullable()
                ->constrained('users');
            $table->foreignId('ceo_approved_by')
                ->nullable()
                ->constrained('users');

            // Approval Timestamps
            $table->timestamp('finance_approved_at')->nullable();
            $table->timestamp('ceo_approved_at')->nullable();

            // Status and Timestamps
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            // Add indexes for better performance
            $table->index('approval_status');
            $table->index('is_active');
            $table->index(['min_amount', 'max_amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charge_ranges');
    }
};
