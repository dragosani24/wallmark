@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <h1>Dashboard</h1>
    <p class="muted">Estado general del muro de enlaces.</p>

    <div class="stats">
        <div class="stat">
            <div class="value">{{ $linkCount }}</div>
            <div class="label">Enlaces guardados</div>
        </div>
        <div class="stat">
            <div class="value">{{ $tagCount }}</div>
            <div class="label">Etiquetas</div>
        </div>
        <div class="stat">
            <div class="value">{{ $imagelessCount }}</div>
            <div class="label">Sin imagen</div>
        </div>
    </div>

    <h2>Etiquetas más usadas</h2>
    @forelse ($topTags as $tag)
        <span class="tag">{{ $tag->name }} · {{ $tag->links_count }}</span>
    @empty
        <p class="muted">Aún no hay etiquetas.</p>
    @endforelse

    <h2>Últimos enlaces</h2>
    <table>
        <thead>
            <tr>
                <th>Título</th>
                <th>Etiquetas</th>
                <th>Guardado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($latestLinks as $link)
                <tr>
                    <td>
                        <div class="card-link">
                            @if ($link->favicon_url)
                                <img src="{{ $link->favicon_url }}" alt="" onerror="this.remove()">
                            @endif
                            <a href="{{ $link->url }}" target="_blank" rel="noopener" class="truncate">{{ $link->title }}</a>
                        </div>
                    </td>
                    <td>
                        @foreach ($link->tags as $tag)
                            <span class="tag">{{ $tag->name }}</span>
                        @endforeach
                    </td>
                    <td class="muted">{{ $link->created_at->diffForHumans(short: true) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">No hay enlaces todavía.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
