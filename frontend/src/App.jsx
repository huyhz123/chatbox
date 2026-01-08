import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { CartProvider } from './context/CartContext';
import { ToastProvider } from './components/Toast';
import { ConfirmProvider } from './components/ConfirmModal';

// Components
import Navbar from './components/Navbar';
import Footer from './components/Footer';
import ProtectedRoute from './components/ProtectedRoute';

// Pages
import Home from './pages/Home';
import ProductDetail from './pages/ProductDetail';
import Cart from './pages/Cart';
import Login from './pages/Login';
import Register from './pages/Register';
import Orders from './pages/Orders';

// Admin Pages
import Dashboard from './pages/admin/Dashboard';
import AdminProducts from './pages/admin/Products';
import AdminOrders from './pages/admin/Orders';

function App() {
  return (
    <AuthProvider>
      <CartProvider>
        <ToastProvider>
          <ConfirmProvider>
            <BrowserRouter>
              <div className="min-h-screen flex flex-col bg-gray-100">
                <Navbar />
                <main className="flex-1">
                  <Routes>
                    {/* Public routes */}
                    <Route path="/" element={<Home />} />
                    <Route path="/product/:id" element={<ProductDetail />} />
                    <Route path="/cart" element={<Cart />} />
                    <Route path="/login" element={<Login />} />
                    <Route path="/register" element={<Register />} />

                    {/* Protected routes */}
                    <Route
                      path="/orders"
                      element={
                        <ProtectedRoute>
                          <Orders />
                        </ProtectedRoute>
                      }
                    />

                    {/* Admin routes */}
                    <Route
                      path="/admin"
                      element={
                        <ProtectedRoute adminOnly>
                          <Dashboard />
                        </ProtectedRoute>
                      }
                    />
                    <Route
                      path="/admin/products"
                      element={
                        <ProtectedRoute adminOnly>
                          <AdminProducts />
                        </ProtectedRoute>
                      }
                    />
                    <Route
                      path="/admin/orders"
                      element={
                        <ProtectedRoute adminOnly>
                          <AdminOrders />
                        </ProtectedRoute>
                      }
                    />

                    {/* 404 */}
                    <Route
                      path="*"
                      element={
                        <div className="container mx-auto px-4 py-16 text-center">
                          <h1 className="text-4xl font-bold text-dark mb-4">404</h1>
                          <p className="text-gray-500 mb-6">Trang không tồn tại</p>
                          <a href="/" className="btn-primary inline-block">Về trang chủ</a>
                        </div>
                      }
                    />
                  </Routes>
                </main>
                <Footer />
              </div>
            </BrowserRouter>
          </ConfirmProvider>
        </ToastProvider>
      </CartProvider>
    </AuthProvider>
  );
}

export default App;
