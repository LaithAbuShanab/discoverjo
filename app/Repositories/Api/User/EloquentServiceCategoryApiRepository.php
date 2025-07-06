<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllCategoriesResource;
use App\Http\Resources\AllServiceCategoriesResource;
use App\Http\Resources\AllServicesResource;
use App\Http\Resources\AllServiceSubCategoriesResource;
use App\Http\Resources\SingleServiceResource;
use App\Interfaces\Gateways\Api\User\ServiceCategoryApiRepositoryInterface;
use App\Models\Service;
use App\Models\ServiceCategory;


class EloquentServiceCategoryApiRepository implements ServiceCategoryApiRepositoryInterface
{
    public function allServiceCategories()
    {
        $eloquentCategories = ServiceCategory::whereNull('parent_id')->orderBy('priority')->get();
        return AllServiceCategoriesResource::collection($eloquentCategories);
    }

    public function allServiceByCategory($data)
    {
        $perPage = config('app.pagination_per_page');
        $category = ServiceCategory::with('children')->where('slug', $data['category_slug'])->first();

        $allSubcategories = $category->children()->whereHas('services')->get();

        $services = Service::where('status', 1)
            ->whereHas('categories', function ($query) use ($category) {
                $query->where('service_category_id', $category->id)
                    ->orWhereIn('service_category_id', $category->children->pluck('id'));
            })
            ->paginate($perPage);

        $servicesArray = $services->toArray();
        $parameterNext = $servicesArray['next_page_url'] ;
        $parameterPrevious = $servicesArray['prev_page_url'];


        $pagination = [
            'next_page_url' => $parameterNext,
            'prev_page_url' => $parameterPrevious,
            'total' => $servicesArray['total'],
        ];
        activityLog('service category',$category, 'the user view this service category ','view');

        return [
            'category' => new AllCategoriesResource($category),
            'sub_categories' => AllServiceSubCategoriesResource::collection($allSubcategories),
            'services' => AllServicesResource::collection($services),
            'pagination' => $pagination
        ];
    }

    public function allSubcategories($data)
    {
        $subcategories = ServiceCategory::whereIn('slug', $data)->with('children')->get();
        $allChildren = $subcategories->pluck('children')->flatten();
        $stringData = implode(", ", $data);
        activityLog('view specific services categories ',$subcategories->first(), 'the user view these categories','view',[
            'categories'     => $stringData,
        ]);
        return AllServiceSubCategoriesResource::collection($allChildren);
    }

    public function search($query)
    {
        $categories = ServiceCategory::where(function ($queryBuilder) use ($query) {
            $queryBuilder->where('name_en', 'like', '%' . $query . '%')
                ->orWhere('name_ar', 'like', '%' . $query . '%');
        })->whereNull('parent_id')->get();

        if($query){
            activityLog('search for service category ',$categories->first(), $query,'search');
        }
        return AllServiceCategoriesResource::collection($categories);
    }

    public function serviceSearch($query)
    {
        $perPage = config('app.pagination_per_page');
        $lowerQuery = strtolower($query);

        $services = Service::where(function ($q) use ($lowerQuery) {
            $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", ["%{$lowerQuery}%"])
                ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))) LIKE ?", ["%{$lowerQuery}%"]);
        })->paginate($perPage);

        $pagination = [
            'next_page_url' => $services->nextPageUrl(),
            'prev_page_url' => $services->previousPageUrl(),
            'total'         => $services->total(),
        ];

        if (!empty($query) && $services->isNotEmpty()) {
            activityLog('Searched for service', $services->first(), $query, 'search');
        }

        return [
            'services'   => AllServicesResource::collection($services),
            'pagination' => $pagination,
        ];
    }


    //transfer to service controller
    public function dateServices($date)
    {
        $perPage = config('app.pagination_per_page');

        $services = Service::whereHas('serviceBookings', function ($query) use ($date) {
                $query->whereDate('available_start_date', '<=', $date)
                    ->whereDate('available_end_date', '>=', $date);
            })
            ->whereHas('provider', function ($query) {
                $query->where('status', 1);
            })
            ->with('serviceBookings') // optional: eager load bookings if needed
            ->orderBy('created_at', 'desc')
            ->paginate($perPage); // you can replace this with any other valid field


        $servicesArray = $services->toArray();

        $pagination = [
            'next_page_url' => $servicesArray['next_page_url'],
            'prev_page_url' => $servicesArray['next_page_url'],
            'total' => $servicesArray['total'],
        ];

        activityLog('view service in specific date',$services->first(),'The user viewed service in specific date '.$date['date'],'view');

        // Pass user coordinates to the PlaceResource collection
        return [
            'events' => AllServicesResource::collection($services),
            'pagination' => $pagination
        ];

    }

    public function singleService($slug)
    {
        $service = Service::findBySlug($slug);
        return new SingleServiceResource($service);

    }

    public function servicesBySubcategory($slug)
    {
        $perPage = config('app.pagination_per_page');
        $subcategory = ServiceCategory::findBySlug($slug);

        $services = Service::where('status', 1)
            ->whereHas('categories', function ($query) use ($subcategory) {
                $query->where('service_category_id', $subcategory->id);
            })
            ->paginate($perPage);

        $parent = $subcategory?->parent;

        $servicesArray = $services->toArray();
        $parameterNext = $servicesArray['next_page_url'] ;
        $parameterPrevious = $servicesArray['prev_page_url'];


        $pagination = [
            'next_page_url' => $parameterNext,
            'prev_page_url' => $parameterPrevious,
            'total' => $servicesArray['total'],
        ];
        activityLog('service subcategory',$subcategory, 'the user view this service subcategory ','view');

        return [
            'parent'=> new AllServiceCategoriesResource($parent),
            'subcategory' => new AllServiceSubCategoriesResource($subcategory),
            'services' => AllServicesResource::collection($services),
            'pagination' => $pagination
        ];


    }

}
