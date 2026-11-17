<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;

class PageController extends Controller
{
    /**
     * A single published CMS page by slug; drafts are reported as missing.
     */
    public function show(string $slug): PageResource
    {
        return new PageResource(
            Page::query()->published()->where('slug', $slug)->firstOrFail()
        );
    }
}
