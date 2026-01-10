<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Guild;
use App\Models\GuildMember;
use App\Models\ChatUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GuildController extends Controller
{
    /**
     * Get list of guilds
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'search' => 'nullable|string',
                'min_level' => 'nullable|integer|min:1',
                'sort' => 'nullable|in:members,level,created_at',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $query = Guild::with(['leader', 'members']);

            // Search by name or tag
            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'LIKE', '%' . $request->search . '%')
                      ->orWhere('tag', 'LIKE', '%' . $request->search . '%');
                });
            }

            // Filter by minimum level
            if ($request->min_level) {
                $query->where('level', '>=', $request->min_level);
            }

            // Sort
            $sortBy = $request->sort ?? 'members';
            if ($sortBy === 'members') {
                $query->withCount('members')->orderBy('members_count', 'desc');
            } elseif ($sortBy === 'level') {
                $query->orderBy('level', 'desc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $guilds = $query->paginate(20);

            return response()->json([
                'success' => true,
                'guilds' => $guilds->items(),
                'pagination' => [
                    'current_page' => $guilds->currentPage(),
                    'total' => $guilds->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch guilds',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific guild
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $guild = Guild::with(['leader', 'members.user'])->find($id);

            if (!$guild) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guild not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'guild' => $guild,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch guild',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new guild
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:50|unique:guilds,name',
                'tag' => 'required|string|max:6|unique:guilds,tag',
                'description' => 'nullable|string|max:500',
                'emblem' => 'nullable|url',
                'min_level' => 'nullable|integer|min:1|max:99',
                'is_public' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Check if user is already in a guild
            $existingMembership = GuildMember::where('user_id', $user->id)->first();
            if ($existingMembership) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already in a guild. Leave your current guild first.',
                ], 400);
            }

            // Check user level
            if ($user->level < 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be at least level 10 to create a guild',
                ], 400);
            }

            // Guild creation cost
            $creationCost = 10000;
            if ($user->balance < $creationCost) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance. Guild creation costs 10,000 coins',
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Deduct creation cost
                $user->deductCoins($creationCost, "Created guild: {$request->name}");

                // Create guild
                $guild = Guild::create([
                    'name' => $request->name,
                    'tag' => strtoupper($request->tag),
                    'description' => $request->description,
                    'emblem' => $request->emblem,
                    'leader_id' => $user->id,
                    'min_level' => $request->min_level ?? 1,
                    'is_public' => $request->is_public ?? true,
                    'level' => 1,
                    'exp' => 0,
                ]);

                // Add creator as leader
                GuildMember::create([
                    'guild_id' => $guild->id,
                    'user_id' => $user->id,
                    'role' => 'leader',
                    'contribution' => 0,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Guild created successfully',
                    'guild' => $guild->load('leader'),
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create guild',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Join a guild
     */
    public function join(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $guild = Guild::find($id);
            if (!$guild) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guild not found',
                ], 404);
            }

            // Check if already in a guild
            $existingMembership = GuildMember::where('user_id', $user->id)->first();
            if ($existingMembership) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already in a guild',
                ], 400);
            }

            // Check level requirement
            if ($user->level < $guild->min_level) {
                return response()->json([
                    'success' => false,
                    'message' => "You must be at least level {$guild->min_level} to join",
                ], 400);
            }

            // Check if guild is full
            $memberCount = GuildMember::where('guild_id', $id)->count();
            $maxMembers = 50; // Default max members

            if ($memberCount >= $maxMembers) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guild is full',
                ], 400);
            }

            // Join guild
            GuildMember::create([
                'guild_id' => $id,
                'user_id' => $user->id,
                'role' => 'member',
                'contribution' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Joined guild successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to join guild',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Leave guild
     */
    public function leave(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $membership = GuildMember::where('guild_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$membership) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not in this guild',
                ], 404);
            }

            // Leaders cannot leave (must transfer or disband)
            if ($membership->role === 'leader') {
                return response()->json([
                    'success' => false,
                    'message' => 'Guild leaders cannot leave. Transfer leadership or disband the guild.',
                ], 400);
            }

            $membership->delete();

            return response()->json([
                'success' => true,
                'message' => 'Left guild successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to leave guild',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kick member from guild
     */
    public function kickMember(Request $request, int $guildId, int $userId): JsonResponse
    {
        try {
            $currentUser = $request->user();

            // Check if current user has permission
            $currentMembership = GuildMember::where('guild_id', $guildId)
                ->where('user_id', $currentUser->id)
                ->first();

            if (!$currentMembership || !in_array($currentMembership->role, ['leader', 'officer'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to kick members',
                ], 403);
            }

            // Find target member
            $targetMembership = GuildMember::where('guild_id', $guildId)
                ->where('user_id', $userId)
                ->first();

            if (!$targetMembership) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not in this guild',
                ], 404);
            }

            // Cannot kick leader or higher rank
            if ($targetMembership->role === 'leader') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot kick guild leader',
                ], 400);
            }

            if ($currentMembership->role === 'officer' && $targetMembership->role === 'officer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Officers cannot kick other officers',
                ], 400);
            }

            $targetMembership->delete();

            return response()->json([
                'success' => true,
                'message' => 'Member kicked successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to kick member',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Promote member
     */
    public function promoteMember(Request $request, int $guildId, int $userId): JsonResponse
    {
        try {
            $currentUser = $request->user();

            // Only leader can promote
            $currentMembership = GuildMember::where('guild_id', $guildId)
                ->where('user_id', $currentUser->id)
                ->where('role', 'leader')
                ->first();

            if (!$currentMembership) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only guild leader can promote members',
                ], 403);
            }

            $targetMembership = GuildMember::where('guild_id', $guildId)
                ->where('user_id', $userId)
                ->first();

            if (!$targetMembership) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found in guild',
                ], 404);
            }

            // Promote: member -> officer
            if ($targetMembership->role === 'member') {
                $targetMembership->update(['role' => 'officer']);
                return response()->json([
                    'success' => true,
                    'message' => 'Promoted to Officer',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'User is already at max rank',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to promote member',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Donate to guild
     */
    public function donate(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:100|max:100000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $membership = GuildMember::where('guild_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$membership) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not in this guild',
                ], 404);
            }

            if ($user->balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance',
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Deduct from user
                $user->deductCoins($request->amount, "Donated to guild");

                // Add to guild contribution
                $membership->increment('contribution', $request->amount);

                // Add guild exp
                $guild = Guild::find($id);
                $guild->increment('exp', $request->amount / 10);

                // Check for level up
                $expNeeded = $guild->level * 10000;
                if ($guild->exp >= $expNeeded) {
                    $guild->increment('level');
                    $guild->decrement('exp', $expNeeded);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Donation successful',
                    'contribution' => $membership->fresh()->contribution,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to donate',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get guild members
     */
    public function members(Request $request, int $id): JsonResponse
    {
        try {
            $members = GuildMember::where('guild_id', $id)
                ->with('user')
                ->orderBy('contribution', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'members' => $members,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch members',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
