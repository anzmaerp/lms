<?php

namespace Modules\Courses\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VideoController extends Controller
{

    public function play(Request $request, $path)
    {
        if (
            !Auth::check() || 
            !$request->hasValidSignature() || 
            !Storage::disk(getStorageDisk())->exists("courses/$path") ||
            parse_url($request->headers->get('referer'), PHP_URL_HOST) !== parse_url(config('app.url'), PHP_URL_HOST)
            ) {
            abort(404);
        }   

        $path = Storage::disk(getStorageDisk())->path("courses/$path");

        $stream = new StreamedResponse(function() use ($path) {
            $stream = fopen($path, 'rb');
            fpassthru($stream);
            fclose($stream);
        });

        $stream->headers->set('Content-Type', mime_content_type($path));
        $stream->headers->set('Content-Disposition', 'inline; filename="' . $path . '"');

        return $stream;
        
    }
}
