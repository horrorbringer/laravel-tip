<?php

namespace App\Http\Controllers\PDF;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WatermakController extends Controller
{
    public function __invoke(Request $request)
    {
        $html = view('pdf.template-kh', ['title' => 'សួស្តី ពិភពលោក!'])->render();
        return (new \KhmerPdf\LaravelKhPdf\Controllers\PdfKh())->loadHtml($html)->watermarkText('Confidential', 0.2, 'khmeros', 100, 45, '#FF0000')->download('watermarked.pdf');
    }
}
