<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;

class BranchController extends Controller
{
    /**
     * Get all active branches for selection
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return response()->json($branches);
    }
}
