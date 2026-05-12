<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ContactNumbersRequest;
use App\Http\Requests\Customer\MeUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user()->load(['atoll', 'island', 'contactNumbers']));
    }

    public function update(MeUpdateRequest $request): UserResource
    {
        $user = $request->user();
        $user->update($request->validated());

        return new UserResource($user->fresh(['atoll', 'island', 'contactNumbers']));
    }

    public function replaceContactNumbers(ContactNumbersRequest $request): JsonResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($request, $user): void {
            $user->contactNumbers()->delete();

            $user->contactNumbers()->createMany(
                collect($request->validated('contact_numbers'))
                    ->values()
                    ->map(fn (string $number, int $index): array => [
                        'number' => $number,
                        'position' => $index + 1,
                    ])->all()
            );
        });

        return response()->json([
            'message' => 'Contact numbers updated successfully.',
            'user' => new UserResource($user->fresh(['atoll', 'island', 'contactNumbers'])),
        ]);
    }
}
