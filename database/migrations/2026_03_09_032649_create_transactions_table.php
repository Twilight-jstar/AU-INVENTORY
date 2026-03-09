<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // Link to the Item
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            // Link to the Admin/User who performed the action
            $table->foreignId('user_id')->constrained();
            
            // Core Movement Data
            $table->enum('type', ['In', 'Out']); // "In" for Stock In, "Out" for Stock Out
            $table->integer('quantity');
            
            // Contextual Data
            $table->string('source_destination')->nullable(); // Supplier Name OR Office/Dept Name
            $table->string('personnel_name')->nullable();    // Received By OR Released To
            $table->string('reference_no')->nullable();      // Invoice # or Request #
            
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};