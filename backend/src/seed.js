const mongoose = require('mongoose');
const dotenv = require('dotenv');
const User = require('./models/User');
const Product = require('./models/Product');

dotenv.config();

const isFresh = process.argv.includes('--fresh');

const adminUser = {
  name: 'Admin',
  email: 'admin@shop.com',
  password: 'admin123',
  role: 'admin'
};

const sampleProducts = [
  {
    name: 'iPhone 15 Pro Max 256GB',
    price: 34990000,
    oldPrice: 36990000,
    image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/i/p/iphone-15-pro-max_3.png',
    category: 'Điện thoại',
    brand: 'Apple',
    description: 'iPhone 15 Pro Max với chip A17 Pro, camera 48MP, cổng USB-C, khung titan',
    featured: true,
    stock: 50,
    specs: { 'Màn hình': '6.7 inch Super Retina XDR', 'Chip': 'A17 Pro', 'RAM': '8GB', 'Bộ nhớ': '256GB', 'Camera': '48MP + 12MP + 12MP' }
  },
  {
    name: 'iPhone 15 128GB',
    price: 22990000,
    oldPrice: 24990000,
    image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/i/p/iphone-15_1.png',
    category: 'Điện thoại',
    brand: 'Apple',
    description: 'iPhone 15 với Dynamic Island, camera 48MP, USB-C',
    featured: true,
    stock: 80,
    specs: { 'Màn hình': '6.1 inch', 'Chip': 'A16 Bionic', 'RAM': '6GB', 'Bộ nhớ': '128GB' }
  },
  {
    name: 'Samsung Galaxy S24 Ultra 256GB',
    price: 31990000,
    oldPrice: 33990000,
    image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/s/a/samsung-s24-ultra-xam-1.png',
    category: 'Điện thoại',
    brand: 'Samsung',
    description: 'Samsung Galaxy S24 Ultra với bút S Pen, AI Galaxy tích hợp, camera 200MP',
    featured: true,
    stock: 45,
    specs: { 'Màn hình': '6.8 inch Dynamic AMOLED 2X', 'Chip': 'Snapdragon 8 Gen 3', 'RAM': '12GB', 'Bộ nhớ': '256GB' }
  },
  {
    name: 'Samsung Galaxy Z Fold5 512GB',
    price: 41990000,
    oldPrice: 44990000,
    image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/s/a/samsung-galaxy-z-fold-5-xanh-1.png',
    category: 'Điện thoại',
    brand: 'Samsung',
    description: 'Điện thoại gập cao cấp Samsung Galaxy Z Fold5',
    featured: false,
    stock: 20,
    specs: { 'Màn hình chính': '7.6 inch', 'Màn hình phụ': '6.2 inch', 'Chip': 'Snapdragon 8 Gen 2', 'RAM': '12GB' }
  },
  {
    name: 'Xiaomi 14 Ultra 512GB',
    price: 23990000,
    oldPrice: 25990000,
    image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/x/i/xiaomi-14-ultra-den-1.png',
    category: 'Điện thoại',
    brand: 'Xiaomi',
    description: 'Xiaomi 14 Ultra với camera Leica chuyên nghiệp',
    featured: true,
    stock: 35,
    specs: { 'Màn hình': '6.73 inch AMOLED', 'Chip': 'Snapdragon 8 Gen 3', 'RAM': '16GB', 'Bộ nhớ': '512GB' }
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
    stock: 25,
    specs: { 'Màn hình': '14.2 inch Liquid Retina XDR', 'Chip': 'Apple M3 Pro', 'RAM': '18GB', 'SSD': '512GB' }
  },
  {
    name: 'MacBook Air 15" M3 256GB',
    price: 32990000,
    oldPrice: 34990000,
    image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/m/a/macbook-air-15-m3-den-1.png',
    category: 'Laptop',
    brand: 'Apple',
    description: 'MacBook Air 15 inch siêu mỏng với chip M3',
    featured: false,
    stock: 40,
    specs: { 'Màn hình': '15.3 inch Liquid Retina', 'Chip': 'Apple M3', 'RAM': '8GB', 'SSD': '256GB' }
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
    stock: 15,
    specs: { 'Màn hình': '15.6 inch OLED 3.5K', 'CPU': 'Intel Core i7-13700H', 'RAM': '16GB', 'SSD': '512GB' }
  },
  {
    name: 'ASUS ROG Strix G16 RTX 4070',
    price: 42990000,
    oldPrice: 45990000,
    image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/t/e/text_ng_n_1__6_6.png',
    category: 'Laptop',
    brand: 'ASUS',
    description: 'Laptop gaming ASUS ROG với RTX 4070, màn hình 240Hz',
    featured: false,
    stock: 18,
    specs: { 'Màn hình': '16 inch 240Hz', 'CPU': 'Intel Core i9-13980HX', 'RAM': '16GB', 'GPU': 'RTX 4070' }
  },
  {
    name: 'iPad Pro M4 11" 256GB WiFi',
    price: 28990000,
    oldPrice: 30990000,
    image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/i/p/ipad-pro-m4-11-inch-wifi-silver-1.png',
    category: 'Tablet',
    brand: 'Apple',
    description: 'iPad Pro M4 với màn hình Ultra Retina XDR, chip M4 mạnh mẽ',
    featured: true,
    stock: 30,
    specs: { 'Màn hình': '11 inch Ultra Retina XDR', 'Chip': 'Apple M4', 'Bộ nhớ': '256GB', 'Kết nối': 'WiFi 6E' }
  },
  {
    name: 'Samsung Galaxy Tab S9 Ultra',
    price: 27990000,
    oldPrice: 29990000,
    image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/s/a/samsung-galaxy-tab-s9-ultra-1.png',
    category: 'Tablet',
    brand: 'Samsung',
    description: 'Samsung Galaxy Tab S9 Ultra với màn hình AMOLED 14.6 inch',
    featured: false,
    stock: 22,
    specs: { 'Màn hình': '14.6 inch Dynamic AMOLED 2X', 'Chip': 'Snapdragon 8 Gen 2', 'RAM': '12GB', 'Bộ nhớ': '256GB' }
  },
  {
    name: 'Apple Watch Series 9 GPS 45mm',
    price: 11990000,
    oldPrice: 12990000,
    image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/a/p/apple-watch-s9-gps-45mm-vien-nhom-day-cao-su-xanh.png',
    category: 'Đồng hồ',
    brand: 'Apple',
    description: 'Apple Watch Series 9 với chip S9, Double Tap, màn hình sáng hơn',
    featured: true,
    stock: 60,
    specs: { 'Kích thước': '45mm', 'Chip': 'Apple S9', 'Màn hình': 'Retina LTPO OLED', 'Chống nước': '50m' }
  },
  {
    name: 'AirPods Pro 2 USB-C',
    price: 6490000,
    oldPrice: 6990000,
    image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/g/r/group_168_1.png',
    category: 'Âm thanh',
    brand: 'Apple',
    description: 'AirPods Pro 2 với cổng USB-C, chống ồn chủ động ANC',
    featured: true,
    stock: 100,
    specs: { 'Kết nối': 'Bluetooth 5.3', 'Chống ồn': 'ANC + Transparency', 'Pin tai nghe': '6 giờ', 'Pin hộp': '30 giờ' }
  },
  {
    name: 'Sony WH-1000XM5',
    price: 8490000,
    oldPrice: 8990000,
    image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/g/r/group_170_3.png',
    category: 'Âm thanh',
    brand: 'Sony',
    description: 'Tai nghe Sony WH-1000XM5 chống ồn hàng đầu thế giới',
    featured: false,
    stock: 35,
    specs: { 'Driver': '30mm', 'Chống ồn': 'ANC Auto NC Optimizer', 'Pin': '30 giờ', 'Sạc nhanh': '3 phút = 3 giờ' }
  },
  {
    name: 'Sạc nhanh Apple 20W USB-C',
    price: 549000,
    oldPrice: 590000,
    image: 'https://cdn2.cellphones.com.vn/x368,rw,q100/media/catalog/product/a/d/adapter-sac-apple-20w-usb-c-1_1.jpg',
    category: 'Phụ kiện',
    brand: 'Apple',
    description: 'Củ sạc nhanh Apple 20W USB-C Power Adapter chính hãng',
    featured: false,
    stock: 200,
    specs: { 'Công suất': '20W', 'Cổng': 'USB-C', 'Tương thích': 'iPhone 8 trở lên, iPad, AirPods' }
  }
];

