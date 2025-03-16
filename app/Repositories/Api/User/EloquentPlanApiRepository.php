<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\PlanResource;
use App\Http\Resources\SinglePlanResource;
use App\Interfaces\Gateways\Api\User\PlanApiRepositoryInterface;
use App\Models\Place;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EloquentPlanApiRepository implements PlanApiRepositoryInterface
{
    public function allPlans()
    {
        $userId = Auth::guard('api')->user()->id;
        $plans = Plan::with('days.activities')
            ->where(function ($query) use ($userId) {
                $query->where('creator_type', 'App\Models\Admin')
                    ->orWhere(function ($query) use ($userId) {
                        $query->where('creator_type', 'App\Models\User')
                            ->where('creator_id', $userId);
                    });
            })->get();
        return PlanResource::collection($plans);
    }

    public function createPlan($validatedData)
    {
        return DB::transaction(function () use ($validatedData) {
            $plan = new Plan();
            $plan->creator_type = 'App\Models\User';
            $plan->creator_id = Auth::guard('api')->user()->id;

            $plan->setTranslations('name', ['en' => $validatedData['name'], 'ar' => $validatedData['name']]);
            $plan->setTranslations('description', ['en' => $validatedData['description'], 'ar' => $validatedData['description']]);

            $plan->save();

            foreach ($validatedData['days'] as $index => $dayData) {
                $day = $plan->days()->create([
                    'plan_id' => $plan->id,
                    'day' => $index + 1,
                ]);

                foreach ($dayData['activities'] as $activityData) {
                    $place_id = Place::where('slug', $activityData['place_slug'])->first()->id;
                    $activity = $day->activities()->create([
                        'plan_day_id' => $day->id,
                        'activity_name' => $activityData['name'],
                        'start_time' => $activityData['start_time'],
                        'end_time' => $activityData['end_time'],
                        'place_id' => $place_id,
                        'notes' => $activityData['note'],
                    ]);

                    // Set Translations
                    $activity->setTranslations('activity_name', ['en' => $activityData['name'], 'ar' => $activityData['name']]);
                    $activity->setTranslations('notes', ['en' => $activityData['note'], 'ar' => $activityData['note']]);
                    $activity->save();
                }
            }
        });
    }

    public function updatePlan($validatedData)
    {
        return DB::transaction(function () use ($validatedData) {
            // Fetch the plan
            $plan = Plan::where('slug', $validatedData['plan_slug'])->firstOrFail();

            // Update Plan Details
            $plan->setTranslations('name', ['ar' => $validatedData['name'], 'en' => $validatedData['name']]);
            $plan->setTranslations('description', ['ar' => $validatedData['description'], 'en' => $validatedData['description']]);
            $plan->save();

            // Delete existing days & activities (to be reinserted)
            $plan->days()->delete();

            foreach ($validatedData['days'] as $index => $dayData) {
                // Create new day
                $day = $plan->days()->create([
                    'plan_id' => $plan->id,
                    'day' => $index + 1,
                ]);

                foreach ($dayData['activities'] as $activityData) {
                    // Fetch place_id using slug
                    $place = Place::where('slug', $activityData['place_slug'])->first();

                    // Create Activity
                    $activity = $day->activities()->create([
                        'plan_day_id' => $day->id,
                        'activity_name' => $activityData['name'],
                        'start_time' => $activityData['start_time'],
                        'end_time' => $activityData['end_time'],
                        'place_id' => $place->id,
                        'notes' => $activityData['note'] ?? null,
                    ]);

                    // Set Translations
                    $activity->setTranslations('activity_name', ['en' => $activityData['name'], 'ar' => $activityData['name']]);
                    $activity->setTranslations('notes', ['en' => $activityData['note'] ?? '', 'ar' => $activityData['note'] ?? '']);
                    $activity->save();
                }
            }
        });
    }

    public function plans()
    {
        $perPage = 15;
        $allPlans = Plan::where('creator_type', 'App\Models\Admin')->with('days')->paginate($perPage);
        //        $plansArray = $plans->toArray();
        // Convert paginated items to a collection and shuffle
        $shuffledPlans = $allPlans->getCollection()->shuffle();

        // Replace the collection in the paginator with the shuffled plans
        $allPlans->setCollection($shuffledPlans);


        $pagination = [
            'next_page_url' => $allPlans['next_page_url'],
            'prev_page_url' => $allPlans['next_page_url'],
            'total' => $allPlans['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'plans' => PlanResource::collection($allPlans),
            'pagination' => $pagination
        ];
    }

    public function deletePlan($slug)
    {
        $plan = Plan::where('slug', $slug)->delete();
    }

    public function show($slug)
    {
        $plan = Plan::where('slug', $slug)->firstOrFail();
        return new SinglePlanResource($plan);
    }

    public function myPlans()
    {
        return PlanResource::collection(Auth::guard('api')->user()->plans);
    }

    public function favorite($id)
    {
        $user = Auth::guard('api')->user();
        $user->favoritePlans()->attach($id);
    }

    public function deleteFavorite($id)
    {
        $user = Auth::guard('api')->user();
        $user->favoritePlans()->detach($id);
    }

    public function search($query)
    {
        $perPage = config('app.pagination_per_page');
        // Check if the user is authenticated
        if (!Auth::guard('api')->user()) {
            $plans = Plan::where(function ($queryBuilder) use ($query) {
                $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
            })->where('creator_type', 'App\Models\Admin')->paginate($perPage);
        } else {
            $plans = Plan::where(function ($queryBuilder) use ($query) {
                $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
            })->where(function ($queryBuilder) {
                $queryBuilder->where('creator_type', 'App\Models\Admin')
                    ->orWhere(function ($queryBuilder) {
                        $queryBuilder->where('creator_type', 'App\Models\User')
                            ->where('creator_id', Auth::guard('api')->user()->id);
                    });
            })->paginate($perPage);
        }

        $plansArray = $plans->toArray();

        $pagination = [
            'next_page_url' => $plansArray['next_page_url'],
            'prev_page_url' => $plansArray['next_page_url'],
            'total' => $plansArray['total'],
        ];
        activityLog('plan', $plans->first(), $query, 'search');

        // Pass user coordinates to the PlaceResource collection
        return [
            'plans' => PlanResource::collection($plans),
            'pagination' => $pagination
        ];
    }

    public function filter($data)
    {
        $perPage = 20;
        $numberOfDays = $data['number_of_days'] ?? null;
        $regionId = $data['region_id'] ?? null;

        // Base query for filtering plans
        $baseQuery = Plan::query();

        // Check if the user is authenticated
        if (!Auth::guard('api')->user()) {
            $baseQuery->where('creator_type', 'App\Models\Admin');
        } else {
            $baseQuery->where(function ($queryBuilder) {
                $queryBuilder->where('creator_type', 'App\Models\Admin')
                    ->orWhere(function ($queryBuilder) {
                        $queryBuilder->where('creator_type', 'App\Models\User')
                            ->where('creator_id', Auth::guard('api')->user()->id);
                    });
            });
        }

        $baseQuery->when($numberOfDays != null, function ($queryBuilder) use ($numberOfDays) {
            $queryBuilder->whereHas('activities', function ($queryBuilder) use ($numberOfDays) {
                $queryBuilder->where('day_number', $numberOfDays);
            });
        });
        // Apply filters independently

        $baseQuery->when($regionId != null, function ($queryBuilder) use ($regionId) {
            $queryBuilder->whereHas('activities.place', function ($queryBuilder) use ($regionId) {
                $queryBuilder->where('region_id', $regionId);
            });
        });


        // Execute the query and get the results
        $plans = $baseQuery->paginate($perPage);

        // Prepare pagination data
        $plansArray = $plans->toArray();
        $pagination = [
            'next_page_url' => $plansArray['next_page_url'] ?? null,
            'prev_page_url' => $plansArray['prev_page_url'] ?? null,
            'total' => $plansArray['total'] ?? 0,
        ];

        // Prepare response data
        $response = [
            'plans' => PlanResource::collection($plans),
            'pagination' => $pagination
        ];

        return $response;
    }
}
