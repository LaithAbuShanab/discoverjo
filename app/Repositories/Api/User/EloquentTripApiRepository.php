<?php

namespace App\Repositories\Api\User;

use App\Events\GroupMemberEvent;
use App\Http\Resources\PrivateTripResource;
use App\Http\Resources\TagsResource;
use App\Http\Resources\TripDetailsResource;
use App\Http\Resources\TripResource;
use App\Interfaces\Gateways\Api\User\TripApiRepositoryInterface;
use App\Models\Admin;
use App\Models\Conversation;
use App\Models\DeviceToken;
use App\Models\GroupMember;
use App\Models\Place;
use App\Models\Reviewable;
use App\Models\Tag;
use App\Models\Trip;
use App\Models\User;
use App\Models\UsersTrip;
use App\Notifications\Users\Trip\AcceptCancelInvitationNotification;
use App\Notifications\Users\Trip\AcceptCancelNotification;
use App\Notifications\Users\Trip\NewRequestNotification;
use App\Notifications\Users\Trip\NewTripNotification as TripNewTripNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use App\Pipelines\ContentFilters\ContentFilter;
use Illuminate\Pipeline\Pipeline;

class EloquentTripApiRepository implements TripApiRepositoryInterface
{
    public function tags()
    {
        $tags = Tag::get();
        return TagsResource::collection($tags);
    }

    public function trips()
    {
        $userId = Auth::guard('api')->user()->id;

        $trips = Trip::whereHas('user', function ($query) {
            $query->where('status', '1'); // Only include trips where the user status is 1
        })
            ->where(function ($query) use ($userId) {
                // Public trips
                $query->where('trip_type', '0')
                    ->orWhere(function ($query) use ($userId) {
                        // Followers trips and trips created by the authenticated user
                        $query->where('trip_type', '1')
                            ->where(function ($query) use ($userId) {
                                $query->whereHas('user.followers', function ($query) use ($userId) {
                                    $query->where('follower_id', $userId);
                                })->orWhere('user_id', $userId);
                            });
                    })->orWhere(function ($query) use ($userId) {
                        // Specific user trips
                        $query->where('trip_type', '2')
                            ->whereHas('usersTrip', function ($query) use ($userId) {
                                $query->where('user_id', $userId)
                                    ->where('status', '1');
                            })->orWhere('user_id', $userId);
                    });
            })
            ->where('status', '1')
            ->where(function ($query) {
                $query->where('trip_type', '!=', '2')
                    ->whereHas('usersTrip', function ($query) {
                        $query->where('status', '1');
                    }, '!=', DB::raw('attendance_number'))
                    ->orWhere('trip_type', '2');
            })
            ->get();

        return TripResource::collection($trips);
    }

    public function allTrips()
    {
        $perPage = config('app.pagination_per_page');
        $now = now()->setTimezone('Asia/Riyadh');
        $trips = Trip::where('status', '1')
            ->where('trip_type', '0')
            ->where('date_time', '>=', $now)
            ->whereHas('user', function ($query) {
                $query->where('status', '!=', '0');
            })
            ->paginate($perPage);

        $tripsArray = $trips->toArray();

        $pagination = [
            'next_page_url' => $tripsArray['next_page_url'],
            'prev_page_url' => $tripsArray['next_page_url'],
            'total' => $tripsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'trips' => TripResource::collection($trips),
            'pagination' => $pagination
        ];
    }

    public function invitationTrips()
    {
        $userId = Auth::guard('api')->user()->id;
        $trips = Trip::where('trip_type', '2')
            ->where('status', '1')
            ->whereHas('usersTrip', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('status', '0');
            })->get();


