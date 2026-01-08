import { useState, useEffect } from 'react';
import { productsAPI } from '../../services/api';
import { useToast } from '../../components/Toast';
import { useConfirm } from '../../components/ConfirmModal';

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price);
};

const initialFormState = {
  name: '',
  price: '',
  oldPrice: '',
  image: '',
  category: 'Điện thoại',
  brand: '',
  description: '',
  featured: false
};

const categories = ['Điện thoại', 'Laptop', 'Tablet', 'Phụ kiện', 'Đồng hồ', 'Âm thanh'];

const Products = () => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [formData, setFormData] = useState(initialFormState);
  const [submitting, setSubmitting] = useState(false);

  const toast = useToast();
  const confirm = useConfirm();

  useEffect(() => {
    fetchProducts();
  }, []);

  const fetchProducts = async () => {
    try {
      const res = await productsAPI.getAll();
      setProducts(res.data.products);
    } catch (error) {
      toast.error('Không thể tải danh sách sản phẩm');
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);

    try {
      const data = {
        ...formData,
        price: Number(formData.price),
        oldPrice: formData.oldPrice ? Number(formData.oldPrice) : null
      };

      if (editingId) {
        await productsAPI.update(editingId, data);
        toast.success('Cập nhật sản phẩm thành công!');
      } else {
        await productsAPI.create(data);
        toast.success('Thêm sản phẩm mới thành công!');
      }

      fetchProducts();
      resetForm();
    } catch (error) {
      toast.error(error.response?.data?.message || 'Có lỗi xảy ra');
    } finally {
      setSubmitting(false);
    }
  };

  const handleEdit = (product) => {
    setFormData({
      name: product.name,
      price: product.price,
      oldPrice: product.oldPrice || '',
      image: product.image,
      category: product.category,
      brand: product.brand,
      description: product.description || '',
      featured: product.featured || false
    });
    setEditingId(product._id);
    setShowForm(true);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleDelete = async (product) => {
    const confirmed = await confirm({
      title: 'Xóa sản phẩm',
      message: `Bạn có chắc muốn xóa "${product.name}"? Hành động này không thể hoàn tác.`,
      confirmText: 'Xóa',
      cancelText: 'Hủy',
      type: 'danger'
    });

    if (!confirmed) return;

    try {
      await productsAPI.delete(product._id);
      toast.success('Đã xóa sản phẩm');
      fetchProducts();
    } catch (error) {
      toast.error(error.response?.data?.message || 'Không thể xóa sản phẩm');
    }
  };

  const resetForm = () => {
    setFormData(initialFormState);
    setEditingId(null);
    setShowForm(false);
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold text-dark">Quản lý sản phẩm</h1>
        <button
          onClick={() => {
            if (showForm) resetForm();
            else setShowForm(true);
          }}
          className="btn-primary flex items-center gap-2"
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={showForm ? "M6 18L18 6M6 6l12 12" : "M12 6v6m0 0v6m0-6h6m-6 0H6"} />
          </svg>
          {showForm ? 'Đóng' : 'Thêm sản phẩm'}
        </button>
      </div>

      {/* Form */}
      {showForm && (
        <div className="bg-white rounded-lg shadow-sm p-6 mb-6 animate-fade-in">
          <h2 className="font-bold text-lg mb-4">
            {editingId ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới'}
          </h2>
          <form onSubmit={handleSubmit} className="grid md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-1">Tên sản phẩm *</label>
              <input
                type="text"
                required
                disabled={submitting}
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                className="input-field"
                placeholder="iPhone 15 Pro Max 256GB"
              />
            </div>
            <div>
              <label className="block text-sm font-medium mb-1">Thương hiệu *</label>
              <input
                type="text"
                required
                disabled={submitting}
                value={formData.brand}
                onChange={(e) => setFormData({ ...formData, brand: e.target.value })}
                className="input-field"
                placeholder="Apple"
              />
            </div>
            <div>
              <label className="block text-sm font-medium mb-1">Giá (VNĐ) *</label>
              <input
                type="number"
                required
                disabled={submitting}
                value={formData.price}
                onChange={(e) => setFormData({ ...formData, price: e.target.value })}
                className="input-field"
                placeholder="34990000"
              />
            </div>
            <div>
              <label className="block text-sm font-medium mb-1">Giá cũ (VNĐ)</label>
              <input
                type="number"
                disabled={submitting}
                value={formData.oldPrice}
                onChange={(e) => setFormData({ ...formData, oldPrice: e.target.value })}
                className="input-field"
                placeholder="36990000"
              />
            </div>
            <div>
              <label className="block text-sm font-medium mb-1">Danh mục *</label>
              <select
                disabled={submitting}
                value={formData.category}
                onChange={(e) => setFormData({ ...formData, category: e.target.value })}
                className="input-field"
              >
                {categories.map((cat) => (
                  <option key={cat} value={cat}>{cat}</option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium mb-1">URL hình ảnh *</label>
              <input
                type="url"
                required
                disabled={submitting}
                value={formData.image}
                onChange={(e) => setFormData({ ...formData, image: e.target.value })}
                className="input-field"
                placeholder="https://example.com/image.png"
              />
            </div>
            <div className="md:col-span-2">
              <label className="block text-sm font-medium mb-1">Mô tả</label>
              <textarea
                disabled={submitting}
                value={formData.description}
                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                className="input-field"
                rows={3}
                placeholder="Mô tả chi tiết sản phẩm..."
              />
            </div>
            <div className="md:col-span-2">
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  disabled={submitting}
                  checked={formData.featured}
                  onChange={(e) => setFormData({ ...formData, featured: e.target.checked })}
                  className="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"
                />
                <span className="text-sm">Sản phẩm nổi bật (hiển thị ở trang chủ)</span>
              </label>
            </div>
            <div className="md:col-span-2 flex gap-4">
              <button
                type="submit"
                disabled={submitting}
                className="btn-primary flex items-center gap-2"
              >
                {submitting && <span className="spinner" />}
                {submitting ? 'Đang lưu...' : (editingId ? 'Cập nhật' : 'Thêm mới')}
              </button>
              <button
                type="button"
                onClick={resetForm}
                disabled={submitting}
                className="btn-outline"
              >
                Hủy
              </button>
            </div>
          </form>
        </div>
      )}

      {/* Products table */}
      {loading ? (
        <div className="space-y-4">
          {[...Array(5)].map((_, i) => (
            <div key={i} className="bg-gray-200 h-20 rounded-lg animate-pulse"></div>
          ))}
        </div>
      ) : products.length === 0 ? (
        <div className="bg-white rounded-lg p-12 text-center">
          <svg className="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
          </svg>
          <p className="text-gray-500 mb-4">Chưa có sản phẩm nào</p>
          <button onClick={() => setShowForm(true)} className="btn-primary">
            Thêm sản phẩm đầu tiên
          </button>
        </div>
      ) : (
        <div className="bg-white rounded-lg shadow-sm overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left text-sm font-medium text-gray-500">Sản phẩm</th>
                  <th className="px-4 py-3 text-left text-sm font-medium text-gray-500">Danh mục</th>
                  <th className="px-4 py-3 text-left text-sm font-medium text-gray-500">Giá</th>
                  <th className="px-4 py-3 text-left text-sm font-medium text-gray-500">Nổi bật</th>
                  <th className="px-4 py-3 text-right text-sm font-medium text-gray-500">Thao tác</th>
                </tr>
              </thead>
              <tbody className="divide-y">
                {products.map((product) => (
                  <tr key={product._id} className="hover:bg-gray-50">
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-3">
                        <img
                          src={product.image}
                          alt={product.name}
                          className="w-12 h-12 object-contain bg-gray-100 rounded"
                          onError={(e) => {
                            e.target.src = 'https://via.placeholder.com/48?text=No';
                          }}
                        />
                        <div>
                          <p className="font-medium line-clamp-1 max-w-xs">{product.name}</p>
                          <p className="text-sm text-gray-500">{product.brand}</p>
                        </div>
                      </div>
                    </td>
                    <td className="px-4 py-3 text-sm">{product.category}</td>
                    <td className="px-4 py-3">
                      <p className="font-medium text-secondary">{formatPrice(product.price)}</p>
                      {product.oldPrice && (
                        <p className="text-sm text-gray-400 line-through">{formatPrice(product.oldPrice)}</p>
                      )}
                    </td>
                    <td className="px-4 py-3">
                      {product.featured && (
                        <span className="bg-primary text-dark text-xs px-2 py-1 rounded font-medium">Hot</span>
                      )}
                    </td>
                    <td className="px-4 py-3 text-right">
                      <button
                        onClick={() => handleEdit(product)}
                        className="text-blue-500 hover:text-blue-700 font-medium mr-3"
                      >
                        Sửa
                      </button>
                      <button
                        onClick={() => handleDelete(product)}
                        className="text-red-500 hover:text-red-700 font-medium"
                      >
                        Xóa
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <div className="px-4 py-3 border-t bg-gray-50 text-sm text-gray-500">
            Tổng: {products.length} sản phẩm
          </div>
        </div>
      )}
    </div>
  );
};

export default Products;
