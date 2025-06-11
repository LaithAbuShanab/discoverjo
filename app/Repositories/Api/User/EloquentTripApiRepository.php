<?php

namespace App\Repositories\Api\User;

use App\Events\GroupMemberEvent;
use App\Http\Resources\PrivateTripResource;
use App\Http\Resources\TagsResource;
use App\Http\Resources\TripDetailsResource;
use App\Http\Resources\TripResource;
use App\Interfaces\Gateways\Api\User\TripApiRepositoryInterface;
use App\Models\Conversation;
use App\Models\GroupMember;
use App\Models\Place;
use App\Models\Reviewable;
use App\Models\Tag;
use App\Models\Trip;
use App\Models\User;
use App\Models\UsersTrip;
use App\Notifications\Users\Trip\AcceptCancelInvitationNotification;
use App\Notifications\Users\Trip\AcceptCancelNotification;
use App\Notifications\Users\Trip\DeleteTripNotification;
use App\Notifications\Users\Trip\NewRequestNotification;
use App\Notifications\Users\Trip\NewTripNotification as TripNewTripNotification;
use App\Notifications\Users\Trip\RemoveUserTripNotification;
use Carbon\Carbon;
use Illuminate\Notifications\DatabaseNotification;
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
        $user = Auth::guard('api')->user();
        $userId = $user->id;
        $userAge = Carbon::parse($user->birthday)->age;
        $userSex = $user->sex;

        // Trips created by the user (should bypass all filters)
        $ownTrips = Trip::where('user_id', $userId)
            ->whereHas('user', fn($q) => $q->where('status', '1'))
            ->where('status', '1');

        // Trips not created by the user but pass all filters
        $otherTrips = Trip::where('user_id', '!=', $userId)
            ->whereHas('user', fn($q) => $q->where('status', '1'))
            ->where('status', '1')
            ->where(fn($q) => $this->applyTripTypeVisibility($q, $userId))
            ->where(fn($q) => $this->applyCapacityCheck($q))
            ->where(fn($q) => $this->applySexAndAgeFilter($q, $userId, $userSex, $userAge));

        // Merge and sort
        $trips = $ownTrips->union($otherTrips)->orderBy('date_time')->get();

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
        $userId = Auth::guard('api')->user()->id;

        $userTrips = UsersTrip::where('user_id', $userId)
            ->where('status', '1')
            ->pluck('trip_id')
            ->toArray();

        $trips = Trip::where(function ($query) use ($userId, $userTrips) {
            $query->where('user_id', $userId)
                ->orWhereIn('id', $userTrips);
        })
            ->whereIn('status', ['0', '1'])
            ->get();

        return PrivateTripResource::collection($trips);
    }

    public function createTrip($request)
    {
        DB::beginTransaction();

        try {
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
            $trip->place_id = Place::where('slug', $request->place_slug)->first()->id ?? null;

            // Rollback if place not found
            if (!$trip->place_id) {
                DB::rollBack();
                return response()->json(['error' => 'Invalid place_slug'], 422);
            }

            $trip->trip_type = $request->trip_type;
            $trip->name = $filteredName;
            $trip->description = $filteredDescription;
            $trip->cost = $request->cost ?? null;
            $trip->age_range = $ageRange ?? null;
            $trip->sex = $request->trip_type == 2 ? 0 : $request->gender;
            $trip->date_time = $dateTime;
            $trip->attendance_number = $request->trip_type == 2
                ? User::whereIn('slug', explode(',', $request->users))->count()
                : $request->attendance_number;
            $trip->save();
            $trip->tags()->attach($tags);

            // Create Trip Conversation
            $conversation = new Conversation();
            $conversation->trip_id = $trip->id;
            $conversation->save();

            $first_member = new GroupMember();
            $first_member->user_id = Auth::guard('api')->user()->id;
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

            // Send notification for specific user & Followers of this user
            $this->handleTripTypeNotifications($request, $trip);

            DB::commit(); // ✅ Commit only if everything passes

            return new TripResource($createdTrip);
        } catch (\Throwable $e) {
            DB::rollBack(); // ❌ Rollback on failure
            throw $e; // Or return error response
        }
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

            $tokens = $user->DeviceTokenMany->pluck('token')->toArray();
            if (!empty($tokens)) {
                sendNotification($tokens, $notificationData);
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
        $tokens = Trip::find($trip_id)->user->DeviceTokenMany->pluck('token')->toArray();
        $receiverLanguage = Trip::find($trip_id)->user->lang;
        $notificationData = [
            'title' => Lang::get('app.notifications.new-request', [], $receiverLanguage),
            'body' => Lang::get('app.notifications.new-user-request-from-trip', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
            'icon'  => asset('assets/icon/trip.png'),
            'sound' => 'default',
        ];
        sendNotification($tokens, $notificationData);
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
        $tokens = $user->DeviceTokenMany->pluck('token')->toArray();
        if (!empty($tokens)) {
            sendNotification($tokens, $notificationData);
        }
    }

    public function tripDetails($slug)
    {
        $trip = Trip::where('slug', $slug)->first();
        return new TripDetailsResource($trip);
    }

    // WHEN USER LEAVE THE TRIP

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
        $trip = Trip::with(['usersTrip.user.DeviceTokenMany', 'conversation'])->where('slug', $slug)->firstOrFail();

        $owner = Auth::guard('api')->user();

        // Notify active users (status = 1)
        foreach ($trip->usersTrip->where('status', '1') as $userTrip) {
            $user = $userTrip->user;

            // Send database notification
            Notification::send($user, new DeleteTripNotification($owner, $trip));

            // Send FCM notification
            $tokens = $user->DeviceTokenMany->pluck('token')->toArray();
            if (!empty($tokens)) {
                $lang = $user->lang ?? app()->getLocale();

                $title = __('app.notifications.trip-deleted', [], $lang);
                $body  = __('app.notifications.trip-deleted-body', ['username' => $owner->username], $lang);

                $notificationData = [
                    'title' => $title,
                    'body'  => $body,
                    'icon'  => asset('assets/icon/trip.png'),
                    'sound' => 'default',
                ];
                sendNotification($tokens, $notificationData);
            }
        }

        // Now update trip and user_trip statuses, and delete conversation
        $trip->status = '2';
        $trip->save();

        $trip->usersTrip()->update(['status' => '2']);

        if ($trip->conversation) {
            $trip->conversation->delete();
        }
    }

    public function update($request)
    {
        $trip = Trip::where('slug', $request->trip_slug)->firstOrFail();
        $oldType = $trip->trip_type;

        // Use pipeline for filtering if applicable
        $filteredName = $request->name ? app(Pipeline::class)
            ->send($request->name)
            ->through([ContentFilter::class])
            ->thenReturn() : $trip->name;

        $filteredDescription = $request->description ? app(Pipeline::class)
            ->send($request->description)
            ->through([ContentFilter::class])
            ->thenReturn() : $trip->description;

        $cost = $request->trip_type == 2 ? null : ($request->cost ?? $trip->cost);

        $trip->fill([
            'trip_type' => $request->trip_type ?? $trip->trip_type,
            'place_id' => Place::where('slug', $request->place_slug)->value('id') ?? $trip->place_id,
            'name' => $filteredName,
            'description' => $filteredDescription,
            'cost' => $cost,
            'sex' => $request->gender ?? $trip->gender,
            'attendance_number' => $request->attendance_number ?? $trip->attendance_number,
        ]);

        if ($request->trip_type == 2) {
            $trip->age_range = null;
        } elseif ($request->has(['age_min', 'age_max'])) {
            $trip->age_range = json_encode([
                'min' => $request->age_min,
                'max' => $request->age_max,
            ]);
        }

        if ($request->has(['date', 'time'])) {
            $trip->date_time = Carbon::createFromFormat('Y-m-d H:i:s', "{$request->date} {$request->time}");
        }

        $trip->save();

        if ($request->filled('tags')) {
            $tagIds = collect(explode(',', $request->tags))
                ->map(fn($tag) => Tag::where('slug', trim($tag))->value('id'))
                ->filter()
                ->all();

            $trip->tags()->sync($tagIds);
        }

        if ($oldType != $request->trip_type) {
            $trip->usersTrip()->delete();

            DatabaseNotification::where('type', TripNewTripNotification::class)
                ->whereJsonContains('data->options->trip_id', $trip->id)
                ->delete();

            $this->handleTripTypeNotifications($request, $trip);
        } elseif ($request->trip_type == 2 && $request->filled('users')) {
            $this->inviteNewUsersOnly($request, $trip);
        }
    }

    public function removeUser($request)
    {
        $trip = Trip::where('slug', $request->trip_slug)->first();
        $user = User::where('slug', $request->user_slug)->first();

        $user_id = $user->id;

        $trip->usersTrip()->where('user_id', $user_id)->delete();
        $trip->conversation->members()->where('user_id', $user_id)->delete();

        Notification::send($user, new RemoveUserTripNotification(Auth::guard('api')->user(), $trip));

        // To Send Notification To Owner Using Firebase Cloud Messaging
        $tokens = $user->DeviceTokenMany->pluck('token')->toArray();
        $receiverLanguage = $user->lang;
        $notificationData = [
            'title' => Lang::get('app.notifications.you-have-removed', [], $receiverLanguage),
            'body' => Lang::get('app.notifications.you-have-removed-from-trip', ['username' => Auth::guard('api')->user()->username, 'trip_name' => $trip->name], $receiverLanguage),
            'icon'  => asset('assets/icon/trip.png'),
            'sound' => 'default',
        ];
        sendNotification($tokens, $notificationData);
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
        $perPage = config('app.pagination_per_page');
        $user = Auth::guard('api')->user();
        $allTrips = null;

        if ($user) {
            $userId = $user->id;
            $userAge = Carbon::parse($user->birthday)->age;
            $userSex = $user->sex;

            $ownTrips = Trip::where('user_id', $userId)
                ->whereIn('status', [0, 1])
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%$query%")
                        ->orWhere('description', 'like', "%$query%");
                })
                ->whereHas('user', fn($q) => $q->where('status', '1'));

            $otherTrips = Trip::where('user_id', '!=', $userId)
                ->whereIn('status', [0, 1])
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%$query%")
                        ->orWhere('description', 'like', "%$query%");
                })
                ->whereHas('user', fn($q) => $q->where('status', '1'))
                ->where(fn($q) => $this->applyTripTypeVisibility($q, $userId))
                ->where(fn($q) => $this->applySexAndAgeFilter($q, $userId, $userSex, $userAge));

            $allTrips = $ownTrips->union($otherTrips)
                ->orderBy('status', 'desc')
                ->orderBy('date_time', 'desc')
                ->paginate($perPage);
        } else {
            $allTrips = Trip::where('trip_type', 0)
                ->whereIn('status', [0, 1])
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%$query%")
                        ->orWhere('description', 'like', "%$query%");
                })
                ->whereHas('user', fn($q) => $q->where('status', '1'))
                ->orderBy('status', 'desc')
                ->orderBy('date_time', 'desc')
                ->paginate($perPage);
        }

        $tripsArray = $allTrips->toArray();
        $pagination = [
            'next_page_url' => $tripsArray['next_page_url'],
            'prev_page_url' => $tripsArray['prev_page_url'],
            'total' => $tripsArray['total'],
        ];

        if ($query) {
            activityLog('trip', $allTrips->first(), $query, 'search');
        }

        return [
            'trips' => TripResource::collection($allTrips),
            'pagination' => $pagination,
        ];
    }

    private function handleTripTypeNotifications($request, $trip)
    {
        $user = Auth::guard('api')->user();
        if ($request->trip_type == 1) {
            $followers = $user->followers()->get();

            // Decode age range from trip
            $ageRange = json_decode($trip->age_range, true);
            $minAge = $ageRange['min'] ?? 0;
            $maxAge = $ageRange['max'] ?? 100;

            foreach ($followers as $follower) {
                // Skip if the trip is gender-specific and the follower doesn't match
                if ($trip->sex != 2 && $follower->sex != $trip->sex) {
                    dd($trip->sex, $follower->sex);
                    continue;
                }

                // Calculate age
                if (!$follower->birthday) {
                    dd($follower->birthday);
                    continue;
                }

                $age = \Carbon\Carbon::parse($follower->birthday)->age;

                // Skip if age not in range
                if ($age < $minAge || $age > $maxAge) {
                    dd($age, $minAge, $maxAge);
                    continue;
                }

                // Passed filters: send notifications
                $receiverLanguage = $follower->lang;
                $notificationData = [
                    'title' => Lang::get('app.notifications.new-trip-title', [], $receiverLanguage),
                    'body'  => Lang::get('app.notifications.new-trip-body', ['username' => $user->username], $receiverLanguage),
                    'icon'  => asset('assets/icon/trip.png'),
                    'sound' => 'default',
                ];

                Notification::send($follower, new TripNewTripNotification($user, $trip));

                $tokens = $follower->DeviceTokenMany->pluck('token')->toArray();
                if (!empty($tokens)) {
                    sendNotification($tokens, $notificationData);
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

                $tokens = $invitedUser->DeviceTokenMany->pluck('token')->toArray();
                if (!empty($tokens)) {
                    sendNotification($tokens, $notificationData);
                }
            }
        }
    }

    private function inviteNewUsersOnly($request, $trip)
    {
        $user = Auth::guard('api')->user();
        $requestedSlugs = explode(',', $request->users);

        $existingUserIds = $trip->usersTrip()->pluck('user_id')->toArray();

        $newUsers = User::whereIn('slug', $requestedSlugs)
            ->whereNotIn('id', $existingUserIds)
            ->get();

        foreach ($newUsers as $newUser) {
            $receiverLanguage = $newUser->lang;
            $notificationData = [
                'title' => Lang::get('app.notifications.new-trip-invitation-title', [], $receiverLanguage),
                'body'  => Lang::get('app.notifications.new-trip-invitation-body', ['username' => $user->username], $receiverLanguage),
                'icon'  => asset('assets/icon/trip.png'),
                'sound' => 'default',
            ];

            UsersTrip::create([
                'trip_id' => $trip->id,
                'user_id' => $newUser->id,
                'status' => '0',
            ]);

            Notification::send($newUser, new TripNewTripNotification($user, $trip));

            $tokens = $newUser->DeviceTokenMany->pluck('token')->toArray();
            if (!empty($tokens)) {
                sendNotification($tokens, $notificationData);
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

    private function applyTripTypeVisibility($query, $userId)
    {
        $query->where('trip_type', '0') // Public
            ->orWhere(function ($q) use ($userId) {
                $q->where('trip_type', '1') // Followers
                    ->where(function ($q) use ($userId) {
                        $q->whereHas('user.followers', fn($q) => $q->where('follower_id', $userId))
                            ->orWhere('user_id', $userId);
                    });
            })
            ->orWhere(function ($q) use ($userId) {
                $q->where('trip_type', '2') // Specific
                    ->whereHas('usersTrip', fn($q) => $q->where('user_id', $userId)->whereIn('status', ['0', '1']))
                    ->orWhere('user_id', $userId);
            });

        return $query;
    }

    private function applyCapacityCheck($query)
    {
        $currentUserId = Auth::guard('api')->id();

        $query->where(function ($q) use ($currentUserId) {
            $q->orWhere('trip_type', 2);

            $q->orWhereHas('usersTrip', function ($q2) use ($currentUserId) {
                $q2->where('user_id', $currentUserId);
            });

            $q->orWhere(function ($q3) {
                $q3->where('trip_type', '!=', 2)
                    ->whereRaw("(
                        SELECT COUNT(*) FROM users_trips
                        WHERE users_trips.trip_id = trips.id
                        AND users_trips.status = 1
                    ) < trips.attendance_number");
            });
        });

        return $query;
    }


    private function applySexAndAgeFilter($query, $userId, $userSex, $userAge)
    {
        $query->where(function ($q) use ($userId, $userSex, $userAge) {
            $q->where('user_id', $userId)
                ->orWhere(function ($q) use ($userSex, $userAge) {
                    $q->where('trip_type', '2')
                        ->orWhere(function ($q) use ($userSex, $userAge) {
                            $q->whereIn('sex', [$userSex, 0])
                                ->where(function ($q) use ($userAge) {
                                    $q->whereNull('age_range')
                                        ->orWhere(function ($q) use ($userAge) {
                                            $q->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(age_range, "$.min")) AS UNSIGNED) <= ?', [$userAge])
                                                ->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(age_range, "$.max")) AS UNSIGNED) >= ?', [$userAge]);
                                        });
                                });
                        });
                });
        });

        return $query;
    }

    public function dateTrips($date)
    {
        $perPage = config('app.pagination_per_page');
        $user = Auth::guard('api')->user();

        if ($user) {
            $userId = $user->id;
            $userAge = Carbon::parse($user->birthday)->age;
            $userSex = $user->sex;

            $ownTrips = Trip::where('user_id', $userId)
                ->whereDate('date_time', '=', $date)
                ->whereIn('status', [0, 1]) // Filter for status 0 or 1
                ->whereHas('user', fn($q) => $q->where('status', '1'));

            // Other users' trips on the specified date (apply filters, allow any status)
            $otherTrips = Trip::where('user_id', '!=', $userId)
                ->whereDate('date_time', '=', $date)
                ->whereIn('status', [0, 1]) // Filter for status 0 or 1
                ->whereHas('user', fn($q) => $q->where('status', '1'))
                ->where(fn($q) => $this->applyTripTypeVisibility($q, $userId))
                ->where(fn($q) => $this->applySexAndAgeFilter($q, $userId, $userSex, $userAge));

            // Combine and sort
            $allTrips = $ownTrips->union($otherTrips)
                ->orderBy('status', 'desc')
                ->orderBy('date_time', 'desc')
                ->paginate($perPage);
        } else {
            $allTrips = Trip::where('trip_type', 0)
                ->whereDate('date_time', '=', $date)->paginate($perPage);
        }

        $tripsArray = $allTrips->toArray();
        $pagination = [
            'next_page_url' => $tripsArray['next_page_url'],
            'prev_page_url' => $tripsArray['prev_page_url'],
            'total' => $tripsArray['total'],
        ];

        activityLog(
            'view trips in specific date',
            $allTrips->first(),
            'The user viewed trips on ' . $date,
            'view'
        );

        return [
            'trips' => TripResource::collection($allTrips),
            'pagination' => $pagination,
        ];
    }
}
