import { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import Banner from '../components/Banner';
import ProductCard from '../components/ProductCard';
import { productsAPI } from '../services/api';

const Home = () => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchParams] = useSearchParams();

  const category = searchParams.get('category');
  const search = searchParams.get('search');
  const brand = searchParams.get('brand');

  useEffect(() => {
    fetchProducts();
  }, [category, search, brand]);

  const fetchProducts = async () => {
    setLoading(true);
    try {
      const params = {};
      if (category) params.category = category;
      if (search) params.search = search;
      if (brand) params.brand = brand;

      const res = await productsAPI.getAll(params);
      setProducts(res.data.products);
    } catch (error) {
      console.error('Error fetching products:', error);
    } finally {
      setLoading(false);
    }
  };

  const featuredProducts = products.filter(p => p.featured);
  const regularProducts = category || search || brand ? products : products.filter(p => !p.featured);

  return (
    <div>
      {/* Banner - only show on home page without filters */}
      {!category && !search && !brand && <Banner />}

      <div className="container mx-auto px-4 py-8">
        {/* Page title */}
        {(category || search || brand) && (
          <div className="mb-6">
            <h1 className="text-2xl font-bold text-dark">
              {search ? `Kết quả tìm kiếm: "${search}"` : category || brand}
            </h1>
            <p className="text-gray-500 mt-1">
              {products.length} sản phẩm
            </p>
          </div>
        )}

        {/* Featured products */}
        {!category && !search && !brand && featuredProducts.length > 0 && (
          <section className="mb-12">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-2xl font-bold text-dark">
                <span className="text-secondary">Hot</span> Sản phẩm nổi bật
              </h2>
            </div>
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              {featuredProducts.map((product) => (
                <ProductCard key={product._id} product={product} />
              ))}
            </div>
          </section>
        )}

        {/* Regular products */}
        <section>
          {!category && !search && !brand && (
            <h2 className="text-2xl font-bold text-dark mb-6">Tất cả sản phẩm</h2>
          )}

          {loading ? (
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              {[...Array(8)].map((_, i) => (
                <div key={i} className="card animate-pulse">
                  <div className="aspect-square bg-gray-200"></div>
                  <div className="p-4 space-y-3">
                    <div className="h-3 bg-gray-200 rounded w-1/4"></div>
                    <div className="h-4 bg-gray-200 rounded"></div>
                    <div className="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div className="h-5 bg-gray-200 rounded w-1/2"></div>
                  </div>
                </div>
              ))}
            </div>
          ) : regularProducts.length > 0 ? (
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              {regularProducts.map((product) => (
                <ProductCard key={product._id} product={product} />
              ))}
            </div>
          ) : (
            <div className="text-center py-12">
              <svg className="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p className="text-gray-500 text-lg">Không tìm thấy sản phẩm nào</p>
            </div>
          )}
        </section>

        {/* Categories quick links */}
        {!category && !search && !brand && (
          <section className="mt-12">
            <h2 className="text-2xl font-bold text-dark mb-6">Danh mục sản phẩm</h2>
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
              {[
                { name: 'Điện thoại', icon: '📱', color: 'bg-blue-50 hover:bg-blue-100' },
                { name: 'Laptop', icon: '💻', color: 'bg-purple-50 hover:bg-purple-100' },
                { name: 'Tablet', icon: '📟', color: 'bg-green-50 hover:bg-green-100' },
                { name: 'Đồng hồ', icon: '⌚', color: 'bg-orange-50 hover:bg-orange-100' },
                { name: 'Âm thanh', icon: '🎧', color: 'bg-pink-50 hover:bg-pink-100' },
                { name: 'Phụ kiện', icon: '🔌', color: 'bg-yellow-50 hover:bg-yellow-100' },
              ].map((cat) => (
                <a
                  key={cat.name}
                  href={`/?category=${encodeURIComponent(cat.name)}`}
                  className={`${cat.color} rounded-xl p-6 text-center transition-colors`}
                >
                  <span className="text-4xl">{cat.icon}</span>
                  <p className="mt-2 font-medium text-dark">{cat.name}</p>
                </a>
              ))}
            </div>
          </section>
        )}
      </div>
    </div>
  );
};

export default Home;
