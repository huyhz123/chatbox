<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Get feed (posts from friends + following + public)
     */
    public function feed(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Get friend IDs and following IDs
            $friendIds = $user->friends()->pluck('friend_id')->toArray();
            $followingIds = $user->following()->pluck('following_id')->toArray();
            $userIds = array_unique(array_merge([$user->id], $friendIds, $followingIds));

            $posts = Post::with(['user', 'reactions', 'comments.user'])
                ->where(function($query) use ($userIds) {
                    $query->whereIn('user_id', $userIds)
                          ->orWhere('privacy', 'public');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $posts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch feed',
            ], 500);
        }
    }

    /**
     * Get user's posts
     */
    public function userPosts(Request $request, $userId): JsonResponse
    {
        try {
            $currentUser = $request->user();

            $query = Post::with(['user', 'reactions', 'comments'])
                ->where('user_id', $userId);

            // Privacy check
            if (!$currentUser || $currentUser->id != $userId) {
                $query->where('privacy', 'public');
            }

            $posts = $query->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $posts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch posts',
            ], 500);
        }
    }

    /**
     * Create new post
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'media' => 'sometimes|array',
            'media.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,webm|max:51200', // 50MB
            'privacy' => 'sometimes|in:public,friends,private',
            'location' => 'sometimes|string|max:100',
            'feeling' => 'sometimes|string|max:50',
        ]);

        try {
            $user = $request->user();
            $mediaUrls = [];

            // Handle media uploads
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $path = $file->store('posts/' . date('Y/m'), 'public');
                    $mediaUrls[] = [
                        'type' => str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'video',
                        'url' => Storage::url($path),
                    ];
                }
            }

            $post = Post::create([
                'user_id' => $user->id,
                'content' => $validated['content'],
                'media' => $mediaUrls,
                'privacy' => $validated['privacy'] ?? 'public',
                'location' => $validated['location'] ?? null,
                'feeling' => $validated['feeling'] ?? null,
            ]);

            // Add EXP for creating post
            $user->addExp(20);

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => ['post' => $post->load('user')],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update post
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'sometimes|string|max:5000',
            'privacy' => 'sometimes|in:public,friends,private',
        ]);

        try {
            $post = Post::where('user_id', $request->user()->id)
                ->findOrFail($id);

            $post->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'data' => ['post' => $post],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update post',
            ], 404);
        }
    }

    /**
     * Delete post
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $post = Post::where('user_id', $request->user()->id)
                ->findOrFail($id);

            // Delete media files
            if ($post->media) {
                foreach ($post->media as $media) {
                    $path = str_replace('/storage/', '', parse_url($media['url'], PHP_URL_PATH));
                    Storage::disk('public')->delete($path);
                }
            }

            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete post',
            ], 404);
        }
    }

    /**
     * React to post
     */
    public function react(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:like,love,haha,wow,sad,angry',
        ]);

        try {
            $post = Post::findOrFail($id);
            $user = $request->user();

            $reaction = PostReaction::updateOrCreate(
                [
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                ],
                [
                    'type' => $validated['type'],
                ]
            );

            // Update post likes count
            $post->likes_count = $post->reactions()->count();
            $post->save();

            return response()->json([
                'success' => true,
                'message' => 'Reaction added',
                'data' => ['reaction' => $reaction],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to react to post',
            ], 500);
        }
    }

    /**
     * Remove reaction
     */
    public function unreact(Request $request, $id): JsonResponse
    {
        try {
            PostReaction::where('post_id', $id)
                ->where('user_id', $request->user()->id)
                ->delete();

            $post = Post::find($id);
            if ($post) {
                $post->likes_count = $post->reactions()->count();
                $post->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Reaction removed',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove reaction',
            ], 500);
        }
    }

    /**
     * Comment on post
     */
    public function comment(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'sometimes|exists:comments,id',
        ]);

        try {
            $post = Post::findOrFail($id);

            $comment = Comment::create([
                'post_id' => $post->id,
                'user_id' => $request->user()->id,
                'parent_id' => $validated['parent_id'] ?? null,
                'content' => $validated['content'],
            ]);

            // Update post comments count
            $post->increment('comments_count');

            // Add EXP
            $request->user()->addExp(5);

            return response()->json([
                'success' => true,
                'message' => 'Comment added',
                'data' => ['comment' => $comment->load('user')],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to comment',
            ], 500);
        }
    }

    /**
     * Get post comments
     */
    public function comments(Request $request, $id): JsonResponse
    {
        try {
            $comments = Comment::with(['user', 'replies.user'])
                ->where('post_id', $id)
                ->whereNull('parent_id')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $comments,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch comments',
            ], 500);
        }
    }
}
