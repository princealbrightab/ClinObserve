<?php

namespace App\Console\Commands;

use App\Models\EncounterImage;
use App\Models\StudentProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AuditPrivateFiles extends Command
{
    protected $signature = 'clinobserve:audit-files';

    protected $description = 'Read-only audit of missing and unreferenced private images';

    public function handle(): int
    {
        $disk = Storage::disk('clinical');
        $referenced = EncounterImage::pluck('file_path')->merge(StudentProfile::whereNotNull('avatar_path')->pluck('avatar_path'))->unique();
        $missing = $referenced->filter(fn ($path) => ! $disk->exists($path))->count();
        $unreferenced = collect($disk->allFiles())->diff($referenced)->count();
        $this->table(['Referenced files', 'Missing files', 'Unreferenced files'], [[$referenced->count(), $missing, $unreferenced]]);
        $this->info('Read-only audit. No files were deleted. Investigate discrepancies before any manual cleanup.');

        return $missing || $unreferenced ? self::FAILURE : self::SUCCESS;
    }
}
