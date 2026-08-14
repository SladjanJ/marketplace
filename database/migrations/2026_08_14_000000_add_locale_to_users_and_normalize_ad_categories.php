<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 5)->nullable()->after('role');
        });

        if (Schema::hasTable('ads') && Schema::hasColumn('ads', 'category')) {
            DB::table('ads')->where('category', 'Prodaja')->update(['category' => 'sale']);
            DB::table('ads')->where('category', 'Usluge')->update(['category' => 'services']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ads') && Schema::hasColumn('ads', 'category')) {
            DB::table('ads')->where('category', 'sale')->update(['category' => 'Prodaja']);
            DB::table('ads')->where('category', 'services')->update(['category' => 'Usluge']);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
