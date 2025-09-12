<?php

namespace App\Http\Controllers;

use App\Services\PdfService;

class InvoiceController extends Controller
{
    public function index(PdfService $pdf)
    {
        return $pdf->generate('pdf.invoice', [
            'title' => 'វិក្កយបត្រ',
            'content' => 'នេះជាឯកសារ PDF ជាភាសាខ្មែរ'
        ]);
    }
}
