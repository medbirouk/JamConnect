<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_attachments', function (Blueprint $table) {
            
            
            $table->boolean('for_profile')->default(false)->after('post_id');
            
            $table->unsignedBigInteger('post_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('post_attachments', function (Blueprint $table) {
            $table->dropColumn('for_profile');
            $table->unsignedBigInteger('post_id')->nullable(false)->change();
        });
    }
};
