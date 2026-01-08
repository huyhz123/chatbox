import { Link } from 'react-router-dom';
import { useCart } from '../context/CartContext';

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price);
};

const ProductCard = ({ product }) => {
  const { addToCart } = useCart();
  const discount = product.oldPrice
    ? Math.round(((product.oldPrice - product.price) / product.oldPrice) * 100)
    : 0;

  const handleAddToCart = (e) => {
    e.preventDefault();
    addToCart(product);
  };

  return (
    <Link to={`/product/${product._id}`} className="card group block overflow-hidden">
      <div className="relative">
        {/* Image */}
        <div className="aspect-square bg-gray-100 p-4 flex items-center justify-center overflow-hidden">
          <img
            src={product.image}
            alt={product.name}
            className="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-300"
            onError={(e) => {
              e.target.src = 'https://via.placeholder.com/300x300?text=No+Image';
            }}
          />
        </div>

        {/* Discount badge */}
        {discount > 0 && (
          <span className="absolute top-2 left-2 bg-secondary text-white text-xs font-bold px-2 py-1 rounded">
            -{discount}%
          </span>
        )}

        {/* Quick add button */}
        <button
          onClick={handleAddToCart}
          className="absolute bottom-2 right-2 bg-primary text-dark p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-yellow-400"
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
        </button>
      </div>

      <div className="p-4">
        {/* Brand */}
        <span className="text-xs text-gray-500 uppercase tracking-wide">
          {product.brand}
        </span>

        {/* Name */}
        <h3 className="font-medium text-dark mt-1 line-clamp-2 min-h-[48px] group-hover:text-secondary transition-colors">
          {product.name}
        </h3>

        {/* Prices */}
        <div className="mt-2 flex items-center gap-2">
          <span className="text-secondary font-bold text-lg">
            {formatPrice(product.price)}
          </span>
          {product.oldPrice && (
            <span className="text-gray-400 text-sm line-through">
              {formatPrice(product.oldPrice)}
            </span>
          )}
        </div>

        {/* Category tag */}
        <span className="inline-block mt-2 text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
          {product.category}
        </span>
      </div>
    </Link>
  );
};

export default ProductCard;
