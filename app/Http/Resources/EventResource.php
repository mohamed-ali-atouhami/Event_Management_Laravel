<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\URL; 

class EventResource extends JsonResource
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
            'date' => $this->date,
            'time' => $this->time,
            'location' => $this->location,
            'image' => $this->image ? URL::to($this->image) : null,
            'user' => new UserResource($this->whenLoaded('organizer')),
            'ticket_price' => $this->ticket_price,
            'status' => $this->status,
            'rejection_reason' => $this->when($this->status === 'rejected', $this->rejection_reason),
            'is_editable' => $request->user()?->isAdmin() || $request->user()?->id === $this->organizer_id
        ];
    }
}
