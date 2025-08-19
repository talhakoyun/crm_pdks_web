<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Company\CompanyResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    private $tokenData;

    public function __construct($resource, $tokenData = null)
    {
        parent::__construct($resource);
        $this->tokenData = $tokenData;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'company' => new CompanyResource($this->company),
            'title' => $this->title,
            'department' => [
                'id' => $this->department?->id,
                'name' => $this->department?->title,
            ],
            'settings' => [
                'outside' => boolval($this->allow_outside)
            ],
            'profile_photo' => !is_null($this->photo) ? env('APP_URL') . '/upload/user/' . $this->photo : env('APP_URL') . '/upload/default_user.png',
            'token_type' => $this->tokenData['token_type'] ?? 'Bearer',
            'access_token' => $this->tokenData['access_token'] ?? null,
            'expires_at' => $this->tokenData['expires_in'] ?? null,
        ];
    }
}
