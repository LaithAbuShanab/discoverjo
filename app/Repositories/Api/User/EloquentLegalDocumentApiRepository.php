<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\LegalResource;
use App\Http\Resources\TopTenPlaceResource;
use App\Interfaces\Gateways\Api\User\LegalDocumentApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\TopTenPlaceApiRepositoryInterface;
use App\Models\LegalDocument;
use App\Models\TopTen;


class EloquentLegalDocumentApiRepository implements LegalDocumentApiRepositoryInterface
{



    public function getAllLegalDocument()
    {
        $legalDocuments = LegalDocument::with('terms')->get();

        $groupedDocuments = $legalDocuments->groupBy('type');

        $formattedData = [];
        foreach ($groupedDocuments as $type => $documents) {
            $typeName = $type == 1 ? 'Privacy And Policy' : 'Terms Of Service';
            $formattedData[] = [
                $typeName => LegalResource::collection($documents)
            ];
        }
        activityLog('legal Document',$legalDocuments->first(), 'the user view privacy and policy','view');
        $lastLegalDate = LegalDocument::latest('updated_at')->first()?->updated_at?->toDateString();

        return[
            'last_updated' => $lastLegalDate,
            'data' => $formattedData,
        ];
    }


}
