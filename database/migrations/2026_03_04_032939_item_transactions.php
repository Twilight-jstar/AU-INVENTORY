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
        Schema::create('item_transactions', function (Blueprint $table) {
            // id INT PRIMARY KEY AUTO_INCREMENT
            $table->id();

            // items_id INT + FOREIGN KEY (items_id) REFERENCES Items(id)
            $table->foreignId('items_id')->constrained('items')->onDelete('cascade');

            // type VARCHAR(50)
            $table->string('type', 50); 

            // quantity DECIMAL(10, 2)
            $table->decimal('quantity', 10, 2);

            // transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            // Laravel's useCurrent() handles the SQL DEFAULT CURRENT_TIMESTAMP
            $table->timestamp('transaction_date')->useCurrent();

            // Optional: adds created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_transactions');
    }
};