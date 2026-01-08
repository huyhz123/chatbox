const mongoose = require('mongoose');

const productSchema = new mongoose.Schema({
  name: {
    type: String,
    required: [true, 'Vui lòng nhập tên sản phẩm'],
    trim: true
  },
  price: {
    type: Number,
    required: [true, 'Vui lòng nhập giá sản phẩm']
  },
  oldPrice: {
    type: Number,
    default: null
  },
  image: {
    type: String,
    required: [true, 'Vui lòng thêm ảnh sản phẩm']
  },
  category: {
    type: String,
    required: [true, 'Vui lòng chọn danh mục'],
    enum: ['Điện thoại', 'Laptop', 'Tablet', 'Phụ kiện', 'Đồng hồ', 'Âm thanh']
  },
  brand: {
    type: String,
    required: [true, 'Vui lòng nhập thương hiệu']
  },
  description: {
    type: String,
    default: ''
  },
  specs: {
    type: Map,
    of: String,
    default: {}
  },
  stock: {
    type: Number,
    default: 100
  },
  featured: {
    type: Boolean,
    default: false
  }
}, {
  timestamps: true
});

module.exports = mongoose.model('Product', productSchema);
