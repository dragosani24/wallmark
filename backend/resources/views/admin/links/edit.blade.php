@extends('admin.layout')

@section('title', 'Editar enlace')

@section('content')
    <h1>Editar enlace</h1>
    <p class="muted">
        <a href="{{ $link->url }}" target="_blank" rel="noopener">{{ $link->url }}</a>
    </p>

    <form method="POST" action="{{ route('admin.links.update', $link) }}" style="max-width: 560px">
        @csrf
        @method('PATCH')

        <label for="title">Título</label>
        <input id="title" type="text" name="title" value="{{ old('title', $link->title) }}" required>

        <label for="description">Descripción</label>
        <textarea id="description" name="description" rows="3">{{ old('description', $link->description) }}</textarea>

        <label>Etiquetas (separadas por coma)</label>
        <input type="text" name="tags" value="{{ old('tags', $link->tags->pluck('name')->implode(', ')) }}"
               placeholder="ej. css, frontend">

        <div style="margin-top: 20px; display: flex; gap: 10px">
            <button type="submit" class="btn">Guardar</button>
            <a class="btn btn-ghost" href="{{ route('admin.links.index') }}">Cancelar</a>
        </div>
    </form>

    @if ($link->image_url)
        <h2>Vista previa</h2>
        <img src="{{ $link->image_url }}" alt="" style="max-width: 320px; border-radius: 12px; border: 1px solid var(--border)"
             onerror="this.remove()">
    @endif
@endsection
