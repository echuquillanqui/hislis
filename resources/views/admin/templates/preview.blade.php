@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h3 class="fw-bold text-primary">Vista previa: {{ $template->name }}</h3><span class="badge bg-{{ $template->publication_status === 'published' ? 'success' : 'secondary' }}">{{ $template->publication_status }}</span></div>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('templates.publish', $template) }}">@csrf<button class="btn btn-success">Publicar</button></form>
            <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>
    <div class="card shadow-sm"><div class="card-body">
        @forelse($schema as $section)
            <h5 class="border-bottom pb-2 mt-2">{{ $section['title'] ?? 'General' }}</h5>
            <div class="row g-3 mb-3">
                @foreach(($section['fields'] ?? []) as $field)
                    <div class="col-md-{{ max(1, min(12, (int)($field['column'] ?? 12))) }}">
                        <label class="form-label fw-bold">{{ $field['label'] }} @if($field['required'] ?? false)<span class="text-danger">*</span>@endif</label>
                        @if(($field['type'] ?? 'text') === 'textarea')<textarea class="form-control" rows="3"></textarea>
                        @elseif(($field['type'] ?? 'text') === 'select')<select class="form-select"><option>Seleccione...</option>@foreach(($field['options'] ?? []) as $option)<option>{{ $option['label'] }}</option>@endforeach</select>
                        @elseif(($field['type'] ?? 'text') === 'radio' || ($field['type'] ?? 'text') === 'checkbox')@foreach(($field['options'] ?? []) as $option)<div class="form-check"><input class="form-check-input" type="{{ $field['type'] }}"><label class="form-check-label">{{ $option['label'] }}</label></div>@endforeach
                        @else<input type="{{ in_array(($field['type'] ?? 'text'), ['number','date']) ? $field['type'] : 'text' }}" class="form-control">@endif
                        @foreach(($field['rules'] ?? []) as $rule)<small class="text-muted d-block">Regla: {{ $rule['action'] }} si {{ $rule['source_field_slug'] }} {{ $rule['operator'] }} {{ $rule['comparison_value'] }}</small>@endforeach
                    </div>
                @endforeach
            </div>
        @empty<p class="text-muted">No hay campos configurados.</p>@endforelse
    </div></div>
</div>
@endsection
