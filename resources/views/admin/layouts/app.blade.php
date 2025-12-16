<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'Admin Dashboard')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        @yield('styles')
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <a href="{{ route('admin.dashboard') }}">Admin</a>
            </div>

            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link @if(request()->routeIs('admin.dashboard')) active @endif">
                        <span>📊</span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('admin.services.index') }}" class="sidebar-link @if(request()->routeIs('admin.services.*')) active @endif">
                        <span>🔧</span>
                        <span>Services</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('admin.products.index') }}" class="sidebar-link @if(request()->routeIs('admin.products.*')) active @endif">
                        <span>📦</span>
                        <span>Products</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('admin.orders.index') }}" class="sidebar-link @if(request()->routeIs('admin.orders.*')) active @endif">
                        <span>🛒</span>
                        <span>Orders</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('admin.courses.index') }}" class="sidebar-link @if(request()->routeIs('admin.courses.*')) active @endif">
                        <span>📚</span>
                        <span>Courses</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('admin.users.index') }}" class="sidebar-link @if(request()->routeIs('admin.users.*')) active @endif">
                        <span>👥</span>
                        <span>Users</span>
                    </a>
                </li>
            </ul>

            <div style="margin-top: auto;">
                <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: var(--spacing-xl) 0;">
                <a href="{{ route('profile.index') }}" class="sidebar-link">
                    <span>⚙️</span>
                    <span>Settings</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" style="margin-top: var(--spacing-md);">
                    @csrf
                    <button type="submit" class="sidebar-link w-full text-center" style="background: none; border: none; cursor: pointer;">
                        <span>🚪</span>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Top Bar -->
        <header class="topbar">
            <div class="flex-between w-full">
                <div>
                    <h2 style="margin: 0;">@yield('page_title', 'Dashboard')</h2>
                </div>
                <div class="flex gap-md">
                    <button id="sidebarToggle" class="btn btn-ghost btn-sm" style="display: none;">
                        ☰
                    </button>
                    <div class="flex-center gap-md">
                        <span class="text-muted">{{ auth()->user()->name ?? 'Admin' }}</span>
                        <img src="https://via.placeholder.com/40" alt="Profile" class="rounded-full" style="width: 40px; height: 40px; object-fit: cover;">
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            @if ($errors->any())
                <div class="alert alert-danger mb-lg">
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin-left: var(--spacing-lg); margin-top: var(--spacing-sm);">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success mb-lg">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        // Sidebar Toggle for Mobile
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const hamburger = document.getElementById('hamburgerBtn');

        // Show toggle button on mobile
        function updateSidebarToggle() {
            if (window.innerWidth <= 768) {
                sidebarToggle.style.display = 'block';
            } else {
                sidebarToggle.style.display = 'none';
                sidebar.classList.remove('mobile-open');
            }
        }

        updateSidebarToggle();
        window.addEventListener('resize', updateSidebarToggle);

        // Toggle sidebar on mobile
        sidebarToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
        });

        // Close sidebar when clicking on a link
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('mobile-open');
                }
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

        // Drag and Drop support
        function setupDragAndDrop() {
            const draggables = document.querySelectorAll('.draggable');

            draggables.forEach(draggable => {
                draggable.addEventListener('dragstart', () => {
                    draggable.classList.add('dragging');
                });

                draggable.addEventListener('dragend', () => {
                    draggable.classList.remove('dragging');
                    document.querySelectorAll('.drag-over').forEach(el => {
                        el.classList.remove('drag-over');
                    });
                });
            });

            document.querySelectorAll('[data-droppable]').forEach(droppable => {
                droppable.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    droppable.classList.add('drag-over');
                });

                droppable.addEventListener('dragleave', () => {
                    droppable.classList.remove('drag-over');
                });

                droppable.addEventListener('drop', (e) => {
                    e.preventDefault();
                    droppable.classList.remove('drag-over');
                });
            });
        }

        setupDragAndDrop();
    </script>

    @yield('scripts')
</body>
</html>
