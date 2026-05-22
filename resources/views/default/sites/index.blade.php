@extends('default.layouts.app')

@section('content')

<div style="display:flex; flex-wrap:wrap; gap:10px; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <form method="GET" action="{{ route('sites.index') }}" class="input-group" style="max-width:320px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search sites…" class="form-control">
        <button type="submit" class="btn btn-secondary">Search</button>
    </form>
    @can('create sites')
    <a href="{{ route('sites.create') }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Site
    </a>
    @endcan
</div>

<div class="card" style="padding:0;overflow:hidden;">
    @if($data['items']->isEmpty())
        <div class="empty-state">
            <h3>No sites found</h3>
            <p>Add a site to start monitoring analytics.</p>
        </div>
    @else
    <div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>URL</th>
                <th>API Key</th>
                <th>Added</th>
                <th style="width:130px;"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['items'] as $item)
            <tr id="row-{{ $item->id }}">
                <td style="font-weight:600;">{{ $item->name }}</td>
                <td><a href="{{ $item->url }}" target="_blank" style="color:#3b82f6;text-decoration:none;">{{ $item->url }}</a></td>
                <td><code style="font-size:0.8rem;background:#f8fafc;padding:2px 6px;border-radius:4px;">{{ Str::mask($item->api_key, '*', 6) }}</code></td>
                <td style="color:#64748b;">{{ $item->created_at->format('M d, Y') }}</td>
                <td>
                    <div style="display:flex;gap:6px;">
                        @can('edit sites')
                        <a href="{{ route('sites.edit', $item) }}" class="btn btn-secondary btn-sm">Edit</a>
                        @endcan
                        @can('delete sites')
                        <button class="btn btn-danger btn-sm btn-delete" data-id="{{ $item->id }}" data-url="{{ route('sites.delete', $item) }}">Remove</button>
                        @endcan
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>{{-- /.table-wrap --}}
    @endif
</div>

@if($data['items']->hasPages())
<div style="margin-top:16px;">{{ $data['items']->links() }}</div>
@endif

{{-- Delete confirm modal --}}
<div id="confirmModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:28px;max-width:380px;width:100%;margin:20px;">
        <h3 style="margin:0 0 8px;font-size:1rem;color:#1e293b;">Remove Site?</h3>
        <p style="color:#64748b;font-size:0.875rem;margin:0 0 20px;">This will soft-delete the site. You can restore it from the database if needed.</p>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button id="cancel-delete" class="btn btn-secondary">Cancel</button>
            <button id="confirm-delete-btn" class="btn btn-danger">Remove</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let deleteUrl = null, deleteId = null;
const modal = document.getElementById('confirmModal');

document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', () => {
        deleteUrl = btn.dataset.url;
        deleteId  = btn.dataset.id;
        modal.style.display = 'flex';
    });
});

document.getElementById('cancel-delete').addEventListener('click', () => {
    modal.style.display = 'none';
});

function showAlert(type, message) {
    const el = document.createElement('div');
    el.className = `alert alert-${type}`;
    el.textContent = message;
    document.getElementById('alert-container').prepend(el);
    setTimeout(() => el.remove(), 5000);
}

document.getElementById('confirm-delete-btn').addEventListener('click', async () => {
    modal.style.display = 'none';
    const response = await fetch(deleteUrl, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    });
    const res = await response.json();
    if (res.success) {
        toastr.success(res.message);
        showAlert('success', res.message);
        document.getElementById(`row-${deleteId}`)?.remove();
    } else {
        toastr.error('Something went wrong.');
        showAlert('danger', 'Something went wrong.');
    }
});
</script>
@endpush