const seedDatabase = async () => {
  try {
    // Kết nối MongoDB
    await mongoose.connect(process.env.MONGODB_URI);
    console.log('✅ Kết nối MongoDB thành công');

    // Nếu có flag --fresh thì xóa hết data cũ
    if (isFresh) {
      console.log('🗑️  Xóa dữ liệu cũ...');
      await User.deleteMany({});
      await Product.deleteMany({});
      console.log('✅ Đã xóa dữ liệu cũ');
    }

    // Seed Admin
    const existingAdmin = await User.findOne({ email: adminUser.email });
    if (!existingAdmin) {
      await User.create(adminUser);
      console.log('✅ Tạo admin: admin@shop.com / admin123');
    } else {
      console.log('ℹ️  Admin đã tồn tại, bỏ qua');
    }

    // Seed Products
    const productCount = await Product.countDocuments();
    if (productCount === 0 || isFresh) {
      await Product.insertMany(sampleProducts);
      console.log(`✅ Đã thêm ${sampleProducts.length} sản phẩm mẫu`);
    } else {
      console.log(`ℹ️  Đã có ${productCount} sản phẩm, bỏ qua seed`);
    }

    console.log('\n🎉 Seed hoàn tất!');
    console.log('─'.repeat(40));
    console.log('📧 Admin email: admin@shop.com');
    console.log('🔑 Admin password: admin123');
    console.log('─'.repeat(40));

    process.exit(0);
  } catch (error) {
    console.error('❌ Lỗi seed:', error.message);
    process.exit(1);
  }
};

seedDatabase();
