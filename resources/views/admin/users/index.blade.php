@extends('admin.layouts.app')

@section('title', 'Admin - Users')
@section('page_title', 'Users Management')

@section('content')
<!-- Action Bar -->
<div class="flex-between gap-lg mb-lg flex-wrap">
    <div class="flex gap-md" style="flex: 1;">
        <input type="text" placeholder="Search users by name or email..." class="form-control" style="min-width: 250px;">
        <select class="form-control" style="min-width: 150px;">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="user">User</option>
            <option value="moderator">Moderator</option>
        </select>
    </div>
    <button class="btn btn-primary" onclick="openModal('userModal')">➕ Add User</button>
</div>

<!-- Stats -->
<div class="grid grid-cols-4 grid-gap-lg mb-xl">
    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">👥</div>
            <div style="color: var(--gray); font-size: 0.9rem;">Total Users</div>
            <div style="font-weight: 700; font-size: 1.5rem; color: var(--primary);">{{ rand(500, 2000) }}</div>
        </div>
    </div>

    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">✓</div>
            <div style="color: var(--gray); font-size: 0.9rem;">Active Users</div>
            <div style="font-weight: 700; font-size: 1.5rem; color: var(--success);">{{ rand(300, 1000) }}</div>
        </div>
    </div>

    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">🔐</div>
            <div style="color: var(--gray); font-size: 0.9rem;">Admins</div>
            <div style="font-weight: 700; font-size: 1.5rem; color: var(--warning);">{{ rand(5, 20) }}</div>
        </div>
    </div>

    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">📅</div>
            <div style="color: var(--gray); font-size: 0.9rem;">New This Month</div>
            <div style="font-weight: 700; font-size: 1.5rem; color: var(--primary);">{{ rand(50, 200) }}</div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">All Users</h3>
        <span class="badge badge-primary">{{ rand(500, 2000) }} Users</span>
    </div>

    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 30%;">User</th>
                        <th style="width: 20%;">Email</th>
                        <th style="width: 12%;">Role</th>
                        <th style="width: 12%;">Status</th>
                        <th style="width: 14%;">Joined</th>
                        <th style="width: 12%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 1; $i <= 10; $i++)
                        <tr class="draggable" draggable="true">
                            <td>
                                <div class="flex gap-md" style="align-items: center;">
                                    <img src="https://via.placeholder.com/40" alt="User" class="rounded-full" style="width: 40px; height: 40px; object-fit: cover;">
                                    <div>
                                        <div style="font-weight: 600;">User {{ $i }}</div>
                                        <div style="color: var(--gray); font-size: 0.85rem;">ID: USR-{{ sprintf('%05d', 1000 + $i) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>user{{ $i }}@example.com</td>
                            <td>
                                @php
                                $roles = ['Admin', 'User', 'Moderator', 'User'];
                                @endphp
                                <span class="badge badge-{{ ['primary', 'gray', 'warning', 'gray'][$i % 4] }}">{{ $roles[$i % 4] }}</span>
                            </td>
                            <td>
                                @if($i % 5 != 0)
                                    <span class="badge badge-success">✓ Active</span>
                                @else
                                    <span class="badge badge-danger">⏸️ Inactive</span>
                                @endif
                            </td>
                            <td>
                                {{ now()->subDays(rand(1, 90))->format('M d, Y') }}
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn btn-sm btn-ghost" title="Edit">✎</button>
                                    <button class="btn btn-sm btn-ghost" title="View">👁️</button>
                                    <button class="btn btn-sm btn-ghost" title="Delete">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer">
        <div class="pagination">
            <button class="pagination-item disabled">← Previous</button>
            <button class="pagination-item active">1</button>
            <button class="pagination-item">2</button>
            <button class="pagination-item">3</button>
            <button class="pagination-item">4</button>
            <button class="pagination-item">Next →</button>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div id="userModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">Add New User</h3>
            <button type="button" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" placeholder="John" required>
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" placeholder="Doe" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" placeholder="john@example.com" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" placeholder="+1 (555) 000-0000">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Role *</label>
                    <select required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                        <option value="moderator">Moderator</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Password *</label>
                <input type="password" placeholder="Enter password" required>
            </div>

            <label style="display: flex; align-items: center; gap: var(--spacing-sm); cursor: pointer;">
                <input type="checkbox" checked>
                <span>Send welcome email</span>
            </label>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-ghost" onclick="closeModal('userModal')">Cancel</button>
            <button type="button" class="btn btn-sm btn-primary">Save User</button>
        </div>
    </div>
</div>
@endsection
