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
        Schema::create('stock_out', function (Blueprint $table) {
            $table->id(); // PK

            // item_id (FK) - references 'id' on 'items' table
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');

            // quantity - using decimal(10, 2) to match your items table
            $table->decimal('quantity', 10, 2);

            // date_released
            $table->date('date_released');

            // released_to (The person receiving the item)
            $table->string('released_to');

            // department (Which department requested it)
            $table->string('department')->nullable();

            // purpose (e.g., "Office Use", "Project X", "Replacement")
            $table->text('purpose')->nullable();

            // released_by (The staff member processing the release)
            $table->string('released_by');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_out');
    }
};