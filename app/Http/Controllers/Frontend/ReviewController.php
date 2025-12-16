<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use App\Models\Service;
use App\Models\File;
use App\Models\Course;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Store a new review
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:product,service,file,course',
            'id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        $user = $request->user();

        // Get the reviewable model
        $reviewable = match($validated['type']) {
            'product' => Product::findOrFail($validated['id']),
            'service' => Service::findOrFail($validated['id']),
            'file' => File::findOrFail($validated['id']),
            'course' => Course::findOrFail($validated['id']),
        };

        // Check if user already reviewed this item
        $existingReview = Review::where('user_id', $user->id)
            ->where('reviewable_type', get_class($reviewable))
            ->where('reviewable_id', $reviewable->id)
            ->first();

        if ($existingReview) {
            return back()->withErrors(['review' => 'You have already reviewed this item.']);
        }

        // TODO: Check if user has purchased this item (is_verified = true)
        $isVerified = false;

        Review::create([
            'user_id' => $user->id,
            'reviewable_type' => get_class($reviewable),
            'reviewable_id' => $reviewable->id,
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'comment' => $validated['comment'],
            'is_verified' => $isVerified,
            'is_approved' => true, // Auto-approve, can change to false for moderation
        ]);

        return back()->with('success', 'Thank you for your review!');
    }

    /**
     * Update review
     */
    public function update(Request $request, Review $review)
    {
        // Ensure user owns this review
        if ($review->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        $review->update($validated);

        return back()->with('success', 'Review updated successfully!');
    }

    /**
     * Delete review
     */
    public function destroy(Request $request, Review $review)
    {
        // Ensure user owns this review
        if ($review->user_id !== $request->user()->id) {
            abort(403);
        }

        $review->delete();

        return back()->with('success', 'Review deleted successfully!');
    }

    /**
     * Mark review as helpful
     */
    public function helpful(Review $review)
    {
        $review->increment('helpful_count');

        return back()->with('success', 'Thank you for your feedback!');
    }
}
