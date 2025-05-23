<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('post_users', function (Blueprint $table) {
            $table->string('artist_role')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('post_users', function (Blueprint $table) {
            $table->dropColumn('artist_role');
        });
    }
};
