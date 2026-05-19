<?php

namespace App\Http\Controllers;

use App\Services\PostMigrationReconciliationService;
use Inertia\Inertia;
use Inertia\Response;

class PostMigrationReconciliationController extends Controller
{
    public function __construct(private readonly PostMigrationReconciliationService $service) {}

    public function index(): Response
    {
        return Inertia::render('Migration/PostMigrationReconciliation');
    }

    public function run()
    {
        return response()->json($this->service->run());
    }
}
