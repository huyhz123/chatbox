const mongoose = require('mongoose');

const orderItemSchema = new mongoose.Schema({
  product: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'Product',
    required: true
  },
  name: String,
  image: String,
  price: Number,
  quantity: {
    type: Number,
    required: true,
    min: 1
  }
});

const orderSchema = new mongoose.Schema({
  user: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
    required: true
  },
  items: [orderItemSchema],
  totalPrice: {
    type: Number,
    required: true
  },
  shippingAddress: {
    fullName: String,
    phone: String,
    address: String,
    city: String
  },
  paymentMethod: {
    type: String,
    enum: ['COD', 'Banking'],
    default: 'COD'
  },
  status: {
    type: String,
    enum: ['Chờ xác nhận', 'Đang xử lý', 'Đang giao', 'Đã giao', 'Đã hủy'],
    default: 'Chờ xác nhận'
  }
}, {
  timestamps: true
});

module.exports = mongoose.model('Order', orderSchema);
