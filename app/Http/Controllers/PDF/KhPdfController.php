<?php

namespace App\Http\Controllers\PDF;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use KhmerPdf\LaravelKhPdf\Controllers\PdfKh;

class KhPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $html = view('pdf.template-kh', ['title' => 'សួស្តី ពិភពលោក!'])->render();
        return (new PdfKh())->loadHtml($html)->download('khmer_document.pdf');
    }
}
