<?php

namespace Modules\Upcertify\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\View;
use Modules\Upcertify\Models\Certificate;

class CertificateController extends Controller
{
    public function __invoke(Request $request)
    {
        $id = $request->id ?? null;
        $extends = 'upcertify::layouts.app';
        $component = 'create-certificate';
        $props = ['record_id' => $id];
        $load_livewire_scripts = true;
        $load_livewire_styles = true;
        $load_jquery = true;
        return view('upcertify::index', compact('props', 'extends', 'component', 'load_livewire_scripts', 'load_livewire_styles', 'load_jquery'));
    }

    public function certificateList()
    {
        $extends = !empty(config('upcertify.layout')) ?  config('upcertify.layout') : 'upcertify::layouts.app';
        $component = 'certificate-list';
        $load_livewire_scripts = config('upcertify.livewire_scripts') ?? false;
        $load_livewire_styles = config('upcertify.livewire_styles') ?? false;
        $load_jquery = config('upcertify.add_jquery') ?? false;
        return view('upcertify::index', compact('extends', 'component', 'load_livewire_scripts', 'load_livewire_styles', 'load_jquery'));
    }

    public function viewCertificate(Request $request, $uid)
    {

        $certificate = Certificate::whereHashId($uid)->with('template:id,body')->first();
        if (empty($certificate)) {
            return abort(404);
        }

        $wildcard_data = $certificate->wildcard_data;
        $template_body = $certificate->template->body;

        foreach ($template_body['elementsInfo'] as $key => &$element) {
            $wildcardName = $element['wildcardName'];
            if (array_key_exists($wildcardName, $wildcard_data)) {
                $element['wildcardName'] =  $wildcard_data[$wildcardName];
            }
        }

        $body = $template_body;

        return view('upcertify::front-end.certificate', compact('body', 'uid'));
    }

    public function takeCertificateShort(Request $request, $uid)
    {
        $certificate = Certificate::whereHashId($uid)->with('template:id,body')->first();

        if (empty($certificate)) {
            return abort(404);
        }
        $wildcard_data = $certificate->wildcard_data;
        $template_body = $certificate->template->body;
        if (!empty($template_body['elementsInfo'])) {
            foreach ($template_body['elementsInfo'] as $key => &$element) {
                $wildcardName = $element['wildcardName'];
                if (array_key_exists($wildcardName, $wildcard_data)) {
                    $element['wildcardName'] =  $wildcard_data[$wildcardName];
                }
            }
        }


        $body = $template_body;
        $page = 'template';

        return view('upcertify::front-end.download', compact('body'));
    }


public function downloadCertificate($uid)
{
    $certificate = Certificate::whereHashId($uid)
        ->with('template:id,body')
        ->firstOrFail();

    $body = $certificate->body;

    $pdf = Pdf::loadView('upcertify::front-end.pdf', [
        'body' => $body,
    ])->setPaper('a4', 'landscape');

    return $pdf->download("certificate-{$uid}.pdf");
}


public function getCertificate($uid)
{
    $certificate = Certificate::whereHashId($uid)
        ->with('template:id,title,body')
        ->firstOrFail();

    $body = $certificate->template->body; // or $certificate->body depending on your schema

    $pdf = Pdf::loadView('upcertify::front-end.pdf', [
        'body' => $body,
        'uid'  => $uid,
    ])->setPaper('a4', 'landscape');

    return $pdf->download("certificate-{$uid}.pdf");
}

}
