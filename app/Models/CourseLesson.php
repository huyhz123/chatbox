<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'content',
        'video_url',
        'attachments',
        'duration',
        'order',
        'is_free',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'is_free' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function progress()
    {
        return $this->hasMany(CourseProgress::class, 'lesson_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
