import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';

const banners = [
  {
    id: 1,
    title: 'iPhone 15 Pro Max',
    subtitle: 'Giảm đến 2.000.000đ',
    description: 'Titan. Mạnh mẽ. Đẳng cấp.',
    bg: 'from-gray-900 to-gray-700',
    textColor: 'text-white',
    link: '/?category=Điện thoại&brand=Apple'
  },
  {
    id: 2,
    title: 'MacBook Pro M3',
    subtitle: 'Trả góp 0%',
    description: 'Hiệu năng vượt trội với chip M3',
    bg: 'from-blue-900 to-blue-600',
    textColor: 'text-white',
    link: '/?category=Laptop&brand=Apple'
  },
  {
    id: 3,
    title: 'Galaxy S24 Ultra',
    subtitle: 'Tặng kèm bao da',
    description: 'AI tích hợp - Chụp ảnh chuyên nghiệp',
    bg: 'from-violet-900 to-violet-600',
    textColor: 'text-white',
    link: '/?category=Điện thoại&brand=Samsung'
  }
];

const Banner = () => {
  const [currentSlide, setCurrentSlide] = useState(0);

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % banners.length);
    }, 5000);
    return () => clearInterval(timer);
  }, []);

  return (
    <div className="relative overflow-hidden">
      <div
        className="flex transition-transform duration-500 ease-out"
        style={{ transform: `translateX(-${currentSlide * 100}%)` }}
      >
        {banners.map((banner) => (
          <Link
            key={banner.id}
            to={banner.link}
            className={`min-w-full bg-gradient-to-r ${banner.bg} ${banner.textColor}`}
          >
            <div className="container mx-auto px-4 py-12 md:py-20">
              <div className="max-w-xl">
                <span className="inline-block bg-secondary text-white text-sm px-3 py-1 rounded-full mb-4">
                  {banner.subtitle}
                </span>
                <h2 className="text-3xl md:text-5xl font-bold mb-4">
                  {banner.title}
                </h2>
                <p className="text-lg md:text-xl opacity-90 mb-6">
                  {banner.description}
                </p>
                <span className="inline-block bg-primary text-dark font-semibold px-6 py-3 rounded-lg hover:bg-yellow-400 transition">
                  Xem ngay
                </span>
              </div>
            </div>
          </Link>
        ))}
      </div>

      {/* Dots */}
      <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
        {banners.map((_, index) => (
          <button
            key={index}
            onClick={() => setCurrentSlide(index)}
            className={`w-3 h-3 rounded-full transition ${
              index === currentSlide ? 'bg-primary' : 'bg-white/50'
            }`}
          />
        ))}
      </div>

      {/* Arrows */}
      <button
        onClick={() => setCurrentSlide((prev) => (prev - 1 + banners.length) % banners.length)}
        className="absolute left-4 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/30 p-2 rounded-full transition"
      >
        <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <button
        onClick={() => setCurrentSlide((prev) => (prev + 1) % banners.length)}
        className="absolute right-4 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/30 p-2 rounded-full transition"
      >
        <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>
  );
};

export default Banner;
