<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
        });

        
        Schema::table('posts', function (Blueprint $table) {
            $table->foreign('deleted_by')
                ->references('id')->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->foreign('deleted_by')
                ->references('id')->on('users');
        });
    }
};
