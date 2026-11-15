<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    /**
     * Report the health of the API and its database connection.
     */
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('select 1');

            return response()->json([
                'status' => 'ok',
                'database' => 'ok',
            ]);
        } catch (Throwable) {
            return response()->json([
                'status' => 'degraded',
                'database' => 'error',
            ], 503);
        }
    }
}
