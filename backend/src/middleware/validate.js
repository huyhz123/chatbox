const { body, validationResult } = require('express-validator');

// Middleware xử lý kết quả validation
const handleValidation = (req, res, next) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({
      success: false,
      message: 'Dữ liệu không hợp lệ',
      errors: errors.array().map(err => ({
        field: err.path,
        message: err.msg
      }))
    });
  }
  next();
};

// Validation rules
const validateRegister = [
  body('name')
    .trim()
    .notEmpty().withMessage('Vui lòng nhập tên')
    .isLength({ min: 2, max: 50 }).withMessage('Tên từ 2-50 ký tự'),
  body('email')
    .trim()
    .notEmpty().withMessage('Vui lòng nhập email')
    .isEmail().withMessage('Email không hợp lệ')
    .normalizeEmail(),
  body('password')
    .notEmpty().withMessage('Vui lòng nhập mật khẩu')
    .isLength({ min: 6 }).withMessage('Mật khẩu tối thiểu 6 ký tự'),
  handleValidation
];

const validateLogin = [
  body('email')
    .trim()
    .notEmpty().withMessage('Vui lòng nhập email')
    .isEmail().withMessage('Email không hợp lệ'),
  body('password')
    .notEmpty().withMessage('Vui lòng nhập mật khẩu'),
  handleValidation
];

const validateProduct = [
  body('name')
    .trim()
    .notEmpty().withMessage('Vui lòng nhập tên sản phẩm')
    .isLength({ max: 200 }).withMessage('Tên sản phẩm tối đa 200 ký tự'),
  body('price')
    .notEmpty().withMessage('Vui lòng nhập giá')
    .isNumeric().withMessage('Giá phải là số')
    .custom(value => value > 0).withMessage('Giá phải lớn hơn 0'),
  body('category')
    .notEmpty().withMessage('Vui lòng chọn danh mục')
    .isIn(['Điện thoại', 'Laptop', 'Tablet', 'Phụ kiện', 'Đồng hồ', 'Âm thanh'])
    .withMessage('Danh mục không hợp lệ'),
  body('brand')
    .trim()
    .notEmpty().withMessage('Vui lòng nhập thương hiệu'),
  body('image')
    .trim()
    .notEmpty().withMessage('Vui lòng nhập URL hình ảnh')
    .isURL().withMessage('URL hình ảnh không hợp lệ'),
  handleValidation
];

const validateOrder = [
  body('items')
    .isArray({ min: 1 }).withMessage('Giỏ hàng không được trống'),
  body('items.*.product')
    .notEmpty().withMessage('ID sản phẩm không hợp lệ'),
  body('items.*.quantity')
    .isInt({ min: 1 }).withMessage('Số lượng tối thiểu là 1'),
  body('totalPrice')
    .isNumeric().withMessage('Tổng tiền không hợp lệ'),
  body('shippingAddress.fullName')
    .trim()
    .notEmpty().withMessage('Vui lòng nhập họ tên'),
  body('shippingAddress.phone')
    .trim()
    .notEmpty().withMessage('Vui lòng nhập số điện thoại')
    .matches(/^[0-9]{10,11}$/).withMessage('Số điện thoại không hợp lệ'),
  body('shippingAddress.address')
    .trim()
    .notEmpty().withMessage('Vui lòng nhập địa chỉ'),
  handleValidation
];

module.exports = {
  validateRegister,
  validateLogin,
  validateProduct,
  validateOrder
};
