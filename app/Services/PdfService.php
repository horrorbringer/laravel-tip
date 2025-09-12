<?php

namespace App\Services;

use Mpdf\Mpdf;

class PdfService
{
    public function generate(string $view, array $data = [])
    {
        $html = view($view, $data)->render();

        $mpdf = new Mpdf([
            'default_font' => 'hanuman',
            'mode' => 'utf-8',
            'format' => 'A4',
            'fontdata' => [
                'hanuman' => [
                    'R' => storage_path('fonts/Hanuman-Regular.ttf'),
                    'B' => storage_path('fonts/Hanuman-Bold.ttf'),
                ],
                // 'battambang' => [
                //     'R' => storage_path('fonts/Battambang-Regular.ttf'),
                //     'B' => storage_path('fonts/Battambang-Bold.ttf'),
                // ],
                // 'siemreap' => [
                //     'R' => storage_path('fonts/Siemreap-Regular.ttf'),
                // ],
            ]
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('', 'I'); // 'I' = Inline in browser
    }
}
