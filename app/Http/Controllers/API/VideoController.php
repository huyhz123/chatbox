<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoLike;
use App\Models\VideoComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    /**
     * Get video feed (TikTok-style)
     */
    public function feed(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $videos = Video::with(['user', 'likes', 'comments'])
                ->where('status', 'published')
                ->withCount(['likes', 'comments', 'shares'])
                ->inRandomOrder() // For discovery
                ->paginate(10);

            // Mark if user liked each video
            $formattedVideos = $videos->map(function ($video) use ($user) {
                return [
                    'id' => $video->id,
                    'user' => [
                        'id' => $video->user->id,
                        'username' => $video->user->username,
                        'avatar' => $video->user->avatar,
                        'is_verified' => $video->user->is_verified,
                    ],
                    'video_url' => $video->video_url,
                    'thumbnail' => $video->thumbnail,
                    'description' => $video->description,
                    'music' => $video->music,
                    'duration' => $video->duration,
                    'width' => $video->width,
                    'height' => $video->height,
                    'likes_count' => $video->likes_count,
                    'comments_count' => $video->comments_count,
                    'shares_count' => $video->shares_count,
                    'views_count' => $video->views_count,
                    'is_liked' => $user ? $video->likes->contains('user_id', $user->id) : false,
                    'created_at' => $video->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'videos' => $formattedVideos,
                'pagination' => [
                    'current_page' => $videos->currentPage(),
                    'has_more' => $videos->hasMorePages(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch feed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's videos
     */
    public function userVideos(Request $request, int $userId): JsonResponse
    {
        try {
            $videos = Video::where('user_id', $userId)
                ->where('status', 'published')
                ->withCount(['likes', 'comments'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'videos' => $videos->items(),
                'pagination' => [
                    'current_page' => $videos->currentPage(),
                    'total' => $videos->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch videos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload a new video
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'video' => 'required|file|mimes:mp4,mov,avi|max:102400', // 100MB
                'thumbnail' => 'nullable|image|max:5120',
                'description' => 'nullable|string|max:500',
                'music' => 'nullable|string|max:200',
                'is_private' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // TODO: Upload video to storage (S3 or local)
            // $videoPath = $request->file('video')->store('videos', 's3');
            $videoPath = '/temp/video.mp4';

            // TODO: Generate thumbnail if not provided
            $thumbnailPath = $request->hasFile('thumbnail')
                ? '/temp/thumbnail.jpg'
                : '/temp/auto-thumbnail.jpg';

            // TODO: Get video metadata (duration, dimensions)
            $duration = 30; // seconds
            $width = 1080;
            $height = 1920;

            $video = Video::create([
                'user_id' => $user->id,
                'video_url' => $videoPath,
                'thumbnail' => $thumbnailPath,
                'description' => $request->description,
                'music' => $request->music,
                'duration' => $duration,
                'width' => $width,
                'height' => $height,
                'status' => $request->is_private ? 'private' : 'published',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Video uploaded successfully',
                'video' => $video,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload video',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Like/unlike a video
     */
    public function toggleLike(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $video = Video::find($id);
            if (!$video) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video not found',
                ], 404);
            }

            $like = VideoLike::where('video_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if ($like) {
                // Unlike
                $like->delete();
                $video->decrement('likes_count');

                return response()->json([
                    'success' => true,
                    'is_liked' => false,
                    'likes_count' => $video->fresh()->likes_count,
                ]);
            } else {
                // Like
                VideoLike::create([
                    'video_id' => $id,
                    'user_id' => $user->id,
                ]);
                $video->increment('likes_count');

                return response()->json([
                    'success' => true,
                    'is_liked' => true,
                    'likes_count' => $video->fresh()->likes_count,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle like',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add comment to video
     */
    public function addComment(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'content' => 'required|string|max:500',
                'parent_id' => 'nullable|exists:video_comments,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $video = Video::find($id);
            if (!$video) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video not found',
                ], 404);
            }

            $comment = VideoComment::create([
                'video_id' => $id,
                'user_id' => $user->id,
                'parent_id' => $request->parent_id,
                'content' => $request->content,
            ]);

            $video->increment('comments_count');

            return response()->json([
                'success' => true,
                'comment' => $comment->load('user'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add comment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get video comments
     */
    public function getComments(Request $request, int $id): JsonResponse
    {
        try {
            $comments = VideoComment::where('video_id', $id)
                ->whereNull('parent_id')
                ->with(['user', 'replies.user'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'comments' => $comments->items(),
                'pagination' => [
                    'current_page' => $comments->currentPage(),
                    'total' => $comments->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch comments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Increment view count
     */
    public function incrementView(Request $request, int $id): JsonResponse
    {
        try {
            $video = Video::find($id);
            if (!$video) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video not found',
                ], 404);
            }

            $video->increment('views_count');

            return response()->json([
                'success' => true,
                'views_count' => $video->views_count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to increment view',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete video
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $video = Video::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$video) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video not found or unauthorized',
                ], 404);
            }

            // TODO: Delete file from storage
            $video->delete();

            return response()->json([
                'success' => true,
                'message' => 'Video deleted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete video',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
