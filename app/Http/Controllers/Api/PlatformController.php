<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlatformResource;
use App\Models\Platform;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlatformController extends Controller
{
    /**
     * List the platforms a holding can reference.
     */
    public function index(): AnonymousResourceCollection
    {
        $platforms = Platform::query()
            ->orderBy('name')
            ->get();

        return PlatformResource::collection($platforms);
    }
}
