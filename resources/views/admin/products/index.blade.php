@extends('admin.layouts.app')

@section('title', 'Admin - Products')
@section('page_title', 'Products Management')

@section('content')
<!-- Action Bar -->
<div class="flex-between gap-lg mb-lg flex-wrap">
    <div class="flex gap-md" style="flex: 1;">
        <input type="text" placeholder="Search products..." class="form-control" style="min-width: 200px;">
        <select class="form-control" style="min-width: 150px;">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
        </select>
    </div>
    <button class="btn btn-primary" onclick="openModal('productModal')">➕ Add Product</button>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">All Products</h3>
        <span class="badge badge-primary">{{ rand(50, 200) }} Products</span>
    </div>

    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 35%;">Product</th>
                        <th style="width: 15%;">SKU</th>
                        <th style="width: 12%;">Price</th>
                        <th style="width: 12%;">Stock</th>
                        <th style="width: 12%;">Status</th>
                        <th style="width: 14%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 1; $i <= 10; $i++)
                        <tr class="draggable" draggable="true">
                            <td>
                                <div class="flex gap-md" style="align-items: center;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                                        📦
                                    </div>
                                    <div>
                                        <strong>Product {{ $i }}</strong>
                                        <div style="color: var(--gray); font-size: 0.85rem;">Premium Product</div>
                                    </div>
                                </div>
                            </td>
                            <td>PRD-{{ sprintf('%05d', 1000 + $i) }}</td>
                            <td>
                                <strong>${{ number_format(99.99 + ($i * 10), 2) }}</strong>
                            </td>
                            <td>
                                <div>
                                    @php
                                    $stock = rand(0, 100);
                                    $color = 'success';
                                    if ($stock < 10) $color = 'danger';
                                    elseif ($stock < 25) $color = 'warning';
                                    @endphp
                                    <span class="badge badge-{{ $color }}">{{ $stock }} Units</span>
                                </div>
                            </td>
                            <td>
                                @if($i % 3 == 0)
                                    <span class="badge badge-success">✓ Active</span>
                                @elseif($i % 3 == 1)
                                    <span class="badge badge-warning">⏸️ Draft</span>
                                @else
                                    <span class="badge badge-danger">🗑️ Archived</span>
                                @endif
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
            <button class="pagination-item">Next →</button>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">Add New Product</h3>
            <button type="button" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" placeholder="Enter product name" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>SKU *</label>
                    <input type="text" placeholder="PRD-00001" required>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select required>
                        <option value="">Select Category</option>
                        <option value="electronics">Electronics</option>
                        <option value="clothing">Clothing</option>
                        <option value="books">Books</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Price *</label>
                    <input type="number" placeholder="99.99" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Stock *</label>
                    <input type="number" placeholder="0" required>
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea placeholder="Enter product description"></textarea>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select>
                    <option value="active">Active</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-ghost" onclick="closeModal('productModal')">Cancel</button>
            <button type="button" class="btn btn-sm btn-primary">Save Product</button>
        </div>
    </div>
</div>
@endsection
