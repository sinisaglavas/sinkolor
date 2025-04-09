<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerOutput;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class PrescriptionController extends Controller
{
    public function generatePDF($id)
    {
        Carbon::setLocale('sr'); // da bi meseci bili prikazani na srpskom

        ini_set('max_execution_time', 300); // Povećava vreme izvršenja na 5 minuta

        //session()->put('distance_prescription', true);

        $customerInvoice = CustomerInvoice::findOrFail($id);

        if ($customerInvoice) {
            // Pronađi kupca
            $customer = Customer::findOrFail($customerInvoice->customer_id);
            $customerOutput = CustomerOutput::where('invoice_id', $customerInvoice->id)->get();
            $total_per_invoice = CustomerOutput::where('invoice_id', $customerInvoice->id)->select('sum')->sum('sum');

            // Dinamički odredi osnovni URL
            $baseUrl = App::environment('local')
                ? 'http://127.0.0.1:8000'
                : config('app.url');

            // Pravi puni URL ka ruti
            $qrCodeUrl = $baseUrl . '/generate-prescription/' . $customerInvoice->id;

            // Kreiraj QR kod sa Endroid paketom
            $qrCode = new QrCode($qrCodeUrl);
            $writer = new PngWriter();
            $result = $writer->writeString($qrCode);
            $image = $result; // Ovo je binarni sadržaj QR koda kao PNG slike
            // Ako želiš da prikažeš QR kod u blade-u, koristi base64 encoding
            $imageData = base64_encode($image);

            mb_internal_encoding("UTF-8");
            $pdf = PDF::loadView('pdf.prescription',
                compact('customerInvoice', 'customer', 'customerOutput', 'total_per_invoice', 'imageData'))
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => true,  // PHP PARSER
                    'enable_remote' => true,  // Omogućava slike
                    'chroot' => realpath(base_path('public')), // Apsolutna putanja
                    'defaultFont' => 'dejavu sans',
                    'isRemoteEnabled' => true,
                   // 'debugCss' => true  // Uklonite nakon debug-a
                ]);

            return $pdf->stream("Faktura za {$customer->customer}.pdf");

        }
    }

}
