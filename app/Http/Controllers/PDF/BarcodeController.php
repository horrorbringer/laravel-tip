<?php

namespace App\Http\Controllers\PDF;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function __invoke(Request $request)
    {
        $html = view('pdf.template-kh', ['title' => 'សួស្តី ពិភពលោក!'])->render();

        return (new \KhmerPdf\LaravelKhPdf\Controllers\PdfKh())
            ->loadHtml($html)
            ->writeBarcode('123456789', 10, 10, true, 1, true)
            ->download('barcode.pdf');
        }
}
