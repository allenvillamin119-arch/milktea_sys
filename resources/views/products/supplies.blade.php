@extends('layouts.main')

@section('title', 'Manage Supplies')
@section('page-title', 'Manage Supplies for ' . $product->name)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Supplies used by: {{ $product->name }}</h5>
        <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary">Back to Product</a>
    </div>
    <div class="card-body">
        <form id="supplies-form" action="{{ route('products.supplies.update', $product) }}" method="POST">
            @csrf

            <div class="d-flex gap-2 mb-3">
                <input id="supply-search" class="form-control" placeholder="Search supplies by name..." value="{{ old('search', $search ?? '') }}">
                <a href="{{ route('products.supplies.export', $product) }}" class="btn btn-outline-secondary">Export CSV</a>
                <form id="import-form" action="{{ route('products.supplies.import', $product) }}" method="POST" enctype="multipart/form-data" style="display:inline-block">
                    @csrf
                    <label class="btn btn-outline-primary mb-0">
                        Import CSV <input id="import-file" type="file" name="file" accept=".csv" hidden>
                    </label>
                </form>
            </div>

            <div id="supplies-alert"></div>

            <div id="supplies-table" class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Supply</th>
                            <th style="width: 160px">Qty per unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supplies as $s)
                            @php
                                $pivot = $product->supplies->firstWhere('id', $s->id);
                                $qty = $pivot ? $pivot->pivot->quantity : 0;
                            @endphp
                            <tr>
                                <td>{{ $s->name }}</td>
                                <td>
                                    <input type="hidden" name="supplies[{{ $loop->index }}][id]" value="{{ $s->id }}">
                                    <input type="number" min="0" class="form-control form-control-sm" name="supplies[{{ $loop->index }}][quantity]" value="{{ old('supplies.' . $loop->index . '.quantity', $qty) }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <div>
                    {{ $supplies->links() }}
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary">Cancel</a>
                    <button id="save-supplies" type="submit" class="btn btn-primary">Save Supplies</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// AJAX save
document.getElementById('supplies-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('save-supplies');
    btn.disabled = true;

    const rows = Array.from(document.querySelectorAll('table tbody tr'));
    const supplies = rows.map((row, idx) => {
        const idInput = row.querySelector('input[type="hidden"]');
        const qtyInput = row.querySelector('input[type="number"]');
        return {
            id: idInput ? idInput.value : null,
            quantity: qtyInput ? qtyInput.value : 0,
        };
    }).filter(s => s.id !== null);

    const payload = { supplies };

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch(this.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    }).then(r => r.json())
    .then(data => {
        btn.disabled = false;
        const alert = document.getElementById('supplies-alert');
        if (data.success) {
            alert.innerHTML = '<div class="alert alert-success">' + (data.message || 'Saved') + '</div>';
        } else {
            alert.innerHTML = '<div class="alert alert-danger">Error saving</div>';
        }
        setTimeout(() => alert.innerHTML = '', 3000);
    }).catch(err => {
        btn.disabled = false;
        document.getElementById('supplies-alert').innerHTML = '<div class="alert alert-danger">Error saving supplies</div>';
    });
});

// Client-side quick filter
document.getElementById('supply-search').addEventListener('input', function(e) {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('table tbody tr').forEach(function(row) {
        const name = row.querySelector('td').textContent.toLowerCase();
        row.style.display = name.includes(q) ? '' : 'none';
    });
});

// Handle pagination links via AJAX
document.addEventListener('click', function(e) {
    if (e.target.closest('.pagination a')) {
        e.preventDefault();
        const url = e.target.closest('a').href;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                // parse returned HTML and replace table
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTable = doc.querySelector('#supplies-table');
                if (newTable) {
                    document.getElementById('supplies-table').innerHTML = newTable.innerHTML;
                }
                const newLinks = doc.querySelector('.pagination');
                if (newLinks) {
                    document.querySelector('.pagination').innerHTML = newLinks.innerHTML;
                }
            });
    }
});

// Import CSV via form submission (non-AJAX simple submit)
document.getElementById('import-file').addEventListener('change', function() {
    document.getElementById('import-form').submit();
});
</script>
@endpush