        return TripResource::collection($trips);
    }

    public function invitationCount()
    {
        $userId = Auth::guard('api')->user()->id;
        $trips = Trip::where('trip_type', '2')
            ->where('status', '1')
            ->whereHas('usersTrip', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('status', '0');
            })->count();

        return ['count' => $trips];
    }

    public function privateTrips()
    {
        $userTrips = UsersTrip::where('user_id', Auth::guard('api')->user()->id)->where('status', '1')->pluck('trip_id')->toArray();
        $trips = Trip::where('user_id', Auth::guard('api')->user()->id)->orWhereIn('id', $userTrips)->get();
        return PrivateTripResource::collection($trips);
    }

    public function createTrip($request)
    {
        $tags = collect(explode(',', $request->tags))->map(function ($tag) {
            $tagModel = Tag::where('slug', trim($tag))->first();
            return $tagModel ? $tagModel->id : null;
        })->filter()->toArray();

        $ageRange = json_encode(['min' => $request->age_min, 'max' => $request->age_max]);

        $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $request->date . ' ' . $request->time);

        $filteredName = app(Pipeline::class)
            ->send($request->name)
            ->through([ContentFilter::class])
            ->thenReturn();

        $filteredDescription = app(Pipeline::class)
            ->send($request->description)
            ->through([ContentFilter::class])
            ->thenReturn();

        $trip = new Trip();
        $trip->user_id = Auth::guard('api')->user()->id;
        $trip->place_id = Place::where('slug', $request->place_slug)->first()->id;
        $trip->trip_type = $request->trip_type;
        $trip->name = $filteredName;
        $trip->description = $filteredDescription;
        $trip->cost = $request->cost;
        $trip->age_range = $ageRange;
        $trip->sex = $request->gender;
        $trip->date_time = $dateTime;
        $trip->attendance_number = $request->attendance_number;
        $trip->save();
        $trip->tags()->attach($tags);

        // Create Trip Conversation
        $conversation = new Conversation();
        $conversation->trip_id = $trip->id;
        $conversation->save();

        $first_member = new GroupMember();
        $first_member->user_id =  Auth::guard('api')->user()->id;
        $first_member->conversation_id = $conversation->id;
        $first_member->joined_datetime = now();
        $first_member->save();

        $createdTrip = Trip::find($trip->id);

        // Notify an admin about the new user registration
        adminNotification(
            'New Trip',
            'A new trip has been create by ' . Auth::guard('api')->user()->username,
            ['action' => 'view_trip', 'action_label' => 'View Trip', 'action_url' => route('filament.admin.resources.trips.view', $trip)]
        );

        //send notification for specific user & Followers of this user
        $this->handleTripTypeNotifications($request, $trip);

        return new TripResource($createdTrip);
    }

    // When Creator Accept Or Reject User
    public function changeStatus($request)
    {
        return DB::transaction(function () use ($request) {
            $status = $request->status == 'accept' ? '1' : '2';

            $userID = User::where('slug', $request->user_slug)->first()->id;
            $tripId = Trip::where('slug', $request->trip_slug)->first()->id;

            $userTrip = UsersTrip::where('user_id', $userID)
                ->where('trip_id', $tripId)
                ->where('status', '0')
                ->first();

            if ($userTrip) {
                $userTrip->status = $status;
                $userTrip->save();
            }

            $trip = Trip::findOrFail($tripId);

            if ($status == '1' && $trip->conversation) {
                $conversationId = $trip->conversation->id;

                $existingMember = GroupMember::where('conversation_id', $conversationId)
                    ->where('user_id', $userID)
                    ->first();

                if ($existingMember) {
                    $existingMember->joined_datetime = now();
                    $existingMember->left_datetime = null;
                    $existingMember->save();
                } else {
                    // Add the user as a new member of the group
                    $newMember = new GroupMember();
                    $newMember->user_id = $userID;
                    $newMember->conversation_id = $conversationId;
                    $newMember->joined_datetime = now();
                    $newMember->save();
                }

                $contents = [
                    'conversation_id' => $conversationId,
                    'member' => User::findOrFail($userID),
                    'action' => 'join',
                ];

                Broadcast(new GroupMemberEvent($contents))->toOthers();
            }

            $user = User::findOrFail($userID);
            Notification::send($user, new AcceptCancelNotification($trip, $request->status));

            $receiverLanguage = $user->lang;
            $notificationData = [
                'title' => Lang::get(
                    $request->status == 'accept'
                        ? 'app.notifications.accepted-trip'
                        : 'app.notifications.rejected-trip',
                    [],
                    $receiverLanguage
                ),
                'body' => Lang::get(
                    $request->status == 'accept'
                        ? 'app.notifications.accepted-trip-body'
                        : 'app.notifications.rejected-trip-body',
                    ['username' => Auth::guard('api')->user()->username, 'trip_name' => $trip->name],
                    $receiverLanguage
                ),
                'icon'  => asset('assets/icon/trip.png'),
                'sound' => 'default',
            ];

            if ($user->DeviceToken) {
                sendNotification([$user->DeviceToken->token], $notificationData);
            }

            // Return the updated user trip
            return $userTrip;
        });
    }

    public function joinTrip($slug)
    {
        $trip = Trip::where('slug', $slug)->first();
        $trip_id = $trip->id;
        $checkUser = $this->checkIfTheUserHasAlreadyJoined(Auth::guard('api')->user()->id, $trip_id);
        if ($checkUser) {
            $eloquentJoinTrip = new UsersTrip();
            $eloquentJoinTrip->user_id = Auth::guard('api')->user()->id;
            $eloquentJoinTrip->trip_id = $trip_id;
            $eloquentJoinTrip->save();
        }

        // To Save Notification In Database
        Notification::send(Trip::find($trip_id)->user, new NewRequestNotification(Auth::guard('api')->user(), $trip));

        // To Send Notification To Owner Using Firebase Cloud Messaging
        $ownerToken = Trip::find($trip_id)->user->DeviceToken->token;
        $receiverLanguage = Trip::find($trip_id)->user->lang;
        $notificationData = [
            'title' => Lang::get('app.notifications.new-request', [], $receiverLanguage),
            'body' => Lang::get('app.notifications.new-user-request-from-trip', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
            'icon'  => asset('assets/icon/trip.png'),
            'sound' => 'default',
        ];
        sendNotification([$ownerToken], $notificationData);
    }

    public function changeStatusInvitation($request)
    {
        $userId = Auth::guard('api')->user()->id;
        $status = $request->status == 'accept' ? '1' : '2';
        $trip = Trip::where('slug', $request->trip_slug)->first();
        $trip_id = $trip->id;

        $userTrip = UsersTrip::where('user_id', $userId)->where('trip_id', $trip_id)->first();
        $userTrip->status = $status;
        $userTrip->save();

        if ($status == '1') {
            $conversation = Conversation::where('trip_id', $trip_id)->first();
            // add the user as a new member of the group
            $newMember = new GroupMember();
            $newMember->user_id = $userId;
            $newMember->conversation_id = $conversation->id;
            $newMember->joined_datetime = now();
            $newMember->save();
        }

        $trip = Trip::find($trip_id);
        $user = User::find($trip->user_id);

        // To Save Notification In Database
        Notification::send($user, new AcceptCancelInvitationNotification($request->status, Auth::guard('api')->user()->username, $trip));

        // To Send Notification To Owner Using Firebase Cloud Messaging
        $receiverLanguage = $user->lang;
        $notificationData = [
            'title' => Lang::get($request->status == 'accept' ? 'app.notifications.accepted-invitation-trip' : 'app.notifications.rejected-invitation-trip', [], $receiverLanguage),
            'body' => Lang::get($request->status == 'accept' ? 'app.notifications.accepted-invitation-trip-body' : 'app.notifications.rejected-invitation-trip-body', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
            'icon'  => asset('assets/icon/trip.png'),
            'sound' => 'default',
        ];
        sendNotification([$user->DeviceToken->token], $notificationData);
    }

    public function tripDetails($slug)
    {
        $trip = Trip::where('slug', $slug)->first();
        return new TripDetailsResource($trip);
    }

    // When User Leaving
    public function cancelJoinTrip($slug, $request)
    {
        $trip_id = Trip::where('slug', $slug)->first()->id;
        return DB::transaction(function () use ($trip_id) {
            UsersTrip::where('trip_id', $trip_id)->where('user_id', Auth::guard('api')->user()->id)->update(['status' => '3']);
            $conversation = Conversation::where('trip_id', $trip_id)->first();
            if ($conversation) {
                $member = $conversation->members->where('user_id', Auth::guard('api')->user()->id)->where('left_datetime', null)->first();
                $member->left_datetime = now();
                $member->save();

                $contents = [
                    'conversation_id' => $conversation->id,
                    'member' => Auth::guard('api')->user(),
                    'action' => 'leave',
                ];

                Broadcast(new GroupMemberEvent($contents))->toOthers();
            }
        });
    }

    public function remove($slug)
    {
        $trip = Trip::where('slug', $slug)->first();
        $trip->status = '2';
        $trip->save();
        $trip->usersTrip()->update(['status' => '2']);
        $trip->conversation()->delete();
    }

    public function update($request)
    {
        $trip = Trip::where('slug', $request->trip_slug)->first();

        if ($request->name) {
            $filteredName = app(Pipeline::class)
                ->send($request->name)
                ->through([ContentFilter::class])
                ->thenReturn();
        }

        if ($request->description) {
            $filteredDescription = app(Pipeline::class)
                ->send($request->description)
                ->through([ContentFilter::class])
                ->thenReturn();
        }

        // get place id by slug
        $place_id = Place::where('slug', $request->place_slug)->first()->id;

        $trip->place_id = $place_id  ?? $trip->place_id;
        $trip->name = $request->name ? $filteredName : $trip->name;
        $trip->description = $request->description ? $filteredDescription : $trip->description;
        $trip->cost = $request->cost ?? $trip->cost;
        $trip->sex = $request->gender ?? $trip->gender;
        $trip->attendance_number = $request->attendance_number ?? $trip->attendance_number;

        if (isset($request->age_min) && isset($request->age_max)) {
            $age_range = json_encode(['min' => $request->age_min, 'max' => $request->age_max]);
            $trip->age_range = $age_range;
        }

        if (isset($request->date) && isset($request->time)) {
            $date_time = Carbon::createFromFormat('Y-m-d H:i:s', $request->date . ' ' . $request->time);
            $trip->date_time = $date_time;
        }

        $trip->save();

        if (isset($request->tags)) {
            $tags = collect(explode(',', $request->tags))->map(function ($tag) {
                $tagModel = Tag::where('slug', trim($tag))->first();
                return $tagModel ? $tagModel->id : null;
            })->filter()->toArray();
            $trip->tags()->sync($tags);
        }
    }

    // TODO:: SEND NOTIFICATION FOR THE USER WHEN OWNER
    public function removeUser($request)
    {
        $trip = Trip::where('slug', $request->trip_slug)->first();
        $user_id = User::where('slug', $request->user_slug)->first()->id;
        $trip->usersTrip()->where('user_id', $user_id)->delete();
        $trip->conversation->members()->where('user_id', $user_id)->delete();
    }

    public function favorite($id)
    {
        $user = Auth::guard('api')->user();
        $user->favoriteTrips()->attach($id);
    }

    public function deleteFavorite($id)
    {
        $user = Auth::guard('api')->user();
        $user->favoriteTrips()->detach($id);
    }

    public function addReview($data)
    {
        $user = Auth::guard('api')->user();
        $user->reviewTrip()->attach($data['trip_id'], [
            'rating' => $data['rating'],
            'comment' => $data['comment']
        ]);
    }

    public function updateReview($data)
    {
        $user = Auth::guard('api')->user();
        $user->reviewTrip()->sync([$data['trip_id'] => [
            'rating' => $data['rating'],
            'comment' => $data['comment']
        ]]);
    }

    public function deleteReview($id)
    {
        $user = Auth::guard('api')->user();
        $user->reviewTrip()->detach($id);
    }

    public function allReviews($id)
    {
        //you should first make like for reviews and then retrieve it
    }

    public function reviewsLike($request)
    {
        $review = Reviewable::find($request->review_id);
        $status = $request->status == "like" ? '1' : '0';

        $existingLike = $review->like()->where('user_id', Auth::guard('api')->user()->id)->first();

        if ($existingLike) {
            if ($existingLike->pivot->status != $status) {
                $review->like()->updateExistingPivot(Auth::guard('api')->user()->id, ['status' => $status]);
            } else {
                $review->like()->detach(Auth::guard('api')->user()->id);
            }
        } else {
            $review->like()->attach(Auth::guard('api')->user()->id, ['status' => $status]);
        }
    }

    public function search($query)
    {
        $perPage =  config('app.pagination_per_page');


        $trips = Trip::where(function ($q) use ($query) {
            $q->where('name', 'like', "%$query%")
                ->orWhere('description', 'like', "%$query%");
        })
            ->whereHas('user', function ($query) {
                $query->where('status', '1'); // Ensure only trips where the owner is active
            })
            ->paginate($perPage);

        $tripsArray = $trips->toArray();
        $pagination = [
            'next_page_url' => $tripsArray['next_page_url'],
            'prev_page_url' => $tripsArray['next_page_url'],
            'total' => $tripsArray['total'],
        ];
        if ($query) {
            activityLog('trip', $trips->first(), $query, 'search');
        }
        // Pass user coordinates to the PlaceResource collection
        return [
            'trips' => TripResource::collection($trips),
            'pagination' => $pagination
        ];
    }

    private function handleTripTypeNotifications($request, $trip)
    {
        $user = Auth::guard('api')->user();
        if ($request->trip_type == 1) {
            $followers = $user->followers()->get();

            foreach ($followers as $follower) {
                $receiverLanguage = $follower->lang;

                $notificationData = [
                    'title' => Lang::get('app.notifications.new-trip-title', [], $receiverLanguage),
                    'body'  => Lang::get('app.notifications.new-trip-body', ['username' => $user->username], $receiverLanguage),
                    'icon'  => asset('assets/icon/trip.png'),
                    'sound' => 'default',
                ];

                Notification::send($follower, new TripNewTripNotification($user, $trip));

                if ($follower->DeviceToken?->token) {
                    sendNotification([$follower->DeviceToken->token], $notificationData);
                }
            }
        } elseif ($request->trip_type == 2) {
            $slugs = explode(',', $request->users);
            $invitedUsers = User::whereIn('slug', $slugs)->get();

            foreach ($invitedUsers as $invitedUser) {

                $receiverLanguage = $invitedUser->lang;
                $notificationData = [
                    'title' => Lang::get('app.notifications.new-trip-invitation-title', [], $receiverLanguage),
                    'body'  => Lang::get('app.notifications.new-trip-invitation-body', ['username' => $user->username], $receiverLanguage),
                    'icon'  => asset('assets/icon/trip.png'),
                    'sound' => 'default',
                ];

                UsersTrip::create([
                    'trip_id' => $trip->id,
                    'user_id' => $invitedUser->id,
                    'status' => '0',
                ]);

                Notification::send($invitedUser, new TripNewTripNotification($user, $trip));

                if ($invitedUser->DeviceToken?->token) {
                    sendNotification([$invitedUser->DeviceToken->token], $notificationData);
                }
            }
        }
    }

    private function checkIfTheUserHasAlreadyJoined($user_id, $trip_id)
    {
        $checkUser = UsersTrip::where('trip_id', $trip_id)->where('user_id', $user_id)->where('status', '3')->first();
        if ($checkUser) {
            $checkUser->status = '0';
            $checkUser->save();
            return false;
        }
        return true;
    }
}
