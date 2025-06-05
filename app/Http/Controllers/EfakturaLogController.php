<?php

namespace App\Http\Controllers;

use App\Models\CustomerInvoice;
use App\Services\SEFXmlGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\EfakturaLog;
use GuzzleHttp\Client;
use Illuminate\Support\Str;


class EfakturaLogController extends Controller
{
    // Helper funkcija zamene URL-ova u svim metodama
    public function getSefBaseUrl()
    {
        return env('SEF_DEMO') ? 'https://demoefaktura.mfin.gov.rs' : 'https://efaktura.mfin.gov.rs';
    }


    public function sendToSef($id)
    {
        $invoice = CustomerInvoice::with('customer', 'outputs')->findOrFail($id);

        // Provera da li već postoji log
        $existingLog = EfakturaLog::where('invoice_id', $invoice->id)->first();
        if ($existingLog) {
            return response()->json([
                'message' => 'Ova faktura je već poslata u SEF.',
                'status' => $existingLog->sef_status,
                'http_status' => $existingLog->http_status,
            ], 409);
        }

        // Generiši XML
        $generated = (new \App\Services\SEFXmlGenerator())->generate($invoice);
        $xml = $generated['xml'];
        $documentId = $generated['document_id'];

        // Snimi XML lokalno
        $timestamp = now()->format('Ymd_His');
        file_put_contents(storage_path("app/xml_sent_{$timestamp}.xml"), $xml);

        // SEF API
        $apiKey = config('sef.api_key');
        $requestId = (string) Str::uuid();
        $apiUrl = $this->getSefBaseUrl() . '/api/publicApi/sales-invoice/ubl';
        //dd($apiKey, $apiUrl);

        // Slanje putem Guzzle
        $client = new Client();
        try {
            $response = $client->request('POST', $apiUrl, [
                'headers' => [
                    'ApiKey' => $apiKey,
                    'Accept' => 'application/xml',
                    'Content-Type' => 'application/xml',
                ],
                'query' => [
                    'sendToCir' => 'true',
                    'requestId' => $requestId,
                ],
                'body' => $xml,
            ]);

            $body = $response->getBody()->getContents();
            $status = $response->getStatusCode();
        } catch (\Exception $e) {
            return back()->with('error', 'Greška prilikom povezivanja sa SEF: ' . $e->getMessage());
            //return back()->with('error', 'URL koji je poslat:' . );
        }

        // Snimi odgovor
        file_put_contents(storage_path("app/sef_response_{$timestamp}.xml"), $body);

        // Loguj u bazu
        $log = new EfakturaLog();
        $log->invoice_id = $invoice->id;
        $log->sef_document_id = $documentId;
        $log->sef_status = $status === 200 ? 'sent' : 'failed';
        $log->sef_response = $body;
        $log->http_status = $status;
        $log->sent_at = now();
        $log->save();

        if ($status === 200) {
            $xmlResponse = simplexml_load_string($body);
            $namespaces = $xmlResponse->getNamespaces(true);
            $xmlResponse->registerXPathNamespace('ns', $namespaces[''] ?? '');

            $invoiceId = (string) ($xmlResponse->xpath('//ns:InvoiceId')[0] ?? '');
            $salesInvoiceId = (string) ($xmlResponse->xpath('//ns:SalesInvoiceId')[0] ?? '');
            $purchaseInvoiceId = (string) ($xmlResponse->xpath('//ns:PurchaseInvoiceId')[0] ?? '');

            if ($salesInvoiceId) {
                $log->sef_invoice_id = $invoiceId;
                $log->sef_sales_invoice_id = $salesInvoiceId;
                $log->sef_purchase_invoice_id = $purchaseInvoiceId;
                $log->update();

                return back()->with('success', 'Faktura je uspešno poslata i registrovana u SEF-u.');
            }

            return back()->with('warning', 'Faktura je poslata, ali odgovor nije sadržavao identifikatore.');
        }

        return back()->with('error', "Greška u SEF odgovoru: HTTP {$status}");
    }


    public function getSefInvoiceStatus($sefInvoiceId)
    {
        $apiKey = config('sef.api_key');
        //$sefInvoiceId = 7631745;
        //dd($sefInvoiceId);
        $apiUrl = $this->getSefBaseUrl() . "/api/publicApi/sales-invoice/{$sefInvoiceId}";

        try {
            $response = Http::withHeaders([
                'ApiKey' => $apiKey,
                'Accept' => 'application/json',
            ])->get($apiUrl);

            if ($response->successful()) {
                $data = $response->json();
                // Primer polja: "Status": "Sent", "InvoiceId": 7631745, ...
                return $data['Status'] ?? null;
            } else {
                return null; // Ili exception
            }
        } catch (\Exception $e) {
            // Možeš logovati ili vratiti grešku
            return null;
        }
    }

