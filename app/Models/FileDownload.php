<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'user_id',
        'ip_address',
        'download_count',
        'last_downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'last_downloaded_at' => 'datetime',
        ];
    }

    // Relationships
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helpers
    public function canDownload()
    {
        return $this->download_count < $this->file->download_limit;
    }

    public function incrementDownload()
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);
        $this->file->incrementDownloadCount();
    }
}
