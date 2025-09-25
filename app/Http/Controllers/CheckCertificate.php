<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Upcertify\Models\Certificate;

class CheckCertificate extends Controller
{
    public function index(Request $request)
    {
        $certificate = null;

        if ($request->filled('certificate_number')) {
            $data = $request->validate([
                'certificate_number' => ['required', 'string', 'max:255'],
            ]);

            $certificate = Certificate::where('hash_id', $data['certificate_number'])->first();
        }

        return view('check_certificate', compact('certificate'));
    }
    
    public function checkAr(Request $request)
    {
        $certificate = null;

        if ($request->filled('certificate_number')) {
            $data = $request->validate([
                'certificate_number' => ['required', 'string', 'max:255'],
            ]);

            $certificate = Certificate::where('hash_id', $data['certificate_number'])->first();
        }

        return view('checkCertificate-ar', compact('certificate'));
    }
    public function checkEn(Request $request)
    {
        $certificate = null;

        if ($request->filled('certificate_number')) {
            $data = $request->validate([
                'certificate_number' => ['required', 'string', 'max:255'],
            ]);

            $certificate = Certificate::where('hash_id', $data['certificate_number'])->first();
        }

        return view('checkCertificate-en', compact('certificate'));
    }
}
