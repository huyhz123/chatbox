<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class File extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'details',
        'image',
        'file_path',
        'file_type',
        'file_size',
        'version',
        'price',
        'special_price',
        'download_limit',
        'is_encrypted',
        'preview_url',
        'order',
        'is_active',
        'is_featured',
        'sold_count',
        'download_count',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'special_price' => 'decimal:2',
            'file_size' => 'integer',
            'is_encrypted' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'price', 'is_active']);
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->morphMany(OrderItem::class, 'itemable');
    }

    public function downloads()
    {
        return $this->hasMany(FileDownload::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Helpers
    public function getCurrentPrice()
    {
        return $this->special_price ?? $this->price;
    }

    public function getFileSizeFormatted()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes > 1024) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function incrementSoldCount()
    {
        $this->increment('sold_count');
    }

    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }
}
