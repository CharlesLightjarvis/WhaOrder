<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->change();
            $table->integer('stock')->nullable()->default(null)->change();
        });

        DB::table('product_variants')
            ->whereNull('price')
            ->orderBy('id')
            ->eachById(function (object $variant): void {
                $parentPrice = DB::table('products')->where('id', $variant->product_id)->value('price');
                DB::table('product_variants')->where('id', $variant->id)->update([
                    'price' => $parentPrice ?? 0,
                ]);
            }, column: 'id');

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable(false)->change();
        });

        DB::table('products')
            ->whereExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('product_variants')
                ->whereColumn('product_variants.product_id', 'products.id'))
            ->update(['price' => null, 'stock' => null]);
    }

    public function down(): void
    {
        DB::table('products')->whereNull('price')->update(['price' => 0]);
        DB::table('products')->whereNull('stock')->update(['stock' => 0]);

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable(false)->change();
            $table->integer('stock')->nullable(false)->default(0)->change();
        });
    }
};
