<?php

namespace App\Traits;

use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use Symfony\Component\HttpFoundation\Response;

trait pdfTrait
{

    public function pdf($view, $data, $Mdata = null, $ops = null, $filename = null): Response
    {
        $pdf = LaravelMpdf::loadView($view, $data, $Mdata ?? [], $ops);
        return $pdf->stream($filename ?? 'document.pdf');
    }

}
