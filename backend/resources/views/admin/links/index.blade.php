@extends('admin.layout')

@section('title', 'Enlaces')

@section('content')
    <h1>Enlaces</h1>

    <form method="GET" action="{{ route('admin.links.index') }}" class="search">
        <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por título o URL…">
        <button type="submit" class="btn btn-ghost">Buscar</button>
        @if ($search !== '')
            <a class="btn btn-ghost" href="{{ route('admin.links.index') }}">Limpiar</a>
        @endif
    </form>

    <table>
        <thead>
            <tr>
                <th>Enlace</th>
                <th>Etiquetas</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($links as $link)
                <tr>
                    <td>
                        <div class="card-link">
                            @if ($link->favicon_url)
                                <img src="{{ $link->favicon_url }}" alt="" onerror="this.remove()">
                            @endif
                            <div>
                                <a href="{{ $link->url }}" target="_blank" rel="noopener">{{ $link->title }}</a>
                                <div class="muted truncate" style="font-size: 12px">{{ $link->url }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @foreach ($link->tags as $tag)
                            <span class="tag">{{ $tag->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        <div class="actions">
                            <a class="btn btn-ghost" href="{{ route('admin.links.edit', $link) }}">Editar</a>
                            <form class="inline" method="POST" action="{{ route('admin.links.destroy', $link) }}"
                                  onsubmit="return confirm('¿Eliminar este enlace?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">No se encontraron enlaces.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $links->links() }}
    </div>
@endsection
