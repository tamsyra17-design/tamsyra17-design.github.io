<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    /**
     * Rebuild the derived search_text before every insert and update.
     */
    public function saving(Product $product): void
    {
        $product->search_text = $product->buildSearchText();
    }
}
