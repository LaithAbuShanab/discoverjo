<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\PlanResource;
use App\Http\Resources\SinglePlanResource;
use App\Interfaces\Gateways\Api\User\PlanApiRepositoryInterface;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EloquentPlanApiRepository implements PlanApiRepositoryInterface
{
    public function allPlans()
    {
        $userId = Auth::guard('api')->user()->id;
        $plans = Plan::with('activities')
            ->where(function ($query) use ($userId) {
                $query->where('creator_type', 'App\Models\Admin')
                    ->orWhere(function ($query) use ($userId) {
                        $query->where('creator_type', 'App\Models\User')
                            ->where('creator_id', $userId);
                    });
            })->get();
        return PlanResource::collection($plans);
    }

    public function plans()
    {
        $perPage = 15;
        $allPlans = Plan::where('creator_type', 'App\Models\Admin')->with('activities')->paginate($perPage);
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


    public function createPlan($validatedData)
    {
        $plan = new Plan();
        $plan->setTranslations('name', ['en' => $validatedData['name'], 'ar' => $validatedData['name']]);
        $plan->setTranslations('description', ['en' => $validatedData['description'], 'ar' => $validatedData['description']]);
        $plan->creator_type = 'App\Models\User';
        $plan->creator_id = Auth::guard('api')->user()->id;
        $plan->save();

        foreach ($validatedData['days'] as $index => $day) {
            foreach ($day['activities'] as $activityData) {
                $activity = $plan->activities()->create([
                    'activity_name' => $activityData['name'],
                    'day_number' => $index + 1,
                    'start_time' => $activityData['start_time'],
                    'end_time' => $activityData['end_time'],
                    'place_id' => $activityData['place_id'],
                    'notes' => $activityData['note'],
                ]);
                $activity->setTranslations('activity_name', ['en' => $activityData['name'], 'ar' => $activityData['name'],]);
                $activity->setTranslations('notes', ['en' => $activityData['note'], 'ar' => $activityData['note'],]);
                $activity->save();
            }
        }
    }

    public function updatePlan($validatedData)
    {
        $plan = Plan::find($validatedData['plan_id']);
        $plan->activities()->delete();

        $plan->update(['name' => $validatedData['name'], 'description' => $validatedData['description']]);
        $plan->setTranslations('name', ['ar' => $validatedData['name'], 'en' => $validatedData['name']]);
        $plan->setTranslations('description', ['ar' => $validatedData['description'], 'en' => $validatedData['description']]);
        $plan->save();

        foreach ($validatedData['days'] as $index => $day) {
            foreach ($day['activities'] as $activityData) {
                $activity = $plan->activities()->create([
                    'day_number' => $index + 1,
                    'activity_name' => $activityData['name'],
                    'start_time' => $activityData['start_time'],
                    'end_time' => $activityData['end_time'],
                    'place_id' => $activityData['place_id'],
                    'notes' => $activityData['note'] ?? null,
                ]);
                $activity->setTranslations('activity_name', ['en' => $activityData['name'], 'ar' => $activityData['name']]);
                $activity->setTranslations('notes', ['en' => $activityData['note'], 'ar' => $activityData['note']]);
                $activity->save();
            }
        }
    }


    public function deletePlan($id)
    {
        $plan = Plan::find($id)->delete();
    }

    public function show($id)
    {
        $plan = Plan::find($id);
        return new SinglePlanResource($plan);
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
        $perPage = 15;
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

    public function myPlans()
    {
        return PlanResource::collection(Auth::guard('api')->user()->plans);
    }
}
