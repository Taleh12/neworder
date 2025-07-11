<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 10, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->string('attachment')->nullable(); // Optional attachment field
            $table->enum('status', [
            'Draft', 'TeamLeadApproval', 'DepartmentApproval',
            'FinanceApproval', 'ProcurementApproval',
            'WarehouseApproval', 'Closed', 'Rejected'
        ])->default('Draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};