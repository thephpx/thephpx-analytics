@extends('default.layouts.app')

@section('content')

<div class="card" style="max-width:580px;">
    <h2 style="margin:0 0 20px;font-size:1.1rem;font-weight:700;color:#1e293b;">Edit Site</h2>

    <form method="POST" action="{{ route('sites.edit', $data['item']) }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="name">Site Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $data['item']->name) }}"
                   class="form-control @error('name') border-red-500 @enderror"
                   placeholder="My Blog" required>
            @error('name')<p style="color:#dc2626;font-size:0.8rem;margin-top:4px;">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="url">Site URL</label>
            <input type="url" id="url" name="url" value="{{ old('url', $data['item']->url) }}"
                   class="form-control @error('url') border-red-500 @enderror"
                   placeholder="https://example.com" required>
            @error('url')<p style="color:#dc2626;font-size:0.8rem;margin-top:4px;">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="api_key">Matrix Bearer Token</label>
            <input type="text" id="api_key" name="api_key" value="{{ old('api_key', $data['item']->api_key) }}"
                   class="form-control @error('api_key') border-red-500 @enderror"
                   placeholder="The MATRIX_BEARER_TOKEN from your site's .env" required>
            @error('api_key')<p style="color:#dc2626;font-size:0.8rem;margin-top:4px;">{{ $message }}</p>@enderror
        </div>

        <div style="display:flex;gap:10px;margin-top:8px;">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('sites.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection
