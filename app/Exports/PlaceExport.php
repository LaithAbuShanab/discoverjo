<?php
    namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PlaceExport implements FromCollection, WithHeadings
{
    private $gridPoints;

    public function __construct($gridPoints)
    {
        $this->gridPoints = $gridPoints;
    }

    public function collection()
    {
        return collect($this->gridPoints)->map(function ($item) {
            $place['id']= $item->id;
            $place['name']= $item->name;
            $place['description']= $item->description;
            $place['address']= $item->address;
            $place['business_status']= $item->business_status;
            $place['google_map_url']= $item->google_map_url;
            return $place;
        });
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'description',
            'address',
            'business_status',
            'google_map_url',
        ];
    }
}
