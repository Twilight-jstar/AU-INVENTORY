<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_in', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('ref_no')->nullable()->index(); 
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->date('date_received');
            $table->string('supplier_name')->nullable();
            $table->string('received_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_in');
    }
};