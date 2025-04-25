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
        Schema::create('post_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');  
            $table->foreignId('post_id')->constrained('posts');  
            $table->foreignId('created_by')->nullable()->constrained('users');  
            $table->string('status', 25)->default('pending');  
            $table->string('role', 25)->default('user');  
            $table->string('token', 1024)->nullable();  
            $table->timestamp('token_expire_date')->nullable();  
            $table->timestamp('token_used')->nullable();
            $table->timestamps();  

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_users');
    }
};
