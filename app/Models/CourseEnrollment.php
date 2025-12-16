<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'user_id',
        'order_id',
        'enrolled_at',
        'expires_at',
        'progress',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
            'progress' => 'integer',
        ];
    }

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function lessonProgress()
    {
        return $this->hasMany(CourseProgress::class, 'enrollment_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    // Helpers
    public function isActive()
    {
        return !$this->expires_at || $this->expires_at > now();
    }

    public function isCompleted()
    {
        return !is_null($this->completed_at);
    }

    public function updateProgress()
    {
        $totalLessons = $this->course->lessons()->count();
        $completedLessons = $this->lessonProgress()->where('is_completed', true)->count();

        $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

        $this->update(['progress' => $progress]);

        if ($progress >= 100 && !$this->completed_at) {
            $this->update(['completed_at' => now()]);
        }
    }
}
