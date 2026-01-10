<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\StoryView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class StoryController extends Controller
{
    /**
     * Get stories from followed users
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Get followed users IDs
            $followedIds = $user->following()->pluck('followed_id')->toArray();
            $followedIds[] = $user->id; // Include own stories

            // Get active stories (less than 24h old)
            $stories = Story::whereIn('user_id', $followedIds)
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->with(['user', 'views'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('user_id');

            // Format stories by user
            $formattedStories = $stories->map(function ($userStories, $userId) use ($user) {
                $storyUser = $userStories->first()->user;
                $hasUnviewed = $userStories->contains(function ($story) use ($user) {
                    return !$story->views->contains('user_id', $user->id);
                });

                return [
                    'user' => [
                        'id' => $storyUser->id,
                        'username' => $storyUser->username,
                        'avatar' => $storyUser->avatar,
                    ],
                    'stories' => $userStories->map(function ($story) use ($user) {
                        return [
                            'id' => $story->id,
                            'type' => $story->type,
                            'media_url' => $story->media_url,
                            'text_content' => $story->text_content,
                            'background_color' => $story->background_color,
                            'duration' => $story->duration,
                            'views_count' => $story->views_count,
                            'is_viewed' => $story->views->contains('user_id', $user->id),
                            'created_at' => $story->created_at,
                            'expires_at' => $story->created_at->addHours(24),
                        ];
                    }),
                    'has_unviewed' => $hasUnviewed,
                    'total_stories' => $userStories->count(),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'stories' => $formattedStories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific user's stories
     */
    public function userStories(Request $request, int $userId): JsonResponse
    {
        try {
            $currentUser = $request->user();

            $stories = Story::where('user_id', $userId)
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->with(['user', 'views'])
                ->orderBy('created_at', 'asc')
                ->get();

            if ($stories->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active stories',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'stories' => $stories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new story
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'type' => 'required|in:image,video,text',
                'media' => 'required_if:type,image,video|file|max:10240', // 10MB
                'text_content' => 'required_if:type,text|string|max:500',
                'background_color' => 'nullable|string|max:7', // hex color
                'duration' => 'nullable|integer|min:3|max:15', // seconds
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $mediaUrl = null;
            $duration = $request->duration ?? 5;

            // Upload media if provided
            if ($request->hasFile('media')) {
                // TODO: Upload to storage
                $mediaUrl = '/temp/story-media.jpg';

                // For videos, get duration
                if ($request->type === 'video') {
                    // TODO: Get actual video duration
                    $duration = 15;
                }
            }

            $story = Story::create([
                'user_id' => $user->id,
                'type' => $request->type,
                'media_url' => $mediaUrl,
                'text_content' => $request->text_content,
                'background_color' => $request->background_color ?? '#000000',
                'duration' => $duration,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Story created',
                'story' => $story,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create story',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View a story
     */
    public function view(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $story = Story::find($id);
            if (!$story) {
                return response()->json([
                    'success' => false,
                    'message' => 'Story not found',
                ], 404);
            }

            // Check if already viewed
            $view = StoryView::where('story_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$view) {
                StoryView::create([
                    'story_id' => $id,
                    'user_id' => $user->id,
                ]);
                $story->increment('views_count');
            }

            return response()->json([
                'success' => true,
                'views_count' => $story->fresh()->views_count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to view story',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get story viewers
     */
    public function viewers(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $story = Story::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$story) {
                return response()->json([
                    'success' => false,
                    'message' => 'Story not found or unauthorized',
                ], 404);
            }

            $viewers = StoryView::where('story_id', $id)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'viewers' => $viewers,
                'total' => $viewers->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch viewers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a story
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $story = Story::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$story) {
                return response()->json([
                    'success' => false,
                    'message' => 'Story not found',
                ], 404);
            }

            // TODO: Delete media from storage
            $story->delete();

            return response()->json([
                'success' => true,
                'message' => 'Story deleted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete story',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
