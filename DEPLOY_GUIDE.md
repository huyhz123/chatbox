# Hướng dẫn Deploy Electronics Shop

## 📋 Checklist trước khi Deploy

### Backend
- [ ] Đổi `JWT_SECRET` thành chuỗi phức tạp (32+ ký tự)
- [ ] Cấu hình MongoDB Atlas (không dùng localhost)
- [ ] Set `NODE_ENV=production`
- [ ] Cấu hình `FRONTEND_URL` cho CORS

### Frontend
- [ ] Cấu hình `VITE_API_URL` trỏ đến backend production
- [ ] Kiểm tra build thành công: `npm run build`

---

## 🚀 Deploy Backend lên Render

### Bước 1: Tạo MongoDB Atlas
1. Vào https://cloud.mongodb.com
2. Tạo cluster miễn phí (M0)
3. Tạo Database User
4. Whitelist IP: `0.0.0.0/0` (cho phép tất cả)
5. Copy connection string

### Bước 2: Deploy lên Render
1. Vào https://render.com
2. New → Web Service
3. Connect GitHub repo
4. Cấu hình:
   ```
   Name: electronics-shop-api
   Region: Singapore (gần VN)
   Branch: main
   Root Directory: backend
   Runtime: Node
   Build Command: npm install
   Start Command: npm start
   ```

5. Thêm Environment Variables:
   ```
   PORT=5000
   NODE_ENV=production
   MONGODB_URI=mongodb+srv://...
   JWT_SECRET=your_very_long_secret_key_at_least_32_chars
   FRONTEND_URL=https://your-frontend.vercel.app
   ```

6. Click "Create Web Service"

### Lấy URL Backend
Sau khi deploy xong, copy URL dạng:
`https://electronics-shop-api.onrender.com`

---

## 🌐 Deploy Frontend lên Vercel

### Bước 1: Chuẩn bị
1. Vào https://vercel.com
2. Import GitHub repo

### Bước 2: Cấu hình
```
Framework Preset: Vite
Root Directory: frontend
Build Command: npm run build
Output Directory: dist
```

### Bước 3: Environment Variables
```
VITE_API_URL=https://electronics-shop-api.onrender.com/api
```

### Bước 4: Deploy
Click "Deploy" và chờ hoàn thành.

---

## ⚡ Deploy nhanh với Railway

Railway cho phép deploy cả MongoDB + Backend cùng lúc.

1. Vào https://railway.app
2. New Project → Deploy from GitHub
3. Chọn backend folder
4. Add Service → Database → MongoDB
5. Railway tự động tạo `MONGODB_URL`
6. Thêm các biến còn lại:
   ```
   PORT=5000
   NODE_ENV=production
   JWT_SECRET=your_secret
   FRONTEND_URL=https://your-frontend.vercel.app
   ```

---

## 🔧 Cấu hình sau Deploy

### 1. Seed dữ liệu Production
SSH vào server hoặc dùng Railway CLI:
```bash
npm run seed
```

### 2. Kiểm tra Health
```bash
curl https://your-backend.onrender.com/api/health
```

### 3. Test Login
```bash
curl -X POST https://your-backend.onrender.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@shop.com","password":"admin123"}'
```

---

## 🐛 Troubleshooting

### CORS Error
- Kiểm tra `FRONTEND_URL` trong backend có đúng URL frontend không
- URL phải KHÔNG có dấu `/` cuối

### 401 Unauthorized
- Token hết hạn, đăng nhập lại
- Kiểm tra `JWT_SECRET` production khác dev

### MongoDB Connection Failed
- Kiểm tra connection string
- Kiểm tra IP whitelist
- Kiểm tra username/password

### Build Failed (Frontend)
```bash
# Local test
cd frontend
npm run build
```

### Render Free Tier Sleep
- Service sẽ ngủ sau 15 phút không hoạt động
- Request đầu tiên sẽ mất ~30s để wake up
- Upgrade để tránh sleep

---

## 📊 Monitoring

### Render
- Dashboard → Logs
- Dashboard → Metrics

### Vercel
- Dashboard → Deployments → Functions

### MongoDB Atlas
- Clusters → Metrics
- Database → Browse Collections

---

## 💰 Chi phí ước tính

### Free Tier (Demo/MVP)
- Render: $0 (750h/tháng)
- Vercel: $0 (100GB bandwidth)
- MongoDB Atlas: $0 (512MB)

### Production nhỏ (~$20/tháng)
- Render Starter: $7/tháng
- Vercel Pro: $0-20/tháng
- MongoDB M10: $9/tháng

---

## 🔐 Security Checklist Production

- [ ] HTTPS enabled (tự động trên Render/Vercel)
- [ ] JWT Secret mạnh (32+ ký tự random)
- [ ] MongoDB password mạnh
- [ ] Rate limiting (có thể thêm sau)
- [ ] Không commit file .env
- [ ] Đổi password admin mặc định sau deploy
