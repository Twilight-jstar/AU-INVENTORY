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
        Schema::create('items', function (Blueprint $table) {
            // id INT PRIMARY KEY AUTO_INCREMENT
            $table->id(); 

            // product_code VARCHAR(100) UNIQUE NOT NULL
            $table->string('product_code', 100)->unique();

            // name VARCHAR(255) NOT NULL
            $table->string('name'); 

            // quantity DECIMAL(10, 2) DEFAULT 0
            $table->decimal('quantity', 10, 2)->default(0);

            // min_stock DECIMAL(10, 2) DEFAULT 0
            $table->decimal('min_stock', 10, 2)->default(0);

            // unit_id INT + FOREIGN KEY (unit_id) REFERENCES Units(id)
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');

            // category_id INT + FOREIGN KEY (category_id) REFERENCES Categories(id)
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');

            // description TEXT
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};