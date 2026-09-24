<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('status')->default('active')->after('is_active');
        });

        // Preserve current visibility: products already hidden via is_active
        // stay hidden (draft), and products already out of stock start in
        // the out_of_stock state instead of silently reading as active.
        DB::table('products')->where('is_active', false)->update(['status' => 'draft']);
        DB::table('products')->where('is_active', true)->where('stock', '<=', 0)->update(['status' => 'out_of_stock']);

        Schema::table('products', function (Blueprint $table) {
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
