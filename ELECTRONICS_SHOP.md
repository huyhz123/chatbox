# TechShop - Electronics E-commerce

Web shop đồ điện tử phong cách Thế Giới Di Động / CellphoneS.

## Công nghệ

### Frontend
- React 18 + Vite
- Tailwind CSS
- React Router v6
- Context API (Auth + Cart)
- Axios

### Backend
- Node.js + Express
- MongoDB + Mongoose
- JWT Authentication
- bcrypt

## Cài đặt & Chạy

### Yêu cầu
- Node.js >= 18
- MongoDB (local hoặc Atlas)

### 1. Backend

```bash
cd backend
npm install
npm run dev
```

Server chạy tại: http://localhost:5000

### 2. Frontend

```bash
cd frontend
npm install
npm run dev
```

App chạy tại: http://localhost:3000

## Tài khoản mặc định

- **Admin**: admin@shop.com / admin123

## Cấu trúc

```
├── backend/
│   ├── src/
│   │   ├── config/db.js
│   │   ├── models/
│   │   │   ├── User.js
│   │   │   ├── Product.js
│   │   │   └── Order.js
│   │   ├── controllers/
│   │   ├── routes/
│   │   ├── middleware/
│   │   └── server.js
│   ├── .env
│   └── package.json
│
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   │   ├── Navbar.jsx
│   │   │   ├── Banner.jsx
│   │   │   ├── ProductCard.jsx
│   │   │   ├── Footer.jsx
│   │   │   └── ProtectedRoute.jsx
│   │   ├── pages/
│   │   │   ├── Home.jsx
│   │   │   ├── ProductDetail.jsx
│   │   │   ├── Cart.jsx
│   │   │   ├── Login.jsx
│   │   │   ├── Register.jsx
│   │   │   ├── Orders.jsx
│   │   │   └── admin/
│   │   │       ├── Dashboard.jsx
│   │   │       ├── Products.jsx
│   │   │       └── Orders.jsx
│   │   ├── context/
│   │   │   ├── AuthContext.jsx
│   │   │   └── CartContext.jsx
│   │   ├── services/api.js
│   │   ├── App.jsx
│   │   └── main.jsx
│   └── package.json
```

## API Endpoints

### Auth
- POST /api/auth/register
- POST /api/auth/login
- GET /api/auth/me

### Products
- GET /api/products
- GET /api/products/:id
- POST /api/products (admin)
- PUT /api/products/:id (admin)
- DELETE /api/products/:id (admin)

### Orders
- POST /api/orders
- GET /api/orders/me
- GET /api/orders (admin)
- PUT /api/orders/:id (admin)

## Tính năng

### User
- Xem sản phẩm theo danh mục
- Tìm kiếm sản phẩm
- Xem chi tiết sản phẩm
- Thêm/xóa giỏ hàng
- Đặt hàng
- Xem lịch sử đơn hàng

### Admin
- Dashboard thống kê
- CRUD sản phẩm
- Quản lý đơn hàng
- Cập nhật trạng thái đơn hàng

## Biến môi trường (backend/.env)

```env
PORT=5000
MONGODB_URI=mongodb://localhost:27017/electronics_shop
JWT_SECRET=your_secret_key
NODE_ENV=development
```