    // attemptCancelInvoice($id) proverava status u SEF-u (GET /sales-invoice/{invoiceId})
    // na osnovu statusa poziva: sendCancelToSef() ako je status Послато/Одбијено
    //                           sendCreditNoteToSef() ako je status Прихваћено (Accepted)
    public function attemptCancelInvoice($id)
    {
        $invoice = CustomerInvoice::with('customer', 'outputs')->findOrFail($id);
        $log = EfakturaLog::where('invoice_id', $invoice->id)->first();

        if (!$log || !$log->sef_sales_invoice_id) {
            return back()->with('error', 'Faktura nije pronađena u SEF evidenciji.');
        }

        $status = $this->getSefInvoiceStatus($log->sef_invoice_id);
        dd($status);

        if (!$status) {
            return back()->with('error', 'Nije moguće dohvatiti status iz SEF-a.');
        }

        if (in_array($status, ['Sent', 'Rejected', 'Approved'])) {
            return $this->sendCancelToSef($id); // tvoja metoda
        }

        if ($status === 'Accepted') {
            return $this->sendCreditNoteToSef($id); // UBL storno
        }

        return back()->with('warning', "Faktura ima status '$status' — nije moguće stornirati automatski.");
    }



    public function sendCreditNoteToSef($id)
    {
        $invoice = CustomerInvoice::with('customer', 'outputs')->findOrFail($id);
        $generated = (new \App\Services\SEFXmlGenerator())->generateCreditNote($invoice);

        $xml = $generated['xml'];
        $documentId = $generated['document_id'];

        // Snimi XML za pregled
        $timestamp = now()->format('Ymd_His');
        file_put_contents(storage_path("app/xml_creditnote_{$timestamp}.xml"), $xml);

        $apiKey = config('sef.api_key');
        $requestId = (string) \Str::uuid();

        $query = http_build_query([
            'sendToCir' => !empty($invoice->customer->jbkjs) ? 'true' : 'false',
            'requestId' => $requestId,
        ]);

        $url = $this->getSefBaseUrl() .'/api/publicApi/sales-invoice/ubl?' . $query;

        try {
            $response = Http::withHeaders([
                'ApiKey' => $apiKey,
                'Accept' => 'application/xml',
                'Content-Type' => 'application/xml',
            ])->withBody($xml, 'application/xml')->post($url);

            $body = $response->body();
            $status = $response->status();

            file_put_contents(storage_path("app/sef_response_creditnote_{$timestamp}.xml"), $body);

            if ($status === 200) {
                // Parsiranje ako želiš da sačuvaš response
                $xmlResponse = simplexml_load_string($body);
                $namespaces = $xmlResponse->getNamespaces(true);
                $xmlResponse->registerXPathNamespace('ns', $namespaces['']);

                $salesInvoiceId = (string) $xmlResponse->xpath('//ns:SalesInvoiceId')[0] ?? null;

                // Snimi log
                $log = new EfakturaLog();
                $log->invoice_id = $invoice->id;
                $log->sef_status = 'storno_sent';
                $log->sef_sales_invoice_id = $salesInvoiceId;
                $log->sef_document_id = $documentId;
                $log->sef_response = $body;
                $log->http_status = $status;
                $log->sent_at = now();
                $log->save();

                return back()->with('success', "Storno faktura uspešno poslata u SEF.");
            } else {
                return back()->with('error', "Greška prilikom slanja storno fakture: HTTP $status\n$body");
            }
        } catch (\Exception $e) {
            return back()->with('error', "Greška u komunikaciji sa SEF: " . $e->getMessage());
        }
    }


    public function sendCancelToSef($id)
    {
        $invoice = CustomerInvoice::with('customer', 'outputs')->findOrFail($id);

        // Provera da li već postoji log
        $existingLog = EfakturaLog::where('invoice_id', $invoice->id)->first();

        if ($existingLog) {
           if ($existingLog->sef_sales_invoice_id && $existingLog->http_status == 200){
               // Generiši XML
               $generated = (new \App\Services\SEFXmlGenerator())->generateCreditNote($invoice);
               $xml = $generated['xml'];
               $documentId = $generated['document_id'];

               // Snimi XML lokalno
               $timestamp = now()->format('Ymd_His');
               file_put_contents(storage_path("app/xml_cancel_{$timestamp}.xml"), $xml);

               // SEF API
               $apiKey = config('sef.api_key');
              // $requestId = (string) Str::uuid();
               $apiUrl = $this->getSefBaseUrl() . '/api/publicApi/sales-invoice/storno';

               // Slanje putem Guzzle
               $client = new Client();
               try {
                   $response = $client->request('POST', $apiUrl, [
                       'headers' => [
                           'ApiKey' => $apiKey,
                           'Accept' => 'application/xml',
                           'Content-Type' => 'application/json',
                       ],
                 //        'query' => [
                 //          'sendToCir' => 'true',
                 //          'requestId' => $requestId,
                 //     ],
                       'body' => $xml,
                   ]);

                   $body = $response->getBody()->getContents();
                   $status = $response->getStatusCode();
               } catch (\Exception $e) {
                   return back()->with('error', 'Greška prilikom povezivanja sa SEF: ' . $e->getMessage());
               }

               // Snimi odgovor
               file_put_contents(storage_path("app/sef_response_cancel_{$timestamp}.xml"), $body);

               if ($status === 200)
               {
                   $delete_log = EfakturaLog::where('invoice_id',$invoice->id)->first();
                   $delete_log->delete();

                   return back()->with('success', "Faktura je uspešno stornirana u SEF-u: HTTP {$status}");
               }
           }
        }
        return back()->with('warning', 'Faktura nije pronađena u bazi uspešno poslatih!');
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
