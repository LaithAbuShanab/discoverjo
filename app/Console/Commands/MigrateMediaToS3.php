<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;

class MigrateMediaToS3 extends Command
{
    protected $signature = 'media:migrate-to-s3';
    protected $description = 'Move media files from local to S3';

    public function handle()
    {
        $mediaItems = Media::where('disk', 'media')->get();
        $bar = $this->output->createProgressBar($mediaItems->count());

        foreach ($mediaItems as $media) {
            $localDisk = Storage::disk('media');
            $s3Disk = Storage::disk('s3');

            $originalPath = $media->getPath(); // absolute path
            $relativePath = $media->getPathRelativeToRoot();

            if ($localDisk->exists($relativePath)) {
                $fileContent = $localDisk->get($relativePath);

                // 🔧 تم حذف 'public'
                $s3Disk->put($relativePath, $fileContent);

                // تحديث قاعدة البيانات
                $media->disk = 's3';
                $media->save();
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nMigration complete!");
    }
}

