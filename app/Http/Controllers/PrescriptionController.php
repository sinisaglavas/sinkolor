<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerOutput;
use App\Models\Output;
use App\Models\Stock;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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

    public function generateCodebookPDF()
    {
        Carbon::setLocale('sr');
        ini_set('max_execution_time', 300);

        $all_stocks = Stock::select('code', 'article')->orderBy('code')->get();

        $itemsPerColumn = 80;
        //chunk($itemsPerColumn) deli kolekciju na manje kolekcije (grupe) od po 80 artikala
        //values() resetuje ključeve tih chunkova da budu 0, 1, 2... umesto originalnih ključeva
        $columns = $all_stocks->chunk($itemsPerColumn)->values();

        //Prazan niz u koji ću ubacivati po 3 kolone za svaku stranicu.
        $pages = [];
        //Idem kroz sve kolone u koracima po 3 (jer jedna stranica = 3 kolone)
        for ($i = 0; $i < $columns->count(); $i += 3) {
            //Svaka "stranica" sadrži 3 kolone: i, i+1, i i+2
            //Ako neka od njih ne postoji (npr. ostane 1 ili 2 kolone na kraju),
            //koristi se ?? collect() da se stavi prazna kolekcija — ovo sprečava greške
            $page = [
                $columns->get($i) ?? collect(),
                $columns->get($i + 1) ?? collect(),
                $columns->get($i + 2) ?? collect(),
            ];
            //U $pages ostaju sve stranice sa po 3 kolone
            $pages[] = $page; //Niz svih stranica (svaka stranica ima do 3 kolone)	npr. niz sa 4 elementa (4 stranice)
        }
        //dd($pages);
        mb_internal_encoding("UTF-8");
        $pdf = PDF::loadView('pdf.codeBook',
            compact('pages'))
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

        return $pdf->stream("Šifarnik.pdf");
    }

    public function generateCurrentStockPDF()
    {
        Carbon::setLocale('sr');
        ini_set('max_execution_time', 300);

        $all_stocks = Stock::select('code', 'article', 'unit', 'price', 'pcs')->orderBy('code')->get();
        $date_of_turnover = Output::latest()->first()->date_of_turnover;
        $date_of_turnover = Carbon::parse($date_of_turnover)->translatedFormat('j. F Y.');

        //chunk($itemsPerColumn) deli kolekciju na manje kolekcije (grupe) od po 80 artikala
        $itemsPerColumn = 50;

        $pages = $all_stocks->chunk($itemsPerColumn); // Svaka stranica = 80 artikala
        mb_internal_encoding("UTF-8");
        $pdf = PDF::loadView('pdf.currentStock', compact('pages', 'date_of_turnover'))
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont' => 'dejavu sans',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
            ]);

        return $pdf->stream("Lager.pdf");
    }


}
