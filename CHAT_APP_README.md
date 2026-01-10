# 🌍 Multilingual Chat & Social Network Platform

A complete multilingual chat and social network platform built with **Laravel 11**, **Vue 3**, **Laravel Reverb (WebSocket)**, **Agora.io**, and **TailwindCSS**.

## ✨ Features

### 🎯 Core Features
- ✅ **Multi-method Authentication** (Email, Phone, Social OAuth)
- ✅ **Real-time Chat** (1-1, Group, Global Hall)
- ✅ **Voice & Video Calls** (1-1, Group, Random)
- ✅ **Live Streaming** (with PK Battle mode, Multi-guest)
- ✅ **Voice Chat Rooms** (Clubhouse-style)
- ✅ **Karaoke System** (1000+ songs, Solo/Duet/Battle)
- ✅ **Mini Games** (Ludo, Poker, Tai Xiu, Xì Dách, etc.)
- ✅ **Short Videos** (TikTok-style with effects)
- ✅ **Stories/Moments** (24h auto-delete)
- ✅ **Social Feed** (Posts, Comments, Reactions)
- ✅ **Dating/Matching** (Swipe cards, Video dating)
- ✅ **Guild System** (Clans, Guild Wars)
- ✅ **Gift System** (2D/3D animations, Lucky boxes)
- ✅ **VIP System** (7 tiers with exclusive features)
- ✅ **Ranking System** (Multiple leaderboards)
- ✅ **Mission & Events** (Daily/Weekly missions)
- ✅ **Payment Integration** (VNPAY, MoMo, ZaloPay)
- ✅ **Coin System** (Buy, Earn, Withdraw)
- ✅ **Admin Panel** (Moderation, Analytics, Reports)
- ✅ **Multi-language** (Vietnamese, English)

### 🔒 Security & Privacy
- End-to-end encryption option
- Content moderation (AI-powered)
- Block/Report system
- Privacy settings (Profile, Messages, Calls)
- 2FA Authentication
- Rate limiting

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 11
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis
- **WebSocket**: Laravel Reverb
- **Authentication**: Laravel Sanctum + JWT
- **File Storage**: AWS S3 / Local
- **Video Processing**: FFmpeg
- **Translation**: i18n (vue-i18n)

### Frontend
- **Framework**: Vue 3 (Composition API)
- **Build Tool**: Vite
- **UI Framework**: TailwindCSS + DaisyUI
- **State Management**: Pinia
- **Router**: Vue Router 4
- **WebSocket**: Laravel Echo + Pusher.js
- **Video/Audio**: Agora.io SDK + WebRTC

### Third-party Services
- **Agora.io**: Video/Audio streaming
- **VNPAY**: Payment gateway
- **MoMo**: Payment gateway
- **ZaloPay**: Payment gateway
- **Firebase**: Push notifications (optional)

## 📋 Requirements

- PHP >= 8.2
- Composer >= 2.x
- Node.js >= 18.x
- MySQL >= 8.0
- Redis >= 6.x
- FFmpeg (for video processing)

## 🚀 Installation

### 1. Clone the repository

\`\`\`bash
git clone <your-repo-url>
cd chatbox
\`\`\`

### 2. Install PHP dependencies

\`\`\`bash
composer install
\`\`\`

### 3. Install Node dependencies

\`\`\`bash
npm install
\`\`\`

### 4. Environment setup

\`\`\`bash
cp .env.example .env
php artisan key:generate
\`\`\`

### 5. Configure database

Edit `.env` file:

\`\`\`env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chatbox_db
DB_USERNAME=root
DB_PASSWORD=your_password
\`\`\`

### 6. Configure Redis

\`\`\`env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
\`\`\`

### 7. Configure Laravel Reverb (WebSocket)

\`\`\`bash
php artisan reverb:install
\`\`\`

Update `.env`:

\`\`\`env
BROADCAST_DRIVER=reverb

REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
\`\`\`

### 8. Configure Agora.io

Sign up at [https://www.agora.io](https://www.agora.io) and get your credentials:

\`\`\`env
AGORA_APP_ID=your_agora_app_id
AGORA_APP_CERTIFICATE=your_certificate
\`\`\`

### 9. Configure Payment Gateways

#### VNPAY
\`\`\`env
VNPAY_TMN_CODE=your_tmn_code
VNPAY_HASH_SECRET=your_hash_secret
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
\`\`\`

#### MoMo
\`\`\`env
MOMO_PARTNER_CODE=your_partner_code
MOMO_ACCESS_KEY=your_access_key
MOMO_SECRET_KEY=your_secret_key
MOMO_ENDPOINT=https://test-payment.momo.vn/v2/gateway/api/create
\`\`\`

#### ZaloPay
\`\`\`env
ZALOPAY_APP_ID=your_app_id
ZALOPAY_KEY1=your_key1
ZALOPAY_KEY2=your_key2
ZALOPAY_ENDPOINT=https://sb-openapi.zalopay.vn/v2/create
\`\`\`

### 10. Run migrations

\`\`\`bash
php artisan migrate
\`\`\`

### 11. Seed database (optional)

\`\`\`bash
php artisan db:seed
\`\`\`

### 12. Create storage link

\`\`\`bash
php artisan storage:link
\`\`\`

### 13. Build frontend assets

\`\`\`bash
npm run build
# or for development with hot reload
npm run dev
\`\`\`

## 🎮 Running the Application

### Start Laravel server

\`\`\`bash
php artisan serve
\`\`\`

### Start Laravel Reverb (WebSocket)

In a new terminal:

\`\`\`bash
php artisan reverb:start
\`\`\`

### Start Queue Worker

In a new terminal:

\`\`\`bash
php artisan queue:work
\`\`\`

### Start Vite Dev Server (for frontend development)

\`\`\`bash
npm run dev
\`\`\`

Access the application at: `http://localhost:8000`

