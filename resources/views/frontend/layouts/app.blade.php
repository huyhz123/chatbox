<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'Welcome to our platform')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        @yield('styles')
    </style>
</head>
<body>
    <!-- Header Navigation -->
    <header>
        <div class="container">
            <nav>
                <div class="nav-logo">
                    <a href="{{ route('home') }}">MyApp</a>
                </div>

                <!-- Desktop Navigation -->
                <ul class="nav-menu" id="navMenu">
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link @if(request()->routeIs('home')) active @endif">
                            Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('services.index') }}" class="nav-link @if(request()->routeIs('services.*')) active @endif">
                            Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('products.index') }}" class="nav-link @if(request()->routeIs('products.*')) active @endif">
                            Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('courses.index') }}" class="nav-link @if(request()->routeIs('courses.*')) active @endif">
                            Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('files.index') }}" class="nav-link @if(request()->routeIs('files.*')) active @endif">
                            Files
                        </a>
                    </li>

                    @auth
                        <li class="nav-item">
                            <a href="{{ route('checkout.index') }}" class="nav-link">
                                Checkout
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('profile.index') }}" class="nav-link @if(request()->routeIs('profile.*')) active @endif">
                                Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline">
                                    Logout
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline">
                                Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('register') }}" class="btn btn-sm btn-primary">
                                Sign Up
                            </a>
                        </li>
                    @endauth
                </ul>

                <!-- Mobile Menu Toggle -->
                <button class="hamburger" id="hamburgerBtn">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @if ($errors->any())
            <div class="container mt-lg">
                <div class="alert alert-danger">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mt-sm" style="margin-left: var(--spacing-lg);">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="container mt-lg">
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container-lg">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>About Us</h4>
                    <p>We provide high-quality services and products for your business needs.</p>
                </div>

                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <a href="{{ route('home') }}">Home</a>
                    <a href="{{ route('services.index') }}">Services</a>
                    <a href="{{ route('products.index') }}">Products</a>
                    <a href="{{ route('courses.index') }}">Courses</a>
                </div>

                <div class="footer-section">
                    <h4>Support</h4>
                    <a href="#contact">Contact Us</a>
                    <a href="#faq">FAQ</a>
                    <a href="#privacy">Privacy Policy</a>
                    <a href="#terms">Terms of Service</a>
                </div>

                <div class="footer-section">
                    <h4>Connect</h4>
                    <a href="#facebook">Facebook</a>
                    <a href="#twitter">Twitter</a>
                    <a href="#linkedin">LinkedIn</a>
                    <a href="#instagram">Instagram</a>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} MyApp. All rights reserved. | Made with ❤️</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const navMenu = document.getElementById('navMenu');

        hamburgerBtn?.addEventListener('click', () => {
            navMenu.classList.toggle('mobile-active');
        });

        // Close menu when a link is clicked
        document.querySelectorAll('.nav-menu .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('mobile-active');
            });
        });

        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId)?.classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId)?.classList.remove('active');
        }

        // Close modal when clicking on close button or overlay
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });

            const closeBtn = modal.querySelector('.modal-close');
            closeBtn?.addEventListener('click', () => {
                modal.classList.remove('active');
            });
        });
    </script>

    @yield('scripts')
</body>
</html>
