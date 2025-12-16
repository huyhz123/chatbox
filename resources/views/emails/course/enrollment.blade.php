@extends('emails.layout')

@section('title', 'Course Enrollment Confirmation - ' . config('app.name'))

@section('content')
    <h2>Course Enrollment Confirmed! 📚</h2>

    <p>Hi {{ $enrollment->user->name }},</p>

    <p>Congratulations! You've successfully enrolled in the course:</p>

    <div class="info-box">
        <h3 style="margin: 0 0 10px 0; color: #667eea;">{{ $enrollment->course->name }}</h3>
        <p style="margin: 5px 0;">
            <strong>Instructor:</strong> {{ $enrollment->course->instructor ?? 'Professional Team' }}<br>
            <strong>Level:</strong> {{ ucfirst($enrollment->course->level) }}<br>
            <strong>Duration:</strong> {{ $enrollment->course->duration }} hours<br>
            <strong>Enrolled:</strong> {{ $enrollment->created_at->format('F d, Y') }}
        </p>
    </div>

    <p>You can now start learning at your own pace. Access all lessons, materials, and resources anytime.</p>

    <h3>What's Next?</h3>

    <div style="margin: 20px 0;">
        <p style="margin: 10px 0;">1️⃣ Start with Lesson 1</p>
        <p style="margin: 10px 0;">2️⃣ Complete lessons in order</p>
        <p style="margin: 10px 0;">3️⃣ Track your progress</p>
        <p style="margin: 10px 0;">4️⃣ Earn your certificate upon completion</p>
    </div>

    <p style="text-align: center; margin-top: 30px;">
        <a href="{{ route('courses.learn', $enrollment->course->id) }}" class="button">
            Start Learning Now
        </a>
    </p>

    <p>Good luck with your learning journey!</p>

    <p>Best regards,<br>The {{ config('app.name') }} Team</p>
@endsection
