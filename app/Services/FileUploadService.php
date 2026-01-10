<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;

class FileUploadService
{
    /**
     * Upload avatar image
     */
    public function uploadAvatar(UploadedFile $file, ?string $oldPath = null): string
    {
        // Delete old file
        if ($oldPath) {
            $this->deleteFile($oldPath);
        }

        // Store new file
        $filename = 'avatar_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('avatars/' . date('Y/m'), $filename, 'public');

        return $path;
    }

    /**
     * Upload cover photo
     */
    public function uploadCoverPhoto(UploadedFile $file, ?string $oldPath = null): string
    {
        if ($oldPath) {
            $this->deleteFile($oldPath);
        }

        $filename = 'cover_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('covers/' . date('Y/m'), $filename, 'public');

        return $path;
    }

    /**
     * Upload video with thumbnail generation
     */
    public function uploadVideo(UploadedFile $file, string $type = 'videos'): array
    {
        // Store video
        $filename = 'video_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $videoPath = $file->storeAs($type . '/' . date('Y/m'), $filename, 'public');

        // Get video metadata
        $duration = $this->getVideoDuration($file->getRealPath());
        $dimensions = $this->getVideoDimensions($file->getRealPath());

        // Generate thumbnail
        $thumbnailPath = $this->generateVideoThumbnail($file->getRealPath(), $type);

        return [
            'video_path' => $videoPath,
            'video_url' => Storage::url($videoPath),
            'thumbnail_path' => $thumbnailPath,
            'thumbnail_url' => $thumbnailPath ? Storage::url($thumbnailPath) : null,
            'duration' => $duration,
            'width' => $dimensions['width'] ?? null,
            'height' => $dimensions['height'] ?? null,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Upload image
     */
    public function uploadImage(UploadedFile $file, string $type = 'images'): array
    {
        $filename = 'img_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($type . '/' . date('Y/m'), $filename, 'public');

        // Get image dimensions
        $dimensions = getimagesize($file->getRealPath());

        return [
            'path' => $path,
            'url' => Storage::url($path),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Upload audio file
     */
    public function uploadAudio(UploadedFile $file, string $type = 'audio'): array
    {
        $filename = 'audio_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($type . '/' . date('Y/m'), $filename, 'public');

        // Get duration if possible
        $duration = $this->getAudioDuration($file->getRealPath());

        return [
            'path' => $path,
            'url' => Storage::url($path),
            'duration' => $duration,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Upload generic file
     */
    public function uploadFile(UploadedFile $file, string $type = 'files'): array
    {
        $filename = 'file_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($type . '/' . date('Y/m'), $filename, 'public');

        return [
            'path' => $path,
            'url' => Storage::url($path),
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Delete file from storage
     */
    public function deleteFile(string $path): bool
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }
            return true;
        } catch (Exception $e) {
            \Log::error('Failed to delete file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get video duration in seconds
     */
    private function getVideoDuration(string $filePath): ?int
    {
        try {
            // Using getID3 library (install: composer require james-heinrich/getid3)
            if (class_exists('\getID3')) {
                $getID3 = new \getID3();
                $fileInfo = $getID3->analyze($filePath);
                return isset($fileInfo['playtime_seconds']) ? (int) $fileInfo['playtime_seconds'] : null;
            }

            // Fallback: Using ffprobe command (if FFmpeg is installed)
            if ($this->commandExists('ffprobe')) {
                $command = sprintf(
                    'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s',
                    escapeshellarg($filePath)
                );
                $duration = shell_exec($command);
                return $duration ? (int) floatval($duration) : null;
            }

            return null;
        } catch (Exception $e) {
            \Log::error('Failed to get video duration: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get video dimensions
     */
    private function getVideoDimensions(string $filePath): array
    {
        try {
            if (class_exists('\getID3')) {
                $getID3 = new \getID3();
                $fileInfo = $getID3->analyze($filePath);
                return [
                    'width' => $fileInfo['video']['resolution_x'] ?? null,
                    'height' => $fileInfo['video']['resolution_y'] ?? null,
                ];
            }

            // Fallback: Using ffprobe
            if ($this->commandExists('ffprobe')) {
                $command = sprintf(
                    'ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=s=x:p=0 %s',
                    escapeshellarg($filePath)
                );
                $output = shell_exec($command);
                if ($output) {
                    [$width, $height] = explode('x', trim($output));
                    return ['width' => (int) $width, 'height' => (int) $height];
                }
            }

            return [];
        } catch (Exception $e) {
            \Log::error('Failed to get video dimensions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get audio duration
     */
    private function getAudioDuration(string $filePath): ?int
    {
        try {
            if (class_exists('\getID3')) {
                $getID3 = new \getID3();
                $fileInfo = $getID3->analyze($filePath);
                return isset($fileInfo['playtime_seconds']) ? (int) $fileInfo['playtime_seconds'] : null;
            }

            if ($this->commandExists('ffprobe')) {
                $command = sprintf(
                    'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s',
                    escapeshellarg($filePath)
                );
                $duration = shell_exec($command);
                return $duration ? (int) floatval($duration) : null;
            }

            return null;
        } catch (Exception $e) {
            \Log::error('Failed to get audio duration: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate video thumbnail
     */
    private function generateVideoThumbnail(string $videoPath, string $type = 'videos'): ?string
    {
        try {
            // Check if FFmpeg is available
            if (!$this->commandExists('ffmpeg')) {
                \Log::warning('FFmpeg not installed, skipping thumbnail generation');
                return null;
            }

            // Generate thumbnail filename
            $thumbnailFilename = 'thumb_' . time() . '_' . Str::random(10) . '.jpg';
            $thumbnailPath = $type . '/' . date('Y/m') . '/thumbnails/' . $thumbnailFilename;
            $fullThumbnailPath = storage_path('app/public/' . $thumbnailPath);

            // Create directory if not exists
            $thumbnailDir = dirname($fullThumbnailPath);
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            // Generate thumbnail at 3 seconds
            $command = sprintf(
                'ffmpeg -i %s -ss 00:00:03 -vframes 1 -vf "scale=320:-1" %s 2>&1',
                escapeshellarg($videoPath),
                escapeshellarg($fullThumbnailPath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($fullThumbnailPath)) {
                return $thumbnailPath;
            }

            return null;
        } catch (Exception $e) {
            \Log::error('Failed to generate video thumbnail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if command exists in system
     */
    private function commandExists(string $command): bool
    {
        $whereIsCommand = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where' : 'which';
        $process = proc_open(
            "$whereIsCommand $command",
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if ($process !== false) {
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            return !empty(trim($stdout));
        }

        return false;
    }

    /**
     * Validate file type
     */
    public function validateFileType(UploadedFile $file, array $allowedTypes): bool
    {
        $mimeType = $file->getMimeType();
        return in_array($mimeType, $allowedTypes);
    }

    /**
     * Validate file size
     */
    public function validateFileSize(UploadedFile $file, int $maxSizeInKb): bool
    {
        return $file->getSize() <= ($maxSizeInKb * 1024);
    }
}
