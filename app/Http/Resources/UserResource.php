<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'given_name'  => $this->given_name ?? null,
            'family_name' => $this->family_name ?? null,
            'email'       => $this->email ?? null,
            'groups'      => $this->groups ?? [],
            'roles'       => $this->resource_access->{"app-test-id"}->roles ?? [],
        ];
    }
}
