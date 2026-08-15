<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index(['status', 'category']);
            $table->index(['status', 'price']);
            $table->index(['user_id', 'created_at']);
            $table->index('location');
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['status', 'category']);
            $table->dropIndex(['status', 'price']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['location']);
        });
    }
};