## 📊 Database Schema

The application uses **45 database tables**:

### Core Tables
- `chat_users` - User accounts with level, VIP, balance
- `vip_packages` - VIP subscription packages
- `transactions` - All financial transactions

### Social Features
- `friendships` - Friend connections
- `follows` - Follow relationships
- `couples` - Couple relationships
- `user_blocks` - Blocked users

### Communication
- `conversations` - Chat conversations
- `conversation_members` - Conversation participants
- `messages` - Chat messages
- `message_reactions` - Message reactions
- `rooms` - Public/Private chat rooms
- `room_members` - Room participants

### Live Streaming
- `live_streams` - Live stream sessions
- `stream_viewers` - Stream viewer tracking

### Gifts
- `gifts` - Gift catalog
- `gift_transactions` - Gift sending history

### Social Content
- `posts` - User posts
- `post_reactions` - Post likes/reactions
- `comments` - Post comments
- `stories` - 24h stories
- `story_views` - Story view tracking
- `videos` - Short videos (TikTok-style)
- `video_reactions` - Video likes

### Entertainment
- `songs` - Karaoke song library
- `karaoke_sessions` - Karaoke recordings
- `games` - Game catalog
- `game_sessions` - Game play sessions

### Community
- `guilds` - Guild/Clan organizations
- `guild_members` - Guild membership

### Gamification
- `rankings` - Leaderboard rankings
- `missions` - Daily/Weekly missions
- `user_missions` - User mission progress
- `badges` - Achievement badges
- `user_badges` - User badge collection
- `events` - Special events

### Communication Logs
- `call_logs` - Voice/Video call history

### Dating
- `dating_profiles` - Dating profiles
- `swipes` - Swipe actions (like/pass)
- `matches` - Matched users

### System
- `chat_notifications` - In-app notifications
- `reports` - Content/User reports
- `user_favorites` - Favorited items
- `user_privacy_settings` - Privacy preferences
- `chat_settings` - App configuration

## 🎨 Frontend Structure

\`\`\`
resources/js/
├── components/
│   ├── common/        # Reusable components
│   ├── chat/          # Chat components
│   ├── live/          # Live streaming
│   ├── room/          # Voice rooms
│   ├── video/         # Short videos
│   ├── game/          # Mini games
│   └── gift/          # Gift animations
├── views/             # Page components
├── stores/            # Pinia stores
├── composables/       # Composition functions
├── services/          # API services
├── utils/             # Helper functions
├── locales/           # Translations
└── router/            # Vue Router
\`\`\`

## 🔧 Configuration

### VIP Pricing (in VND)

| Tier | Name | Price | Duration |
|------|------|-------|----------|
| VIP 1 | Bronze | 100,000đ | 30 days |
| VIP 2 | Silver | 200,000đ | 30 days |
| VIP 3 | Gold | 500,000đ | 30 days |
| VIP 4 | Platinum | 1,000,000đ | 30 days |
| VIP 5 | Diamond | 2,000,000đ | 30 days |
| VIP 6 | King | 5,000,000đ | 30 days |
| VIP 7 | Emperor | 10,000,000đ | 30 days |

### Coin System
- **Rate**: 100 coins = 10,000đ
- **Minimum Withdrawal**: 500,000đ
- **Streamer Commission**: 40%
- **Withdrawal Fee**: 5%

### Daily Rewards
- Login: +100 coins
- Send 50 messages: +50 coins
- Gift 3 items: +200 coins
- Join 1 room: +100 coins
- Watch 1 livestream: +150 coins
- Online 2 hours: +300 coins

## 🧪 Testing

\`\`\`bash
php artisan test
\`\`\`

## 📝 API Documentation

API documentation is available at `/api/documentation` (using Swagger/OpenAPI).

## 🤝 Contributing

Contributions are welcome! Please read our contributing guidelines.

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For support, email support@example.com or join our Discord server.

## 🙏 Acknowledgments

- Laravel Framework
- Vue.js
- Agora.io
- TailwindCSS
- DaisyUI
- All contributors

---

Made with ❤️ by Claude AI

## 📚 Additional Resources

### Development Commands

\`\`\`bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Database
php artisan migrate:fresh --seed

# Queue
php artisan queue:restart

# Optimize
php artisan optimize
\`\`\`

### Production Deployment

\`\`\`bash
# Build assets for production
npm run build

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force
\`\`\`

### Environment Variables Reference

See `.env.example` for all available configuration options including:
- Database configuration
- Redis configuration
- WebSocket (Reverb) configuration
- Agora.io configuration
- Payment gateway credentials
- Feature toggles
- Rate limiting
- Security settings
- File upload limits

## 🎯 Roadmap

- [ ] Mobile apps (iOS/Android) using React Native
- [ ] Desktop app using Electron
- [ ] AI chatbot integration
- [ ] Voice message transcription
- [ ] Real-time translation
- [ ] AR filters for video calls
- [ ] Blockchain integration for NFT gifts
- [ ] Premium sticker marketplace
