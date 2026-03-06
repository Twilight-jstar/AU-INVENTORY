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
        Schema::create('stock_in', function (Blueprint $table) {
            $table->id(); // PK

            // item_id (FK) - references 'id' on 'items' table
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');

            // supplier_id (null) - nullable for now, can be linked to a 'suppliers' table later
            $table->unsignedBigInteger('supplier_id')->nullable();

            // quantity and unit_cost (using decimal for precision)
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 12, 2);

            // date_received
            $table->date('date_received');

            // reference_no (e.g., Invoice # or PO #)
            $table->string('reference_no')->nullable();

            // received_by (usually the name or user_id of the staff)
            $table->string('received_by');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_in');
    }
};