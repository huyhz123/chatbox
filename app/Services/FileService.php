<?php

namespace App\Services;

use App\Models\File;
use App\Models\User;
use App\Models\FileDownload;

class FileService
{
    public function unlockFile(User $user, File $file)
    {
        $download = FileDownload::firstOrCreate([
            'file_id' => $file->id,
            'user_id' => $user->id,
        ], [
            'ip_address' => request()->ip(),
            'download_count' => 0,
        ]);

        // Increment sold count
        $file->incrementSoldCount();

        return $download;
    }

    public function canUserDownload(User $user, File $file)
    {
        $download = FileDownload::where('user_id', $user->id)
            ->where('file_id', $file->id)
            ->first();

        if (!$download) {
            return false;
        }

        return $download->canDownload();
    }

    public function recordDownload(User $user, File $file)
    {
        $download = FileDownload::where('user_id', $user->id)
            ->where('file_id', $file->id)
            ->first();

        if (!$download || !$download->canDownload()) {
            throw new \Exception('Download limit exceeded or access denied');
        }

        $download->incrementDownload();

        return true;
    }

    public function getDownloadUrl(File $file)
    {
        // Generate temporary signed URL for secure download
        return route('files.download', [
            'file' => $file->id,
            'token' => encrypt([
                'file_id' => $file->id,
                'expires' => now()->addMinutes(30)->timestamp,
            ])
        ]);
    }
}
