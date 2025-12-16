@extends('admin.layouts.app')

@section('title', 'Admin - Services')
@section('page_title', 'Services Management')

@section('content')
<!-- Action Bar -->
<div class="flex-between gap-lg mb-lg flex-wrap">
    <div class="flex gap-md" style="flex: 1;">
        <input type="text" placeholder="Search services..." class="form-control" style="min-width: 200px;">
        <select class="form-control" style="min-width: 150px;">
            <option value="">All Categories</option>
            <option value="web">Web Development</option>
            <option value="mobile">Mobile Apps</option>
            <option value="cloud">Cloud Solutions</option>
        </select>
    </div>
    <button class="btn btn-primary" onclick="openModal('serviceModal')">➕ Add Service</button>
</div>

<!-- Services Table -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">All Services</h3>
        <span class="badge badge-primary">{{ rand(10, 50) }} Services</span>
    </div>

    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 1; $i <= 10; $i++)
                        <tr class="draggable" draggable="true">
                            <td>
                                <strong>Service {{ $i }}</strong>
                            </td>
                            <td>
                                @switch($i % 4)
                                    @case(0)
                                        Web Development
                                        @break
                                    @case(1)
                                        Mobile Apps
                                        @break
                                    @case(2)
                                        Cloud Solutions
                                        @break
                                    @default
                                        Consulting
                                @endswitch
                            </td>
                            <td>${{ number_format(999 + ($i * 100), 2) }}</td>
                            <td>
                                @if($i % 3 == 0)
                                    <span class="badge badge-success">✓ Active</span>
                                @else
                                    <span class="badge badge-warning">⏸️ Draft</span>
                                @endif
                            </td>
                            <td>{{ now()->subDays(rand(1, 60))->format('M d, Y') }}</td>
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
            <button class="pagination-item">Next →</button>
        </div>
    </div>
</div>

<!-- Service Modal -->
<div id="serviceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Service</h3>
            <button type="button" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Service Name *</label>
                <input type="text" placeholder="Enter service name" required>
            </div>

            <div class="form-group">
                <label>Category *</label>
                <select required>
                    <option value="">Select Category</option>
                    <option value="web">Web Development</option>
                    <option value="mobile">Mobile Apps</option>
                    <option value="cloud">Cloud Solutions</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Price *</label>
                    <input type="number" placeholder="999.99" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Status *</label>
                    <select required>
                        <option value="active">Active</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea placeholder="Enter service description"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-ghost" onclick="closeModal('serviceModal')">Cancel</button>
            <button type="button" class="btn btn-sm btn-primary">Save Service</button>
        </div>
    </div>
</div>
@endsection
