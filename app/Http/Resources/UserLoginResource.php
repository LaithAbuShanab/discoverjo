<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLoginResource extends JsonResource
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
            'username' => $this->username,
            'email' => $this->email,
            'address' => $this->address,
            'verified_email'=>$this->verified_email,
            'is_guide'=>$this->is_guide,
            'referral_code'=>$this->referral_code,
            'first_login'=>$this->status==2?true:false,
            'avatar'=>$this->getFirstMediaUrl('avatar','avatar_app'),
            'token'=>$this->token,
            'token_website'=>$this->token_website,


        ];
    }
}
