<?php

namespace App\Http\Controllers\Api\Lookup;

use App\Http\Controllers\Controller;
use App\Http\Resources\AtollResource;
use App\Models\Atoll;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AtollLookupController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        return AtollResource::collection(Atoll::query()->orderBy('name')->get());
    }
}
