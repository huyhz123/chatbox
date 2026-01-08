const express = require('express');
const router = express.Router();
const {
  getProducts,
  getProduct,
  createProduct,
  updateProduct,
  deleteProduct
} = require('../controllers/product.controller');
const { protect, adminOnly } = require('../middleware/auth.middleware');
const { validateProduct } = require('../middleware/validate');

router.route('/')
  .get(getProducts)
  .post(protect, adminOnly, validateProduct, createProduct);

router.route('/:id')
  .get(getProduct)
  .put(protect, adminOnly, validateProduct, updateProduct)
  .delete(protect, adminOnly, deleteProduct);

module.exports = router;
