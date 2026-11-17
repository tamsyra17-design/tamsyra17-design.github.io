<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * search_text already ships with the products table (Task 7), so the
     * column is only added when missing. Either way, existing rows are
     * backfilled with the Arabic-normalized search text.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'search_text')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('search_text')->nullable();
            });
        }

        Product::query()->chunkById(200, function ($products): void {
            foreach ($products as $product) {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['search_text' => $product->buildSearchText()]);
            }
        });
    }

    /**
     * The column belongs to the create_products_table migration, so rolling
     * back only leaves the (still valid) normalized values in place.
     */
    public function down(): void
    {
        //
    }
};
