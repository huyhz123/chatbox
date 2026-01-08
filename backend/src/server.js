const express = require('express');
const cors = require('cors');
const dotenv = require('dotenv');
const connectDB = require('./config/db');
const User = require('./models/User');
const Product = require('./models/Product');

// Load env vars
dotenv.config();

// Connect to database
connectDB();

const app = express();

// Middleware
app.use(cors());
app.use(express.json());

// Routes
app.use('/api/auth', require('./routes/auth.routes'));
app.use('/api/products', require('./routes/product.routes'));
app.use('/api/orders', require('./routes/order.routes'));

// Health check
app.get('/api/health', (req, res) => {
  res.json({ status: 'OK', message: 'Server is running' });
});

// Seed data function
const seedData = async () => {
  try {
    // Check if admin exists
    const adminExists = await User.findOne({ email: 'admin@shop.com' });
    if (!adminExists) {
      await User.create({
        name: 'Admin',
        email: 'admin@shop.com',
        password: 'admin123',
        role: 'admin'
      });
      console.log('Admin user created: admin@shop.com / admin123');
    }

    // Check if products exist
    const productCount = await Product.countDocuments();
    if (productCount === 0) {
      const sampleProducts = [
        {
          name: 'iPhone 15 Pro Max 256GB',
          price: 34990000,
          oldPrice: 36990000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/i/p/iphone-15-pro-max_3.png',
          category: 'Điện thoại',
          brand: 'Apple',
          description: 'iPhone 15 Pro Max với chip A17 Pro, camera 48MP, cổng USB-C',
          featured: true,
          specs: { 'Màn hình': '6.7 inch', 'Chip': 'A17 Pro', 'RAM': '8GB', 'Bộ nhớ': '256GB' }
        },
        {
          name: 'Samsung Galaxy S24 Ultra 256GB',
          price: 31990000,
          oldPrice: 33990000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/s/a/samsung-s24-ultra-xam-1.png',
          category: 'Điện thoại',
          brand: 'Samsung',
          description: 'Samsung Galaxy S24 Ultra với bút S Pen, AI tích hợp',
          featured: true,
          specs: { 'Màn hình': '6.8 inch', 'Chip': 'Snapdragon 8 Gen 3', 'RAM': '12GB', 'Bộ nhớ': '256GB' }
        },
        {
          name: 'Xiaomi 14 Ultra 512GB',
          price: 23990000,
          oldPrice: 25990000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/x/i/xiaomi-14-ultra-den-1.png',
          category: 'Điện thoại',
          brand: 'Xiaomi',
          description: 'Xiaomi 14 Ultra với camera Leica, chip Snapdragon 8 Gen 3',
          featured: true,
          specs: { 'Màn hình': '6.73 inch', 'Chip': 'Snapdragon 8 Gen 3', 'RAM': '16GB', 'Bộ nhớ': '512GB' }
        },
        {
          name: 'OPPO Find X7 Ultra 256GB',
          price: 22990000,
          oldPrice: 24990000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/o/p/oppo-find-x7-ultra-xanh-glr-1.png',
          category: 'Điện thoại',
          brand: 'OPPO',
          description: 'OPPO Find X7 Ultra với camera kép periscope',
          featured: false,
          specs: { 'Màn hình': '6.82 inch', 'Chip': 'Snapdragon 8 Gen 3', 'RAM': '16GB', 'Bộ nhớ': '256GB' }
        },
        {
          name: 'MacBook Pro 14" M3 Pro 512GB',
          price: 49990000,
          oldPrice: 52990000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/m/a/macbook-pro-14-2023-m3-pro-1.png',
          category: 'Laptop',
          brand: 'Apple',
          description: 'MacBook Pro 14 inch với chip M3 Pro, màn hình Liquid Retina XDR',
          featured: true,
          specs: { 'Màn hình': '14.2 inch', 'Chip': 'M3 Pro', 'RAM': '18GB', 'SSD': '512GB' }
        },
        {
          name: 'Dell XPS 15 9530 i7-13700H',
          price: 45990000,
          oldPrice: 48990000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/d/e/dell-xps-15-9530-2_1.jpg',
          category: 'Laptop',
          brand: 'Dell',
          description: 'Dell XPS 15 với màn hình OLED 3.5K, Intel Core i7 Gen 13',
          featured: true,
          specs: { 'Màn hình': '15.6 inch OLED', 'CPU': 'i7-13700H', 'RAM': '16GB', 'SSD': '512GB' }
        },
        {
          name: 'ASUS ROG Strix G16 i9-14900HX RTX 4070',
          price: 52990000,
          oldPrice: 55990000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/t/e/text_ng_n_1__6_6.png',
          category: 'Laptop',
          brand: 'ASUS',
          description: 'Laptop gaming ASUS ROG với RTX 4070, màn hình 240Hz',
          featured: false,
          specs: { 'Màn hình': '16 inch 240Hz', 'CPU': 'i9-14900HX', 'RAM': '32GB', 'SSD': '1TB' }
        },
        {
          name: 'iPad Pro M4 11" 256GB WiFi',
          price: 28990000,
          oldPrice: 30990000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/i/p/ipad-pro-m4-11-inch-wifi-silver-1.png',
          category: 'Tablet',
          brand: 'Apple',
          description: 'iPad Pro M4 với màn hình Ultra Retina XDR, chip M4',
          featured: true,
          specs: { 'Màn hình': '11 inch', 'Chip': 'M4', 'Bộ nhớ': '256GB', 'Kết nối': 'WiFi' }
        },
        {
          name: 'Samsung Galaxy Tab S9 Ultra 512GB',
          price: 27990000,
          oldPrice: 29990000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/s/a/samsung-galaxy-tab-s9-ultra-1.png',
          category: 'Tablet',
          brand: 'Samsung',
          description: 'Samsung Galaxy Tab S9 Ultra với màn hình AMOLED 14.6 inch',
          featured: false,
          specs: { 'Màn hình': '14.6 inch AMOLED', 'Chip': 'Snapdragon 8 Gen 2', 'RAM': '12GB', 'Bộ nhớ': '512GB' }
        },
        {
          name: 'Apple Watch Series 9 GPS 45mm',
          price: 11990000,
          oldPrice: 12990000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/a/p/apple-watch-s9-gps-45mm-vien-nhom-day-cao-su-xanh.png',
          category: 'Đồng hồ',
          brand: 'Apple',
          description: 'Apple Watch Series 9 với chip S9, Double Tap',
          featured: true,
          specs: { 'Màn hình': '45mm', 'Chip': 'S9', 'Kết nối': 'GPS', 'Chống nước': '50m' }
        },
        {
          name: 'Samsung Galaxy Watch 6 Classic 47mm',
          price: 9990000,
          oldPrice: 10990000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/g/a/galaxy-watch-6-classic-47mm-lte-silver-1.png',
          category: 'Đồng hồ',
          brand: 'Samsung',
          description: 'Samsung Galaxy Watch 6 Classic với vành xoay vật lý',
          featured: false,
          specs: { 'Màn hình': '47mm', 'Chip': 'Exynos W930', 'RAM': '2GB', 'Bộ nhớ': '16GB' }
        },
        {
          name: 'AirPods Pro 2 USB-C',
          price: 6490000,
          oldPrice: 6990000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/g/r/group_168_1.png',
          category: 'Âm thanh',
          brand: 'Apple',
          description: 'AirPods Pro thế hệ 2 với cổng USB-C, chống ồn chủ động',
          featured: true,
          specs: { 'Kết nối': 'Bluetooth 5.3', 'Chống ồn': 'ANC', 'Pin': '6h (30h với hộp)', 'Cổng sạc': 'USB-C' }
        },
        {
          name: 'Sony WH-1000XM5',
          price: 8490000,
          oldPrice: 8990000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/g/r/group_170_3.png',
          category: 'Âm thanh',
          brand: 'Sony',
          description: 'Tai nghe Sony WH-1000XM5 với chống ồn hàng đầu thế giới',
          featured: false,
          specs: { 'Driver': '30mm', 'Chống ồn': 'ANC', 'Pin': '30 giờ', 'Kết nối': 'Bluetooth 5.2' }
        },
        {
          name: 'Sạc nhanh Apple 20W USB-C',
          price: 549000,
          oldPrice: 590000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/a/d/adapter-sac-apple-20w-usb-c-1_1.jpg',
          category: 'Phụ kiện',
          brand: 'Apple',
          description: 'Củ sạc nhanh Apple 20W USB-C chính hãng',
          featured: false,
          specs: { 'Công suất': '20W', 'Cổng': 'USB-C', 'Tương thích': 'iPhone, iPad, AirPods' }
        },
        {
          name: 'Cáp Anker USB-C to Lightning 1.8m',
          price: 350000,
          oldPrice: 390000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/c/a/cap-sac-anker-powerline-iii-flow-usb-c-to-lightning-0-9m-a8662-1_2.jpg',
          category: 'Phụ kiện',
          brand: 'Anker',
          description: 'Cáp sạc Anker PowerLine III Flow USB-C to Lightning',
          featured: false,
          specs: { 'Độ dài': '1.8m', 'Công suất': '100W', 'Chất liệu': 'Silicone' }
        },
        {
          name: 'iPhone 15 128GB',
          price: 22990000,
          oldPrice: 24990000,
          image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/i/p/iphone-15_1.png',
          category: 'Điện thoại',
          brand: 'Apple',
          description: 'iPhone 15 với Dynamic Island, camera 48MP',
          featured: true,
          specs: { 'Màn hình': '6.1 inch', 'Chip': 'A16 Bionic', 'RAM': '6GB', 'Bộ nhớ': '128GB' }
        }
      ];

      await Product.insertMany(sampleProducts);
      console.log('Sample products created');
    }
  } catch (error) {
    console.error('Error seeding data:', error);
  }
};

// Run seed
seedData();

const PORT = process.env.PORT || 5000;

app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});
