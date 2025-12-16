@extends('admin.layouts.app')

@section('title', 'Admin - Courses')
@section('page_title', 'Courses Management')

@section('content')
<!-- Action Bar -->
<div class="flex-between gap-lg mb-lg flex-wrap">
    <div class="flex gap-md" style="flex: 1;">
        <input type="text" placeholder="Search courses..." class="form-control" style="min-width: 200px;">
        <select class="form-control" style="min-width: 150px;">
            <option value="">All Levels</option>
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
        </select>
    </div>
    <button class="btn btn-primary" onclick="openModal('courseModal')">➕ Add Course</button>
</div>

<!-- Stats -->
<div class="grid grid-cols-4 grid-gap-lg mb-xl">
    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">📚</div>
            <div style="color: var(--gray); font-size: 0.9rem;">Total Courses</div>
            <div style="font-weight: 700; font-size: 1.5rem; color: var(--primary);">{{ rand(20, 50) }}</div>
        </div>
    </div>

    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">👥</div>
            <div style="color: var(--gray); font-size: 0.9rem;">Total Enrollments</div>
            <div style="font-weight: 700; font-size: 1.5rem; color: var(--success);">{{ rand(500, 2000) }}</div>
        </div>
    </div>

    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">⭐</div>
            <div style="color: var(--gray); font-size: 0.9rem;">Average Rating</div>
            <div style="font-weight: 700; font-size: 1.5rem; color: var(--warning);">4.8/5.0</div>
        </div>
    </div>

    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">💰</div>
            <div style="color: var(--gray); font-size: 0.9rem;">Revenue</div>
            <div style="font-weight: 700; font-size: 1.5rem; color: var(--primary);">${{ number_format(rand(50000, 200000), 0) }}</div>
        </div>
    </div>
</div>

<!-- Courses Table -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">All Courses</h3>
        <span class="badge badge-primary">{{ rand(20, 50) }} Courses</span>
    </div>

    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 30%;">Course Title</th>
                        <th style="width: 15%;">Instructor</th>
                        <th style="width: 12%;">Enrolled</th>
                        <th style="width: 12%;">Price</th>
                        <th style="width: 12%;">Rating</th>
                        <th style="width: 12%;">Status</th>
                        <th style="width: 7%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 1; $i <= 10; $i++)
                        <tr class="draggable" draggable="true">
                            <td>
                                <div>
                                    <strong>{{ ['Web Development', 'Data Science', 'Mobile Apps', 'Cloud Computing'][$i % 4] }} Course {{ $i }}</strong>
                                    <div style="color: var(--gray); font-size: 0.85rem;">24 lessons • {{ rand(10, 100) }}h</div>
                                </div>
                            </td>
                            <td>Instructor {{ $i }}</td>
                            <td>
                                <span style="font-weight: 600;">{{ rand(50, 500) }}</span>
                            </td>
                            <td>
                                <strong>${{ number_format(49.99 + ($i * 10), 2) }}</strong>
                            </td>
                            <td>
                                <div style="display: flex; gap: 2px; align-items: center;">
                                    @for ($j = 0; $j < 5; $j++)
                                        <span style="color: #fbbf24; font-size: 0.9rem;">⭐</span>
                                    @endfor
                                </div>
                            </td>
                            <td>
                                @if($i % 3 == 0)
                                    <span class="badge badge-success">✓ Published</span>
                                @else
                                    <span class="badge badge-warning">⏸️ Draft</span>
                                @endif
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn btn-sm btn-ghost" title="Edit">✎</button>
                                    <button class="btn btn-sm btn-ghost" title="View">👁️</button>
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
            <button class="pagination-item">Next →</button>
        </div>
    </div>
</div>

<!-- Course Modal -->
<div id="courseModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">Add New Course</h3>
            <button type="button" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Course Title *</label>
                <input type="text" placeholder="Enter course title" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Instructor *</label>
                    <select required>
                        <option value="">Select Instructor</option>
                        <option value="instr1">Instructor 1</option>
                        <option value="instr2">Instructor 2</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Level *</label>
                    <select required>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Price *</label>
                    <input type="number" placeholder="49.99" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Duration (hours) *</label>
                    <input type="number" placeholder="24" required>
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea placeholder="Enter course description"></textarea>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-ghost" onclick="closeModal('courseModal')">Cancel</button>
            <button type="button" class="btn btn-sm btn-primary">Save Course</button>
        </div>
    </div>
</div>
@endsection
