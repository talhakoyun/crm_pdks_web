<?php

namespace App\Http\Resources\Profile;

use App\Http\Resources\Company\CompanyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    private ?array $tokenData = null;

    public function __construct($resource, ?array $tokenData = null)
    {
        parent::__construct($resource);
        $this->tokenData = $tokenData;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender == 1 ? 'Erkek' : 'Kadın',
            'role' => $this->role,
            'company' => new CompanyResource($this->company),
            'department' => [
                'id' => $this->department?->id,
                'name' => $this->department?->title,
            ],
            'settings' => [
                'outside' => boolval($this->allow_outside),
                'offline' => boolval($this->allow_offline),
                'zone' => boolval($this->allow_zone)
            ],
            'birthday' => $this->birthday,
            'created_at' => $this->created_at,
        ];

        if ($this->tokenData !== null) {
            $data['token'] = $this->tokenData;
        }

        return $data;
    }
}
