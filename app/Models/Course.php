<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Course extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'details',
        'image',
        'price',
        'special_price',
        'level',
        'duration',
        'instructor_name',
        'access_days',
        'certificate',
        'order',
        'is_active',
        'is_featured',
        'enrolled_count',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'special_price' => 'decimal:2',
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

    public function lessons()
    {
        return $this->hasMany(CourseLesson::class);
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function orderItems()
    {
        return $this->morphMany(OrderItem::class, 'itemable');
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

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    // Helpers
    public function getCurrentPrice()
    {
        return $this->special_price ?? $this->price;
    }

    public function incrementEnrolledCount()
    {
        $this->increment('enrolled_count');
    }

    public function getTotalLessonsCount()
    {
        return $this->lessons()->count();
    }
}
