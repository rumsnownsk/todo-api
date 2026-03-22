<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $statuses = Status::with('tasks')->get();
        return response()->json([
            'success' => true,
            'data' => StatusResource::collection($statuses)
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'required|string|max:50|unique:statuses,slug'
        ]);

        $status = Status::create($validated);
        return response()->json([
            'success' => true,
            'data' => $status
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $status = Status::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $status
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $status = Status::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:50',
            'slug' => 'sometimes|required|string|max:50|unique:statuses,slug,' . $id
        ]);

        $status->update($validated);
        return response()->json([
            'success' => true,
            'data' => $status
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $status = Status::findOrFail($id);
        $status->delete();

        return response()->json([
            'success' => true,
            'message' => 'Status deleted successfully'
        ], 200);
    }
}
