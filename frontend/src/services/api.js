import axios from 'axios';

// API URL - sử dụng proxy trong dev, URL trực tiếp trong production
const API_URL = import.meta.env.VITE_API_URL || '/api';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
  timeout: 10000, // 10s timeout
});

// Request interceptor - thêm token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor - xử lý lỗi
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const { response } = error;

    // Xử lý lỗi theo status code
    if (response) {
      switch (response.status) {
        case 401:
          // Token hết hạn hoặc không hợp lệ
          localStorage.removeItem('token');
          // Redirect về login nếu không phải đang ở login
          if (!window.location.pathname.includes('/login')) {
            window.location.href = '/login';
          }
          break;
        case 403:
          // Không có quyền truy cập
          console.error('Không có quyền truy cập');
          break;
        case 404:
          console.error('Không tìm thấy tài nguyên');
          break;
        case 500:
          console.error('Lỗi server');
          break;
        default:
          break;
      }
    } else if (error.code === 'ECONNABORTED') {
      console.error('Request timeout');
    } else if (!navigator.onLine) {
      console.error('Không có kết nối mạng');
    }

    return Promise.reject(error);
  }
);

// Auth API
export const authAPI = {
  register: (data) => api.post('/auth/register', data),
  login: (data) => api.post('/auth/login', data),
  getMe: () => api.get('/auth/me'),
};

// Products API
export const productsAPI = {
  getAll: (params) => api.get('/products', { params }),
  getById: (id) => api.get(`/products/${id}`),
  create: (data) => api.post('/products', data),
  update: (id, data) => api.put(`/products/${id}`, data),
  delete: (id) => api.delete(`/products/${id}`),
};

// Orders API
export const ordersAPI = {
  create: (data) => api.post('/orders', data),
  getMyOrders: () => api.get('/orders/me'),
  getAll: () => api.get('/orders'),
  updateStatus: (id, status) => api.put(`/orders/${id}`, { status }),
};

export default api;
