<?php

namespace App\Filament\Pages;

use FilipFonal\FilamentLogManager\Pages\Logs;
use Symfony\Component\Finder\SplFileInfo;
class CustomLogs extends Logs
{
    public function getFileNames($files): \Illuminate\Support\Collection
    {
        return collect($files)->mapWithKeys(function (SplFileInfo $file) {
            $filename = $file->getFilename();
            return [$filename => $filename];
        });
    }

}
