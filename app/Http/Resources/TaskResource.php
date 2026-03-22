<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'user' => [
                'id' => $this->user->id,
                'client_ip' => $this->user->client_ip,
                'name'=>$this->user->name,
            ],
            'status' => [
                'id'=>$this->status->id,
                'name'=>$this->status->name,
                'slug'=>$this->status->slug,
            ],
            'created_at' => $this->created_at, // Unix timestamp
            'updated_at' => $this->updated_at, // Unix timestamp
            // Опционально: добавляем читаемый формат
            'created_at_readable' => date('Y‑m‑d H:i:s', $this->created_at),
            'updated_at_readable' => date('Y‑m‑d H:i:s', $this->updated_at),
        ];
    }
}
