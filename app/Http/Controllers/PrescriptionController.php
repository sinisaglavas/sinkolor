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

        $customerInvoice = CustomerInvoice::findOrFail($id);

        if ($customerInvoice) {
            // Pronađi kupca
            $customer = Customer::findOrFail($customerInvoice->customer_id);
            $customerOutput = CustomerOutput::where('invoice_id', $customerInvoice->id)->get();
            $total_per_invoice = CustomerOutput::where('invoice_id', $customerInvoice->id)->select('sum')->sum('sum');

            // Pravi puni URL ka ruti
            $company_name = 'DOO Sin Kolor';
            $company_address = 'Kralja Petra I 92';
            $company_city = 'Silbaš';
            $company_account = '340000001103190533';
            $amount = number_format($total_per_invoice, 2, ',', ''); //  npr. 12500,00
            $purpose = 'Uplata po računu: 1' . $customerInvoice->invoice_number;
            $call_number = '1'. preg_replace('/[^0-9]/', '', $customerInvoice->invoice_number);

            $qr_content = implode('|', [
                'K:PR',
                'V:01',
                'C:1',
                'R:'.$company_account,
                'N:'.$company_name."\n".$company_address ."\n".$company_city,
                'I:RSD'.$amount,
                'P:'.$customer->customer."\n".$customer->address."\n".$customer->city,
                'SF:221',
                'S:'.$purpose,
                'RO:'.$call_number
            ]);
            // Kreiraj QR kod sa Endroid paketom
            $qrCode = new QrCode($qr_content);
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
