import { useState, useEffect } from 'react';
import { ordersAPI } from '../../services/api';

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const statuses = ['Chờ xác nhận', 'Đang xử lý', 'Đang giao', 'Đã giao', 'Đã hủy'];

const statusColors = {
  'Chờ xác nhận': 'bg-yellow-100 text-yellow-800',
  'Đang xử lý': 'bg-blue-100 text-blue-800',
  'Đang giao': 'bg-purple-100 text-purple-800',
  'Đã giao': 'bg-green-100 text-green-800',
  'Đã hủy': 'bg-red-100 text-red-800'
};

const Orders = () => {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filterStatus, setFilterStatus] = useState('');
  const [selectedOrder, setSelectedOrder] = useState(null);

  useEffect(() => {
    fetchOrders();
  }, []);

  const fetchOrders = async () => {
    try {
      const res = await ordersAPI.getAll();
      setOrders(res.data.orders);
    } catch (error) {
      console.error('Error fetching orders:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleUpdateStatus = async (orderId, newStatus) => {
    try {
      await ordersAPI.updateStatus(orderId, newStatus);
      setOrders(orders.map(order =>
        order._id === orderId ? { ...order, status: newStatus } : order
      ));
    } catch (error) {
      alert('Lỗi: ' + (error.response?.data?.message || error.message));
    }
  };

  const filteredOrders = filterStatus
    ? orders.filter(order => order.status === filterStatus)
    : orders;

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h1 className="text-2xl font-bold text-dark">Quản lý đơn hàng</h1>
        <div className="flex items-center gap-2">
          <span className="text-sm text-gray-500">Lọc:</span>
          <select
            value={filterStatus}
            onChange={(e) => setFilterStatus(e.target.value)}
            className="input-field w-auto"
          >
            <option value="">Tất cả</option>
            {statuses.map((status) => (
              <option key={status} value={status}>{status}</option>
            ))}
          </select>
        </div>
      </div>

      {loading ? (
        <div className="animate-pulse space-y-4">
          {[...Array(5)].map((_, i) => (
            <div key={i} className="bg-gray-200 h-24 rounded-lg"></div>
          ))}
        </div>
      ) : filteredOrders.length === 0 ? (
        <div className="text-center py-12">
          <p className="text-gray-500">Không có đơn hàng nào</p>
        </div>
      ) : (
        <div className="space-y-4">
          {filteredOrders.map((order) => (
            <div key={order._id} className="bg-white rounded-lg shadow-sm overflow-hidden">
              <div className="p-4 border-b bg-gray-50 flex flex-wrap items-center justify-between gap-4">
                <div className="flex items-center gap-4">
                  <div>
                    <p className="text-xs text-gray-500">Mã đơn</p>
                    <p className="font-mono text-sm">{order._id}</p>
                  </div>
                  <div>
                    <p className="text-xs text-gray-500">Khách hàng</p>
                    <p className="font-medium">{order.user?.name || 'N/A'}</p>
                  </div>
                  <div>
                    <p className="text-xs text-gray-500">Ngày đặt</p>
                    <p className="text-sm">{formatDate(order.createdAt)}</p>
                  </div>
                </div>
                <div className="flex items-center gap-4">
                  <select
                    value={order.status}
                    onChange={(e) => handleUpdateStatus(order._id, e.target.value)}
                    className={`px-3 py-1 rounded-full text-sm font-medium border-0 cursor-pointer ${statusColors[order.status]}`}
                  >
                    {statuses.map((status) => (
                      <option key={status} value={status}>{status}</option>
                    ))}
                  </select>
                  <button
                    onClick={() => setSelectedOrder(selectedOrder === order._id ? null : order._id)}
                    className="text-gray-500 hover:text-dark"
                  >
                    <svg className={`w-5 h-5 transition ${selectedOrder === order._id ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                </div>
              </div>

              {/* Order details */}
              {selectedOrder === order._id && (
                <div className="p-4">
                  <div className="grid md:grid-cols-2 gap-6">
                    {/* Products */}
                    <div>
                      <h3 className="font-medium mb-3">Sản phẩm</h3>
                      <div className="space-y-2">
                        {order.items.map((item, index) => (
                          <div key={index} className="flex gap-3 p-2 bg-gray-50 rounded">
                            <img
                              src={item.image}
                              alt={item.name}
                              className="w-12 h-12 object-contain rounded"
                              onError={(e) => {
                                e.target.src = 'https://via.placeholder.com/48?text=No';
                              }}
                            />
                            <div className="flex-1 min-w-0">
                              <p className="text-sm font-medium line-clamp-1">{item.name}</p>
                              <p className="text-xs text-gray-500">
                                {formatPrice(item.price)} x {item.quantity}
                              </p>
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>

                    {/* Shipping info */}
                    <div>
                      <h3 className="font-medium mb-3">Thông tin giao hàng</h3>
                      <div className="bg-gray-50 p-4 rounded space-y-2 text-sm">
                        <p><span className="text-gray-500">Họ tên:</span> {order.shippingAddress?.fullName}</p>
                        <p><span className="text-gray-500">SĐT:</span> {order.shippingAddress?.phone}</p>
                        <p><span className="text-gray-500">Địa chỉ:</span> {order.shippingAddress?.address}</p>
                        <p><span className="text-gray-500">Thành phố:</span> {order.shippingAddress?.city}</p>
                        <p><span className="text-gray-500">Thanh toán:</span> {order.paymentMethod}</p>
                      </div>

                      <div className="mt-4 p-4 bg-primary/10 rounded">
                        <p className="text-sm text-gray-500">Tổng tiền</p>
                        <p className="text-2xl font-bold text-secondary">{formatPrice(order.totalPrice)}</p>
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default Orders;
