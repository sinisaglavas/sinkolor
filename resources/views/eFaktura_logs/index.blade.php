@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mb-3">
            <div class="col-3">
                <a href="{{ url('/home') }}" class="btn btn-secondary form-control">Glavni meni</a>
            </div>
            <div class="col-3">
                <a href="{{ route('home.allCustomerInvoices') }}" class="btn btn-danger form-control">Sve fakture kupaca</a>
            </div>
        </div>
        <h3 class="mb-4">Logovi slanja u SEF</h3>
        <form method="GET" action="{{ route('efakturaLogs') }}" class="mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <label for="status" class="col-form-label">Filtriraj po statusu:</label>
                </div>
                <div class="col-auto">
                    <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- svi --</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>OK</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>NEUSPELO</option>
                    </select>
                </div>
            </div>
        </form>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Faktura</th>
                <th>Status</th>
                <th>Poslato</th>
                <th>SalesInvoiceId</th>
                <th>PurchaseInvoiceId</th>
                <th>DocumentId</th>
                <th>SEF Odgovor</th>
                <th>Akcije</th>
            </tr>
            </thead>
            <tbody>
            @foreach($logs as $log)
                <tr>
                    <td><pre>{{ $log->invoice->invoice_number ?? '-' }}</pre></td>
                    <td>
                        @if($log->sef_status == 'failed')
                            <div>❌</div>
                            <pre>{{ $log->sef_status }}</pre>
                            <pre>http:<br>{{ $log->http_status }}</pre>
                        @elseif($log->sef_status == 'sent')
                            <div>✅</div>
                            <pre>{{ $log->sef_status }}</pre>
                            <pre>http:<br>{{ $log->http_status }}</pre>
                        @endif
                    </td>
                    <td><pre>{{ $log->sent_at }}</pre></td>
                    <td><pre>{{ $log->sef_sales_invoice_id }}</pre></td>
                    <td><pre>{{ $log->sef_purchase_invoice_id }}</pre></td>
                    <td><pre>{{ $log->sef_document_id }}</pre></td>
                    <td>
                        <pre style="white-space: pre-wrap; max-width: 600px;">{{ $log->sef_response }}</pre>
                    </td>
                    <td class="text-center">
                        @if($log->sef_status == 'failed')
                            <form action="{{ route('efaktura.resend', $log->invoice_id) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-warning" onclick="return confirm('Ponovo poslati fakturu u SEF?')">Pokušaj ponovo</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $logs->links() }}
    </div>
@endsection

