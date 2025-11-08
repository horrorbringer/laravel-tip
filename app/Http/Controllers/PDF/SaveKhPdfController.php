<?php

namespace App\Http\Controllers\PDF;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use KhmerPdf\LaravelKhPdf\Controllers\PdfKh;

class SaveKhPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $html = view('pdf.template-kh', ['title' => 'សួស្តី ពិភពលោក!'])->render();
        return (new PdfKh())->loadHtml($html)->save('pdfs/report.pdf', 'public');
    }
}
