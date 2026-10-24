<?php

namespace App\Http\Controllers;

use App\Models\StudentIdCard;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentIdCardController extends Controller
{
    public function download(StudentIdCard $studentIdCard): Response
    {
        abort_unless(in_array(auth()->user()?->user_type, ['super_admin', 'school_admin', 'branch_admin'], true), 403);

        $studentIdCard->load('class');
        $photoDataUri = null;
        $disk = Storage::disk('public');

        if ($disk->exists($studentIdCard->photo)) {
            $mimeType = $disk->mimeType($studentIdCard->photo);

            if (($mimeType === 'image/jpeg') || function_exists('imagecreatefromstring')) {
                $photoDataUri = 'data:'.$mimeType.';base64,'.base64_encode($disk->get($studentIdCard->photo));
            }
        }

        $fileName = Str::slug($studentIdCard->student_name.'-id-card').'.pdf';

        return Pdf::loadView('pdf.student-id-card', [
            'studentIdCard' => $studentIdCard,
            'photoDataUri' => $photoDataUri,
        ])
            ->setPaper([0, 0, 242.65, 153.07], 'landscape')
            ->download($fileName);
    }
}
