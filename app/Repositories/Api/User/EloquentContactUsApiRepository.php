<?php

namespace App\Repositories\Api\User;

use App\Interfaces\Gateways\Api\User\ContactUsApiRepositoryInterface;
use App\Models\ContactUs;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use App\Models\Admin;
use App\Notifications\Admin\NewContactNotification;

class EloquentContactUsApiRepository implements ContactUsApiRepositoryInterface
{
    public function createContactUs($data, $imageData)
    {
        $contactUS = ContactUs::create($data);
        if ($imageData !== null) {
            foreach ($imageData as $image) {
                $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                $filename = Str::random(10) . '_' . time() . '.' . $extension;
                $contactUS->addMedia($image)->usingFileName($filename)->toMediaCollection('contact');
            }
        }
        Notification::send(Admin::all(), new NewContactNotification($contactUS));

        //$response = Http::post('http://127.0.0.1:3000/notifications');
        return $contactUS;
    }
}
