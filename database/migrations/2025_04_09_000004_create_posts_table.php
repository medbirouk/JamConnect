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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->longText('body')->nullable();  
            $table->foreignId('user_id')->constrained('users');  
            $table->foreignId('deleted_by')->nullable()->constrained('users');  
            $table->timestamp('deleted_at')->nullable();  
            $table->timestamp('date_time')->nullable();  
            $table->json('preview')->nullable();  
            $table->string('preview_url', 2000)->nullable();  
            $table->string('city')->nullable();  
            $table->timestamps();  
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
