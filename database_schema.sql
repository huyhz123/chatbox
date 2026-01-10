-- ==========================================
-- MULTILINGUAL CHAT & SOCIAL NETWORK
-- Database Schema - MySQL 8.0
-- Total Tables: 45
-- ==========================================

-- NOTE: This is a reference schema. Run Laravel migrations instead:
-- php artisan migrate

-- Core User System
CREATE DATABASE IF NOT EXISTS chatbox_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE chatbox_db;

-- Overview of all tables:
-- 1. chat_users - Main user accounts (level, VIP, balance, online status)
-- 2. vip_packages - VIP subscription tiers (Bronze to Emperor)
-- 3. transactions - Financial transactions (deposit, withdrawal, gifts)
-- 4. friendships - Friend relationships (pending, accepted, blocked)
-- 5. follows - Follow system (no approval needed)
-- 6. conversations - Chat conversations (private, group, room)
-- 7. conversation_members - Conversation participants with roles
-- 8. messages - Chat messages (text, media, stickers, gifts)
-- 9. message_reactions - Emoji reactions on messages
-- 10. rooms - Public/Private chat rooms (voice rooms)
-- 11. room_members - Room participants with seat assignments
-- 12. live_streams - Live streaming sessions
-- 13. stream_viewers - Live stream viewer tracking
-- 14. gifts - Gift catalog (free, basic, special, VIP, lucky)
-- 15. gift_transactions - Gift sending history
-- 16. posts - User posts/feed
-- 17. post_reactions - Post reactions (like, love, haha, wow, sad, angry)
-- 18. comments - Post comments (with nested replies)
-- 19. stories - 24-hour stories (image, video, text)
-- 20. story_views - Story view tracking
-- 21. videos - Short videos (TikTok-style)
-- 22. video_reactions - Video likes/reactions
-- 23. songs - Karaoke song library
-- 24. karaoke_sessions - Karaoke recordings
-- 25. games - Mini game catalog
-- 26. game_sessions - Game play sessions
-- 27. guilds - Guild/Clan system
-- 28. guild_members - Guild membership
-- 29. rankings - Leaderboard rankings (daily, weekly, monthly, all-time)
-- 30. missions - Daily/Weekly mission templates
-- 31. user_missions - User mission progress
-- 32. chat_notifications - In-app notifications
-- 33. reports - Content/User reports
-- 34. couples - Couple relationships
-- 35. user_blocks - Blocked users
-- 36. user_privacy_settings - Privacy preferences
-- 37. chat_settings - App configuration
-- 38. call_logs - Voice/Video call history
-- 39. badges - Achievement badges
-- 40. user_badges - User badge collection
-- 41. events - Special events
-- 42. user_favorites - Favorited content (songs, videos, posts)
-- 43. dating_profiles - Dating profiles
-- 44. swipes - Dating swipe actions
-- 45. matches - Matched users

-- Key Features:
-- - Indexes on all foreign keys and frequently queried columns
-- - JSON columns for flexible data storage
-- - Proper cascading deletes
-- - Enum types for status/type fields
-- - Decimal types for financial data
-- - Timestamp tracking on all tables

-- To see the actual migrations, check:
-- database/migrations/2024_12_01_000001_create_chat_users_table.php
-- ... and 44 more migration files

-- Quick Stats:
-- Total Indexes: 100+
-- Foreign Keys: 80+
-- JSON Fields: 30+
-- Enum Fields: 40+

-- Run migrations with:
-- php artisan migrate

-- Seed sample data with:
-- php artisan db:seed

-- Rollback migrations with:
-- php artisan migrate:rollback

-- Fresh migration with seeding:
-- php artisan migrate:fresh --seed
