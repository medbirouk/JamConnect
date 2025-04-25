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
        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('pinned_message_id')
                  ->nullable()
                  ->constrained('chat_messages')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            Schema::table('groups', function (Blueprint $table) {
                $table->dropForeign(['pinned_message_id']);
                $table->dropColumn('pinned_message_id');
            });
        });
    }
};

