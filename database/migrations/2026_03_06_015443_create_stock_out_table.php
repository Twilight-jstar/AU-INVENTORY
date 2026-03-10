<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_out', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('ref_no')->index();
            $table->decimal('quantity', 10, 2);
            $table->date('date_released');
            $table->string('released_to');
            $table->string('department')->nullable();
            $table->text('purpose')->nullable();
            $table->string('released_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_out');
    }
};