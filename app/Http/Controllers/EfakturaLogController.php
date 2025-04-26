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

        // provera da li je već poslata faktura
        $existingLog = EfakturaLog::where('invoice_id', $invoice->id)->first();
        if ($existingLog) {
            return response()->json([
                'message' => 'Ova faktura je već poslata u SEF.',
                'status' => $existingLog->sef_status,
                'response' => $existingLog->sef_response,
            ], 409); // Conflict
        }

        // ako faktura nije poslata nastavak..
        $xml = (new SEFXmlGenerator())->generate($invoice);

        $apiUrl = 'https://demoapi.efaktura.mfin.gov.rs/api/publicApi/invoice/send'; // za produkciju zameni sa produkcijskim URL-om
        $apiKey = config('sef.api_key');

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Accept' => 'application/json',
            'Content-Type' => 'application/xml',
        ])->withBody($xml, 'application/xml')->post($apiUrl);

        EfakturaLog::create([
            'invoice_id' => $invoice->id,
            'sef_status' => $response->successful() ? 'sent' : 'failed',
            'sef_response' => $response->body(),
            'sent_at' => now(),
        ]);

        if ($response->successful()) {
            return back()->with('success', 'Faktura je uspešno poslata u SEF.');
        }

        return back()->with('error', 'Greška prilikom slanja fakture: ' . $response->body());
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
        $query = EfakturaLog::with('invoice')->latest();

        if ($request->filled('status')) {
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
