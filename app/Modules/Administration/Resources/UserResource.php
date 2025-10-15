<?php

namespace App\Modules\Administration\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'adresse' => $this->adresse ?? null,
            'role' => $this->role,
            'telephone' => $this->telephone ?? null,
            'image' => is_null($this->image)  ? asset('/images/male.jpg') : asset('/storage/images/users/'.$this->image),
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i:s'),
        ];
    }
}
