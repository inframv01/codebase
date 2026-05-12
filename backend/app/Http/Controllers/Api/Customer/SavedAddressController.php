<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\SavedAddressRequest;
use App\Http\Resources\SavedAddressResource;
use App\Models\SavedAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SavedAddressController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', SavedAddress::class);

        return SavedAddressResource::collection($request->user()->savedAddresses()->latest()->get());
    }

    public function store(SavedAddressRequest $request): SavedAddressResource
    {
        Gate::authorize('create', SavedAddress::class);

        $address = DB::transaction(function () use ($request): SavedAddress {
            $payload = $request->validated();
            $user = $request->user();

            if (($payload['is_default'] ?? false) === true) {
                $user->savedAddresses()->where('purpose', $payload['purpose'])->update(['is_default' => false]);
            }

            return $user->savedAddresses()->create($payload);
        });

        return new SavedAddressResource($address);
    }

    public function show(SavedAddress $address): SavedAddressResource
    {
        Gate::authorize('view', $address);

        return new SavedAddressResource($address);
    }

    public function update(SavedAddressRequest $request, SavedAddress $address): SavedAddressResource
    {
        Gate::authorize('update', $address);

        DB::transaction(function () use ($request, $address): void {
            $payload = $request->validated();

            if (($payload['is_default'] ?? false) === true) {
                $address->user->savedAddresses()->where('purpose', $payload['purpose'])->update(['is_default' => false]);
            }

            $address->update($payload);
        });

        return new SavedAddressResource($address->refresh());
    }

    public function destroy(SavedAddress $address)
    {
        Gate::authorize('delete', $address);

        $address->delete();

        return response()->noContent();
    }
}
