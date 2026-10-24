<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 18px; font-family: DejaVu Sans, sans-serif; color: #172554; }
        .card { width: 100%; height: 100%; border: 2px solid #1d4ed8; border-radius: 12px; padding: 12px; text-align: center; }
        .school { margin: 0 0 8px; font-size: 11px; text-transform: uppercase; color: #1d4ed8; }
        .photo { width: 64px; height: 64px; margin: 0 auto 7px; border-radius: 50%; border: 2px solid #93c5fd; object-fit: cover; }
        .name { margin: 0 0 5px; font-size: 14px; font-weight: bold; }
        .class { margin: 0; font-size: 11px; color: #475569; }
    </style>
</head>
<body>
    <main class="card">
        <p class="school">{{ $studentIdCard->school?->name ?? config('app.name') }}</p>
        @if ($photoDataUri)
            <img class="photo" src="{{ $photoDataUri }}" alt="Student photo">
        @endif
        <p class="name">{{ $studentIdCard->student_name }}</p>
        <p class="class">Class: {{ $studentIdCard->class?->name ?? '—' }}</p>
    </main>
</body>
</html>
