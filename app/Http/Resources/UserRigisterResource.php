<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserRegisterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "user_id" => $this->id,
            "name"=> $this->name,
            "email"=> $this->email,
            "token"=> $this->createToken("Token")->plainTextToken,
            "roles"=> $this->getPermissionsViaRoles()->pluck(['name']) ?? [],
            "permissions"=> $this->permissions->pluck('name') ?? [],
        ];
    }
}
