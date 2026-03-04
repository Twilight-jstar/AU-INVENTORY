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
        Schema::create('units', function (Blueprint $table) {
            // id INT PRIMARY KEY AUTO_INCREMENT
            $table->id(); 

            // name VARCHAR(50) NOT NULL
            // The second argument sets the length to 50
            $table->string('name', 50); 

            // Automatically adds created_at and updated_at
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};