<?php

namespace App\Http\Controllers;

use App\Models\CustomerInvoice;
use App\Services\SEFXmlGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\EfakturaLog;


class EfakturaLogController extends Controller
{
    public function sendToSef($id)
    {
        $invoice = CustomerInvoice::with('customer', 'outputs')->findOrFail($id);
        //dd($invoice);
        // provera da li je već poslata faktura
        $existingLog = EfakturaLog::where('invoice_id', $invoice->id)->first();

        if ($existingLog) {
            return response()->json([
                'message' => 'Ova faktura je vec poslata u SEF.',
                'status' => $existingLog->sef_status,
                'http_status' => $existingLog->http_status,
            ], 409); // Conflict
        }

        // ako faktura nije poslata nastavak..
        $generated = (new SEFXmlGenerator())->generate($invoice);
        $xml = $generated['xml'];
        $documentId = $generated['document_id'];

        // Snimi XML u fajl da vidiš šta se zapravo šalje
        file_put_contents(storage_path('app/debug_invoice.xml'), $xml);

        $apiUrl = 'https://efaktura.mfin.gov.rs/api/publicApi/sales-invoice/ubl'; // produkcija
        $apiKey = config('sef.api_key'); // produkcija

        //$apiUrl = 'https://demoefaktura.mfin.gov.rs/api/publicApi/sales-invoice/ubl'; // demo
        //$apiKey = trim(env('SEF_DEMO_API_KEY')); // demo 68c7533e-95af-4f0d-bd47-81bdc4e9090e

        //$response = Http::withOptions([
           // 'verify' => false,
        //])->withHeaders([
        $response = Http::withHeaders([
            //'Authorization' => "Bearer {$apiKey}", // za produkciju
            'ApiKey' => $apiKey,
            'Accept' => 'application/xml',
            'Content-Type' => 'application/xml',
        ])->withBody($xml, 'application/xml')->post($apiUrl);

        file_put_contents(storage_path('app/sef_error_response.html'), $response->body());


        $newEInvoiceLog = new EfakturaLog();
        $newEInvoiceLog->invoice_id = $invoice->id;
        $newEInvoiceLog->sef_status = $response->successful() ? 'sent' : 'failed';
        $newEInvoiceLog->sef_response = $response->body();
        $newEInvoiceLog->http_status = $response->status(); // 404, 401, 200
        $newEInvoiceLog->sent_at = now();
        $newEInvoiceLog->save();

        if ($response->successful()) {
            $xmlResponseData = $response->body();

            // Ako je response XML (što često jeste), parsiraj ga:
            $xml = simplexml_load_string($xmlResponseData);
            $namespaces = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('ns', $namespaces['']);

            $salesInvoiceId = (string) $xml->xpath('//ns:SalesInvoiceId')[0];
            $purchaseInvoiceId = (string) $xml->xpath('//ns:PurchaseInvoiceId')[0];

            $newEInvoiceLog->sef_sales_invoice_id = $salesInvoiceId;
            $newEInvoiceLog->sef_purchase_invoice_id = $purchaseInvoiceId;
            $newEInvoiceLog->sef_document_id = $documentId; // UUID koji si sam generisao
            $newEInvoiceLog->sef_status = 'sent';
            $newEInvoiceLog->sef_response_raw = $xmlResponseData;
            $newEInvoiceLog->update();

            $data = json_decode(json_encode($xml), true); // json format
            if (!empty($data['SalesInvoiceId']) && $data['SalesInvoiceId'] > 0) {
                return back()->with('success', 'Faktura je uspešno poslata i podaci su sačuvani'); // faktura je uspešno predata
            } else {
                return back()->with('success', 'Faktura nije uspešno poslata u SEF.');// XML nije prošao, iako je status 200
            }
        }

        return back()->with('error', 'Greška prilikom slanja fakture: ' . $response->status());
    }

    public function testXml($id)
    {
        $invoice = CustomerInvoice::with('customer')->findOrFail($id);
        $xmlContent = app(SEFXmlGenerator::class)->generate($invoice);

        // Snimi privremeni XML fajl da ga proverimo
        \Storage::put('debug/test.xml', $xmlContent);

        return response($xmlContent, 200)
            ->header('Content-Type', 'application/xml');
    }

    public function index(Request $request)
    {
        //Dohvati sve logove slanja u SEF, zajedno sa pripadajućim fakturama, i sortiraj ih tako da najnoviji budu prvi
        $query = EfakturaLog::with('invoice')->latest();// latest() - isto kao ->orderBy('created_at', 'desc')

        if ($request->filled('status')) { // filled bilo koji znak osim + je true,  ako je prazno onda je false
            $query->where('sef_status', $request->status);
        }

        $logs = $query->paginate(20);

        return view('efaktura_logs.index', compact('logs'));
    }

    public function resend(CustomerInvoice $invoice)
    {
        EfakturaLog::where('invoice_id', $invoice->id)->delete();

        return $this->sendToSef($invoice->id); // Poziva već postojeću metodu
    }


}
