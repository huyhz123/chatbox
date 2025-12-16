<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Completion</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
        }
        .certificate {
            background: white;
            padding: 60px;
            text-align: center;
            border: 15px solid #667eea;
            border-radius: 10px;
        }
        .certificate h1 {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 30px;
            text-transform: uppercase;
        }
        .certificate h2 {
            font-size: 24px;
            margin: 20px 0;
        }
        .recipient-name {
            font-size: 36px;
            color: #764ba2;
            margin: 30px 0;
            font-weight: bold;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            display: inline-block;
        }
        .course-name {
            font-size: 28px;
            color: #667eea;
            margin: 30px 0;
            font-style: italic;
        }
        .completion-date {
            margin-top: 40px;
            font-size: 16px;
            color: #666;
        }
        .signature-section {
            margin-top: 60px;
            display: table;
            width: 100%;
        }
        .signature {
            display: table-cell;
            width: 50%;
            padding-top: 30px;
            border-top: 2px solid #333;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <h1>Certificate of Completion</h1>

        <h2>This is to certify that</h2>

        <div class="recipient-name">{{ $enrollment->user->name }}</div>

        <h2>has successfully completed the course</h2>

        <div class="course-name">"{{ $enrollment->course->name }}"</div>

        <p>With {{ $enrollment->progress }}% completion rate</p>

        <div class="completion-date">
            Completed on: {{ $enrollment->updated_at->format('F d, Y') }}<br>
            Certificate ID: CERT-{{ str_pad($enrollment->id, 6, '0', STR_PAD_LEFT) }}
        </div>

        <div class="signature-section">
            <div class="signature">
                <strong>{{ config('app.name') }}</strong><br>
                Authorized Signature
            </div>
        </div>
    </div>
</body>
</html>
