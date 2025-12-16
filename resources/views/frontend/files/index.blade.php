@extends('frontend.layouts.app')

@section('title', 'Files - MyApp')
@section('description', 'Manage and download your files')

@section('content')
<!-- Page Header -->
<section class="py-2xl" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);">
    <div class="container">
        <div class="flex-between gap-lg">
            <div>
                <h1 style="margin: 0 0 var(--spacing-md) 0;">My Files</h1>
                <p style="margin: 0; color: var(--gray);">Total files: 24 | Used: 2.5 GB / 10 GB</p>
            </div>
            <button class="btn btn-primary btn-lg">📤 Upload File</button>
        </div>
    </div>
</section>

<!-- File Browser -->
<section class="py-lg">
    <div class="container">
        <!-- Breadcrumb -->
        <div style="margin-bottom: var(--spacing-lg); display: flex; gap: var(--spacing-md); align-items: center; color: var(--gray); font-size: 0.95rem;">
            <a href="#" style="color: var(--primary);">📁 My Drive</a>
            <span>/</span>
            <span>Documents</span>
        </div>

        <!-- Files List -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Name</th>
                        <th style="width: 15%;">Size</th>
                        <th style="width: 15%;">Modified</th>
                        <th style="width: 15%;">Type</th>
                        <th style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 1; $i <= 8; $i++)
                        <tr>
                            <td>
                                <div class="flex gap-md" style="align-items: center;">
                                    @if($i % 3 == 0)
                                        <span style="font-size: 1.3rem;">📁</span>
                                        <div>
                                            <div style="font-weight: 600;">Folder {{ $i }}</div>
                                            <div style="color: var(--gray); font-size: 0.85rem;">12 items</div>
                                        </div>
                                    @else
                                        <span style="font-size: 1.3rem;">📄</span>
                                        <div>
                                            <div style="font-weight: 600;">Document_{{ $i }}.pdf</div>
                                            <div style="color: var(--gray); font-size: 0.85rem;">PDF Document</div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($i % 3 == 0)
                                    -
                                @else
                                    {{ rand(100, 5000) }} KB
                                @endif
                            </td>
                            <td>
                                <span style="color: var(--gray);">{{ now()->subDays(rand(0, 30))->format('M d, Y') }}</span>
                            </td>
                            <td>
                                @if($i % 3 == 0)
                                    <span class="badge badge-primary">Folder</span>
                                @else
                                    <span class="badge badge-success">PDF</span>
                                @endif
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn btn-sm btn-ghost" title="Download">⬇️</button>
                                    <button class="btn btn-sm btn-ghost" title="Share">🔗</button>
                                    <button class="btn btn-sm btn-ghost" title="Delete">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <button class="pagination-item disabled">← Previous</button>
            <button class="pagination-item active">1</button>
            <button class="pagination-item">2</button>
            <button class="pagination-item">Next →</button>
        </div>
    </div>
</section>

<!-- Storage Info Card -->
<section class="py-lg" style="background: var(--gray-lightest);">
    <div class="container-sm">
        <div class="card">
            <h3>Storage Information</h3>
            <div class="mt-lg">
                <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-md);">
                    <span>Storage Used</span>
                    <span style="font-weight: 600;">2.5 GB / 10 GB</span>
                </div>
                <div style="height: 8px; background: var(--gray-lighter); border-radius: var(--radius-full); overflow: hidden;">
                    <div style="height: 100%; width: 25%; background: var(--gradient-primary);"></div>
                </div>
            </div>

            <div class="grid grid-cols-3 grid-gap-lg mt-lg">
                <div style="text-align: center; padding: var(--spacing-lg); background: rgba(99, 102, 241, 0.1); border-radius: var(--radius-lg);">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">📄</div>
                    <div style="font-size: 0.9rem; color: var(--gray);">Documents</div>
                    <div style="font-weight: 700; font-size: 1.1rem;">1.2 GB</div>
                </div>

                <div style="text-align: center; padding: var(--spacing-lg); background: rgba(139, 92, 246, 0.1); border-radius: var(--radius-lg);">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">🖼️</div>
                    <div style="font-size: 0.9rem; color: var(--gray);">Images</div>
                    <div style="font-weight: 700; font-size: 1.1rem;">900 MB</div>
                </div>

                <div style="text-align: center; padding: var(--spacing-lg); background: rgba(236, 72, 153, 0.1); border-radius: var(--radius-lg);">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">🎥</div>
                    <div style="font-size: 0.9rem; color: var(--gray);">Videos</div>
                    <div style="font-weight: 700; font-size: 1.1rem;">400 MB</div>
                </div>
            </div>

            <button class="btn btn-block btn-outline mt-lg">🚀 Upgrade Storage Plan</button>
        </div>
    </div>
</section>
@endsection
