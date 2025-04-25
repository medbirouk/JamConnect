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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts');  
            $table->foreignId('user_id')->constrained('users');  
            $table->string('name', 50);  
            $table->string('thumbnail_path')->nullable();  
            $table->string('cover_path')->nullable();  
            
            $table->softDeletes();  
            $table->timestamps();  
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
