<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRentalRequest;
use App\Http\Resources\RentalResource;
use App\Models\Rental;
use App\Services\RentalPlacer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    public function __construct(private readonly RentalPlacer $placer) {}

    public function store(StoreRentalRequest $request): JsonResponse
    {
        $rental = $this->placer->place($request->validated(), $request->user('sanctum'));

        return (new RentalResource($rental->load(['items', 'deposit'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Guest lookup by number + phone. A wrong/missing phone yields 404 so the
     * response never confirms that a rental exists.
     */
    public function show(Request $request, string $number): RentalResource
    {
        $phone = preg_replace('/\D+/', '', (string) $request->query('phone', '')) ?? '';
        $rental = Rental::query()->where('number', $number)->first();

        abort_if(
            $phone === '' || $rental === null || (preg_replace('/\D+/', '', $rental->phone) ?? '') !== $phone,
            404,
        );

        return new RentalResource($rental->load(['items', 'deposit', 'returns']));
    }
}
