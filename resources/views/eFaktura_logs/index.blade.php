@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">Logovi slanja u SEF</h3>

        <form method="GET" action="{{ route('efaktura.logs') }}" class="mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <label for="status" class="col-form-label">Filtriraj po statusu:</label>
                </div>
                <div class="col-auto">
                    <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- svi --</option>
                        <option value="OK" {{ request('status') === 'OK' ? 'selected' : '' }}>OK</option>
                        <option value="ERROR" {{ request('status') === 'ERROR' ? 'selected' : '' }}>ERROR</option>
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
                <th>SEF Odgovor</th>
                <th>Akcije</th>
            </tr>
            </thead>
            <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->invoice->invoice_number ?? '-' }}</td>
                    <td>{{ $log->sef_status }}</td>
                    <td>{{ $log->sent_at }}</td>
                    <td>
                        <pre style="white-space: pre-wrap; max-width: 400px;">{{ $log->sef_response }}</pre>
                    </td>
                    <td>
                        @if($log->sef_status !== 'OK')
                            <form action="{{ route('efaktura.resend', $log->invoice_id) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-warning" onclick="return confirm('Ponovo poslati fakturu u SEF?')">Ponovno slanje</button>
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

