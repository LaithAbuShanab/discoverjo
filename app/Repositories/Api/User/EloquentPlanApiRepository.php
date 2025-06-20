<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\PlanResource;
use App\Http\Resources\SinglePlanResource;
use App\Interfaces\Gateways\Api\User\PlanApiRepositoryInterface;
use App\Models\Place;
use App\Models\Plan;
use App\Models\Region;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LevelUp\Experience\Models\Activity;

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
            $user = Auth::guard('api')->user();
            $user->addPoints(10);
            $activity = Activity::find(1);
            $user->recordStreak($activity);
        });
    }

    public function updatePlan($validatedData)
    {
        return DB::transaction(function () use ($validatedData) {
            // Fetch the plan
            $plan = Plan::where('slug', $validatedData['plan_slug'])->firstOrFail();
            $plan->creator_type = 'App\Models\User';
            $plan->creator_id = Auth::guard('api')->user()->id;
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
        $plan = Plan::where('slug', $slug)->first();
        $plan->delete();
        $user = Auth::guard('api')->user();
        $user->deductPoints(10);
    }

    public function show($slug)
    {
        $plan = Plan::where('slug', $slug)->firstOrFail();
        activityLog('plan', $plan, 'The user viewed plan', 'view');

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
        $user = Auth::guard('api')->user();

        $plansQuery = Plan::query()
            ->when($query, function ($q) use ($query) {
                $q->where(function ($q2) use ($query) {
                    $q2->where('name_en', 'like', '%' . $query . '%')
                        ->orWhere('name_ar', 'like', '%' . $query . '%');
                });
            });


        if (!$user) {
            $plansQuery->where('creator_type', 'App\Models\Admin');
        } else {
            $plansQuery->where(function ($q) use ($user) {
                $q->where('creator_type', 'App\Models\Admin')
                    ->orWhere(function ($subQ) use ($user) {
                        $subQ->where('creator_type', 'App\Models\User')
                            ->where('creator_id', $user->id);
                    });
            });
        }

        $plans = $plansQuery->paginate($perPage);
        $plansArray = $plans->toArray();

        $pagination = [
            'next_page_url' => $plansArray['next_page_url'],
            'prev_page_url' => $plansArray['next_page_url'],
            'total' => $plansArray['total'],
        ];
        if ($query) {
            activityLog('plan', $plans->first(), $query, 'search');
        }
        // Pass user coordinates to the PlaceResource collection
        return [
            'plans' => PlanResource::collection($plans),
            'pagination' => $pagination
        ];
    }

    public function filter($data)
    {
        $perPage = config('app.pagination_per_page');
        $numberOfDays = $data['number_of_days'] ?? null;
        $regionSlug = $data['region'] ?? null;
        $regionId = null;

        // Get region ID from slug
        if ($regionSlug !== null) {
            $regionId = Region::findBySlug($regionSlug)?->id;
        }

        // Start building base query
        $baseQuery = Plan::query();

        // Authenticated user filtering
        if (!Auth::guard('api')->user()) {
            $baseQuery->where('creator_type', 'App\Models\Admin');
        } else {
            $baseQuery->where(function ($query) {
                $query->where('creator_type', 'App\Models\Admin')
                    ->orWhere(function ($query) {
                        $query->where('creator_type', 'App\Models\User')
                            ->where('creator_id', Auth::guard('api')->user()->id);
                    });
            });
        }

        // Apply OR-based filtering logic
        if ($numberOfDays !== null || $regionId !== null) {
            $baseQuery->where(function ($query) use ($numberOfDays, $regionId) {

                // Filter by number of days
                if ($numberOfDays !== null) {
                    $query->orWhereExists(function ($subQuery) use ($numberOfDays) {
                        $subQuery->select(DB::raw(1))
                            ->from('plan_days')
                            ->whereRaw('plans.id = plan_days.plan_id')
                            ->groupBy('plan_id')
                            ->havingRaw('COUNT(*) = ?', [$numberOfDays]);
                    });
                }

                // Filter by region
                if ($regionId !== null) {
                    $query->whereHas('days.activities.place', function ($q) use ($regionId) {
                        $q->where('region_id', $regionId);
                    });
                }

            });
        }

        // Execute query with pagination
        $plans = $baseQuery->paginate($perPage);

        // Prepare pagination info
        $plansArray = $plans->toArray();
        $pagination = [
            'next_page_url' => $plansArray['next_page_url'] ?? null,
            'prev_page_url' => $plansArray['prev_page_url'] ?? null,
            'total' => $plansArray['total'] ?? 0,
        ];

        // Prepare final response
        $response = [
            'plans' => PlanResource::collection($plans),
            'pagination' => $pagination,
        ];

        // Optional: Log filtered results for debugging
        $this->logFilteredPlans($baseQuery->get(), $numberOfDays, $regionSlug);

        return $response;
    }

    private function logFilteredPlans($plans, $numberOfDays, $regionSlug): void
    {
        foreach ($plans as $plan) {
            if ($plan instanceof \Illuminate\Database\Eloquent\Model) {
                activityLog(
                    'filter plans',
                    $plan,
                    'The user filtered the places',
                    'filter',
                    [
                        'number_of_days' => $numberOfDays,
                        'region'         => $regionSlug,
                    ]
                );
            }
        }
    }
}
