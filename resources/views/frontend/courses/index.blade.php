@extends('frontend.layouts.app')

@section('title', 'Courses - MyApp')
@section('description', 'Learn from expert-led courses and advance your skills')

@section('content')
<!-- Page Header -->
<section class="py-2xl" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);">
    <div class="container">
        <h1 style="text-align: center; margin: 0 0 var(--spacing-lg) 0;">Online Courses</h1>
        <p style="text-align: center; max-width: 600px; margin: 0 auto; font-size: 1.1rem;">
            Master new skills with our comprehensive courses taught by industry experts.
        </p>
    </div>
</section>

<!-- Filters -->
<section class="py-lg">
    <div class="container">
        <div class="flex-between gap-lg flex-wrap">
            <h2 style="margin: 0;">Available Courses</h2>
            <div class="flex gap-md flex-wrap">
                <input type="text" placeholder="Search courses..." class="form-control" style="min-width: 200px;">
                <select class="form-control" style="min-width: 150px;">
                    <option value="">All Levels</option>
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Courses Grid -->
<section class="py-lg">
    <div class="container">
        <div class="grid grid-cols-4 grid-gap-lg">
            @for ($i = 1; $i <= 12; $i++)
                <div class="card">
                    <!-- Course Image -->
                    <div style="position: relative; margin-bottom: var(--spacing-md);">
                        <div style="aspect-ratio: 16/9; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                            📚
                        </div>
                        <div style="position: absolute; top: 10px; right: 10px; background: var(--primary); color: white; padding: 4px 8px; border-radius: var(--radius-md); font-size: 0.8rem; font-weight: 600;">
                            @switch($i % 3)
                                @case(0)
                                    Beginner
                                    @break
                                @case(1)
                                    Intermediate
                                    @break
                                @default
                                    Advanced
                            @endswitch
                        </div>
                    </div>

                    <!-- Course Info -->
                    <h4 style="margin: 0 0 var(--spacing-xs) 0;">{{ ['Web Development', 'Data Science', 'Mobile Apps', 'Cloud Computing'][$i % 4] }} Course {{ $i }}</h4>

                    <div style="color: var(--gray); font-size: 0.9rem; margin-bottom: var(--spacing-md);">
                        <div>👨‍🏫 Instructor Name</div>
                        <div>⭐ 4.8 ({{ rand(100, 5000) }} reviews)</div>
                    </div>

                    <!-- Course Stats -->
                    <div style="background: var(--gray-lightest); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-md);">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-sm); font-size: 0.85rem;">
                            <div>
                                <div style="color: var(--gray);">📊 {{ rand(10, 50) }} Lessons</div>
                            </div>
                            <div>
                                <div style="color: var(--gray);">⏱️ {{ rand(10, 100) }}h Video</div>
                            </div>
                            <div>
                                <div style="color: var(--gray);">👥 {{ rand(100, 5000) }} Students</div>
                            </div>
                            <div>
                                <div style="color: var(--gray);">📜 Certificate</div>
                            </div>
                        </div>
                    </div>

                    <!-- Price -->
                    <div style="margin-bottom: var(--spacing-md); text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">$99.99</div>
                        <div style="font-size: 0.85rem; color: var(--gray);">Lifetime access</div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-md" style="flex-direction: column;">
                        <a href="#" class="btn btn-sm btn-outline">Preview</a>
                        <button class="btn btn-sm btn-primary" onclick="openModal('enrollModal')">🎓 Enroll Now</button>
                    </div>
                </div>
            @endfor
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <button class="pagination-item disabled">← Previous</button>
            <button class="pagination-item active">1</button>
            <button class="pagination-item">2</button>
            <button class="pagination-item">3</button>
            <button class="pagination-item">Next →</button>
        </div>
    </div>
</section>

<!-- Enroll Modal -->
<div id="enrollModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Enroll in Course</h3>
            <button type="button" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Select Course</label>
                <select class="form-control">
                    <option>Web Development Course 1</option>
                    <option>Data Science Course 2</option>
                </select>
            </div>
            <div class="form-group">
                <label>Payment Method</label>
                <select class="form-control">
                    <option>Credit Card</option>
                    <option>PayPal</option>
                    <option>Bank Transfer</option>
                </select>
            </div>
            <div style="background: var(--gray-lightest); padding: var(--spacing-md); border-radius: var(--radius-md);">
                <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-sm);">
                    <span>Course Price</span>
                    <span>$99.99</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: 700; border-top: 1px solid var(--gray-lighter); padding-top: var(--spacing-sm);">
                    <span>Total</span>
                    <span>$99.99</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-ghost" onclick="closeModal('enrollModal')">Cancel</button>
            <button type="button" class="btn btn-sm btn-primary">Proceed to Payment</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal(modal.id);
        }
    });

    const closeBtn = modal.querySelector('.modal-close');
    closeBtn?.addEventListener('click', () => {
        closeModal(modal.id);
    });
});
</script>
@endsection
