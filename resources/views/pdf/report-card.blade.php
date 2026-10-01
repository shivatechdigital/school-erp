<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Card</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
        }

        /* Header */
        .header {
            text-align: center;
            border-bottom: 3px double #1a56db;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .school-name {
            font-size: 22px;
            font-weight: bold;
            color: #1a56db;
            text-transform: uppercase;
        }
        .school-address {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #dc2626;
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .exam-name {
            font-size: 13px;
            color: #555;
            margin-top: 3px;
        }

        /* Student Info */
        .student-info {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 5px 10px;
            border-bottom: 1px solid #eee;
            width: 25%;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            font-size: 10px;
            text-transform: uppercase;
        }
        .info-value {
            font-weight: bold;
            color: #111;
        }

        /* Marks Table */
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .marks-table th {
            background: #1a56db;
            color: white;
            padding: 8px 6px;
            text-align: center;
            font-size: 11px;
            text-transform: uppercase;
        }
        .marks-table td {
            padding: 7px 6px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        .marks-table tr:nth-child(even) {
            background: #f8fafc;
        }
        .marks-table .subject-name {
            text-align: left;
            font-weight: 500;
        }
        .marks-table .total-row {
            background: #e0e7ff !important;
            font-weight: bold;
            border-top: 2px solid #1a56db;
        }
        .pass { color: #16a34a; font-weight: bold; }
        .fail { color: #dc2626; font-weight: bold; }

        /* Result Box */
        .result-box {
            display: table;
            width: 100%;
            border: 2px solid #1a56db;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .result-cell {
            display: table-cell;
            padding: 10px;
            text-align: center;
            width: 25%;
            border-right: 1px solid #ddd;
        }
        .result-cell:last-child { border-right: none; }
        .result-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        .result-value {
            font-size: 18px;
            font-weight: bold;
            margin-top: 3px;
        }

        /* Footer */
        .footer {
            display: table;
            width: 100%;
            margin-top: 40px;
        }
        .sign-cell {
            display: table-cell;
            width: 33%;
            text-align: center;
        }
        .sign-line {
            border-top: 1px solid #333;
            width: 120px;
            margin: 0 auto 5px;
        }
        .sign-label {
            font-size: 10px;
            color: #555;
        }

        .watermark {
            position: fixed;
            top: 40%;
            left: 30%;
            font-size: 60px;
            color: rgba(0,0,0,0.04);
            transform: rotate(-30deg);
            font-weight: bold;
        }

        .qr-note {
            text-align: center;
            font-size: 9px;
            color: #999;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <div class="watermark">{{ $school->name ?? 'SCHOOL' }}</div>

    {{-- HEADER --}}
    <div class="header">
        <div class="school-name">{{ $school->name ?? 'School Name' }}</div>
        <div class="school-address">
            {{ $school->address ?? '' }}, {{ $school->city ?? '' }} - {{ $school->pincode ?? '' }}
            | Phone: {{ $school->phone ?? '' }}
        </div>
        <div class="report-title">📜 Report Card</div>
        <div class="exam-name">{{ $exam->name }} | Academic Year: {{ $exam->academicYear->name ?? '' }}</div>
    </div>

    {{-- STUDENT INFO --}}
    <div class="student-info">
        <div class="info-row">
            <div class="info-cell">
                <div class="info-label">Student Name</div>
                <div class="info-value">{{ $student->full_name }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Admission No.</div>
                <div class="info-value">{{ $student->admission_no }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Class</div>
                <div class="info-value">{{ $student->class->name ?? '' }} - {{ $student->section->name ?? '' }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Roll No.</div>
                <div class="info-value">{{ $student->roll_no ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="info-row">
            <div class="info-cell">
                <div class="info-label">Date of Birth</div>
                <div class="info-value">{{ $student->date_of_birth?->format('d/m/Y') }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Gender</div>
                <div class="info-value">{{ ucfirst($student->gender ?? '') }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Father's Name</div>
                <div class="info-value">{{ $student->guardians->first()?->name ?? 'N/A' }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Attendance</div>
                <div class="info-value">— %</div>
            </div>
        </div>
    </div>

    {{-- MARKS TABLE --}}
    <table class="marks-table">
        <thead>
            <tr>
                <th style="width:5%">S.No</th>
                <th style="width:25%">Subject</th>
                <th style="width:12%">Max Marks</th>
                <th style="width:12%">Theory</th>
                <th style="width:12%">Practical</th>
                <th style="width:12%">Total</th>
                <th style="width:8%">%</th>
                <th style="width:8%">Grade</th>
                <th style="width:8%">Result</th>
            </tr>
        </thead>
        <tbody>
            @forelse($marks as $index => $mark)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="subject-name">{{ $mark->subject->name ?? 'N/A' }}</td>
                    <td>{{ number_format((float) $mark->max_marks, 0) }}</td>
                    <td>{{ number_format((float) ($mark->theory_marks ?? 0), 0) }}</td>
                    <td>{{ number_format((float) ($mark->practical_marks ?? 0), 0) }}</td>
                    <td><strong>{{ number_format((float) ($mark->total_marks ?? 0), 0) }}</strong></td>
                    <td>{{ number_format((float) ($mark->percentage ?? 0), 1) }}%</td>
                    <td><strong>{{ $mark->grade ?? '—' }}</strong></td>
                    <td class="{{ $mark->result === 'pass' ? 'pass' : 'fail' }}">
                        {{ strtoupper($mark->result ?? 'N/A') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:20px; color:#999;">
                        No marks available for this exam.
                    </td>
                </tr>
            @endforelse

            {{-- TOTAL ROW --}}
            @if($marks->count() > 0)
                <tr class="total-row">
                    <td colspan="2" style="text-align:right; padding-right:10px;">GRAND TOTAL</td>
                    <td>{{ number_format($totalMax, 0) }}</td>
                    <td>{{ number_format((float) $marks->sum('theory_marks'), 0) }}</td>
                    <td>{{ number_format((float) $marks->sum('practical_marks'), 0) }}</td>
                    <td><strong>{{ number_format($totalObtained, 0) }}</strong></td>
                    <td><strong>{{ $overallPercentage }}%</strong></td>
                    <td><strong>{{ $overallGrade }}</strong></td>
                    <td class="{{ $overallResult === 'PASS' ? 'pass' : 'fail' }}">
                        <strong>{{ $overallResult }}</strong>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- RESULT BOX --}}
    <div class="result-box">
        <div class="result-cell">
            <div class="result-label">Overall Result</div>
            <div class="result-value {{ $overallResult === 'PASS' ? 'pass' : 'fail' }}">
                {{ $overallResult }}
            </div>
        </div>
        <div class="result-cell">
            <div class="result-label">Percentage</div>
            <div class="result-value" style="color:#1a56db;">
                {{ $overallPercentage }}%
            </div>
        </div>
        <div class="result-cell">
            <div class="result-label">Grade</div>
            <div class="result-value" style="color:#7c3aed;">
                {{ $overallGrade }}
            </div>
        </div>
        <div class="result-cell">
            <div class="result-label">Class Rank</div>
            <div class="result-value" style="color:#d97706;">
                {{ $rank }}
            </div>
        </div>
    </div>

    {{-- REMARKS --}}
    <div style="margin-bottom:15px; padding:8px; border:1px solid #ddd; border-radius:4px;">
        <strong>Class Teacher's Remark:</strong>
        <span style="color:#666;"> _______________________________</span>
    </div>

    {{-- SIGNATURES --}}
    <div class="footer">
        <div class="sign-cell">
            <div class="sign-line"></div>
            <div class="sign-label">Class Teacher</div>
        </div>
        <div class="sign-cell">
            <div class="sign-line"></div>
            <div class="sign-label">Examination Incharge</div>
        </div>
        <div class="sign-cell">
            <div class="sign-line"></div>
            <div class="sign-label">Principal</div>
        </div>
    </div>

    <div class="qr-note">
        This is a computer-generated report card. | Generated on {{ now()->format('d/m/Y h:i A') }}
    </div>

</body>
</html>
