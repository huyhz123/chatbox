<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomUser;
use App\Models\ChatUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    /**
     * Get list of rooms
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'category' => 'nullable|in:music,gaming,chat,party,study,other',
                'sort' => 'nullable|in:users,created_at',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $query = Room::with(['owner', 'users'])
                ->where('status', 'active');

            // Filter by category
            if ($request->category) {
                $query->where('category', $request->category);
            }

            // Sort
            $sortBy = $request->sort ?? 'users';
            if ($sortBy === 'users') {
                $query->withCount('users')->orderBy('users_count', 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $rooms = $query->paginate(20);

            // Format rooms with seat information
            $formattedRooms = $rooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'description' => $room->description,
                    'icon' => $room->icon,
                    'category' => $room->category,
                    'owner' => [
                        'id' => $room->owner->id,
                        'username' => $room->owner->username,
                        'avatar' => $room->owner->avatar,
                    ],
                    'current_users' => $room->users->count(),
                    'max_users' => $room->max_users,
                    'is_private' => $room->is_private,
                    'seats' => $this->formatSeats($room),
                    'tags' => json_decode($room->tags, true),
                ];
            });

            return response()->json([
                'success' => true,
                'rooms' => $formattedRooms,
                'pagination' => [
                    'current_page' => $rooms->currentPage(),
                    'total' => $rooms->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rooms',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific room
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $room = Room::with(['owner', 'users.user'])->find($id);

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'room' => [
                    'id' => $room->id,
                    'name' => $room->name,
                    'description' => $room->description,
                    'icon' => $room->icon,
                    'category' => $room->category,
                    'owner' => $room->owner,
                    'current_users' => $room->users->count(),
                    'max_users' => $room->max_users,
                    'is_private' => $room->is_private,
                    'seats' => $this->formatSeats($room),
                    'agora' => [
                        'channel' => $room->agora_channel,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch room',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new room
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'description' => 'nullable|string|max:500',
                'icon' => 'nullable|string|max:10',
                'category' => 'required|in:music,gaming,chat,party,study,other',
                'max_users' => 'required|integer|min:2|max:20',
                'is_private' => 'boolean',
                'password' => 'nullable|required_if:is_private,true|string|min:4',
                'tags' => 'nullable|array|max:5',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Check if user already owns an active room
            $existingRoom = Room::where('owner_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($existingRoom) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active room',
                ], 400);
            }

            // Generate Agora channel
            $channelName = 'room_' . $user->id . '_' . time();

            $room = Room::create([
                'owner_id' => $user->id,
                'name' => $request->name,
                'description' => $request->description,
                'icon' => $request->icon ?? '🎤',
                'category' => $request->category,
                'max_users' => $request->max_users,
                'is_private' => $request->is_private ?? false,
                'password' => $request->is_private ? bcrypt($request->password) : null,
                'tags' => json_encode($request->tags ?? []),
                'agora_channel' => $channelName,
                'status' => 'active',
            ]);

            // Owner joins automatically
            RoomUser::create([
                'room_id' => $room->id,
                'user_id' => $user->id,
                'seat_number' => 1,
                'is_muted' => false,
                'role' => 'owner',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Room created successfully',
                'room' => $room,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create room',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Join a room
     */
    public function join(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $room = Room::with('users')->find($id);

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room not found',
                ], 404);
            }

            if ($room->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Room is not active',
                ], 400);
            }

            // Check if room is full
            if ($room->users->count() >= $room->max_users) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room is full',
                ], 400);
            }

            // Check password for private rooms
            if ($room->is_private) {
                $validator = Validator::make($request->all(), [
                    'password' => 'required|string',
                ]);

                if ($validator->fails() || !password_verify($request->password, $room->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid password',
                    ], 401);
                }
            }

            // Check if user already in room
            $existingUser = RoomUser::where('room_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already in this room',
                ], 400);
            }

            // Find available seat
            $occupiedSeats = $room->users->pluck('seat_number')->toArray();
            $availableSeat = null;
            for ($i = 1; $i <= $room->max_users; $i++) {
                if (!in_array($i, $occupiedSeats)) {
                    $availableSeat = $i;
                    break;
                }
            }

            // Join room
            RoomUser::create([
                'room_id' => $id,
                'user_id' => $user->id,
                'seat_number' => $availableSeat,
                'is_muted' => true, // Muted by default
                'role' => 'member',
            ]);

            // TODO: Generate Agora token and return
            // $agoraToken = $this->generateAgoraToken($room->agora_channel, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Joined room successfully',
                'room' => $room->fresh('users'),
                'seat_number' => $availableSeat,
                'agora' => [
                    'app_id' => env('AGORA_APP_ID'),
                    'channel' => $room->agora_channel,
                    'token' => 'temp_token',
                    'uid' => $user->id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to join room',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Leave a room
     */
    public function leave(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $roomUser = RoomUser::where('room_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$roomUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not in this room',
                ], 404);
            }

            $room = Room::find($id);

            // If owner leaves, close the room
            if ($roomUser->role === 'owner') {
                $room->update(['status' => 'closed']);
                RoomUser::where('room_id', $id)->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Room closed',
                ]);
            }

            // Regular user leaves
            $roomUser->delete();

            return response()->json([
                'success' => true,
                'message' => 'Left room successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to leave room',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle mute status
     */
    public function toggleMute(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $roomUser = RoomUser::where('room_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$roomUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not in this room',
                ], 404);
            }

            $roomUser->update([
                'is_muted' => !$roomUser->is_muted,
            ]);

            return response()->json([
                'success' => true,
                'is_muted' => $roomUser->is_muted,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle mute',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kick user from room (owner only)
     */
    public function kickUser(Request $request, int $roomId, int $userId): JsonResponse
    {
        try {
            $currentUser = $request->user();

            $room = Room::find($roomId);
            if (!$room || $room->owner_id !== $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $roomUser = RoomUser::where('room_id', $roomId)
                ->where('user_id', $userId)
                ->first();

            if (!$roomUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not in room',
                ], 404);
            }

            $roomUser->delete();

            return response()->json([
                'success' => true,
                'message' => 'User kicked from room',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to kick user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format seats for room display
     */
    private function formatSeats(Room $room): array
    {
        $seats = array_fill(0, $room->max_users, null);

        foreach ($room->users as $roomUser) {
            if ($roomUser->seat_number <= $room->max_users) {
                $seats[$roomUser->seat_number - 1] = [
                    'user_id' => $roomUser->user->id,
                    'username' => $roomUser->user->username,
                    'avatar' => $roomUser->user->avatar,
                    'is_muted' => $roomUser->is_muted,
                    'role' => $roomUser->role,
                ];
            }
        }

        return $seats;
    }
}
