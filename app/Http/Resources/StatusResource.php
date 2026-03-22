<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'tasks' => $this->when(
                $this->relationLoaded('tasks') && $request->has('with_tasks'),
                fn() => TaskResource::collection($this->tasks)
            ),            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
