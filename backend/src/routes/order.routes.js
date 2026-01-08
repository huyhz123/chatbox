const express = require('express');
const router = express.Router();
const {
  createOrder,
  getMyOrders,
  getAllOrders,
  updateOrderStatus
} = require('../controllers/order.controller');
const { protect, adminOnly } = require('../middleware/auth.middleware');

router.route('/')
  .get(protect, adminOnly, getAllOrders)
  .post(protect, createOrder);

router.get('/me', protect, getMyOrders);
router.put('/:id', protect, adminOnly, updateOrderStatus);

module.exports = router;
