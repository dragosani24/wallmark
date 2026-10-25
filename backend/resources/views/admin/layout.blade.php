<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · URL Wall</title>
    <style>
        :root {
            --bg: #f4f2ee;
            --surface: #ffffff;
            --border: #e4e0d8;
            --text: #22201c;
            --muted: #767065;
            --accent: #c8442e;
            --accent-soft: #f7e4e0;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 15px;
        }
        a { color: var(--accent); text-decoration: none; }
        a:hover { text-decoration: underline; }
        header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            display: flex;
            align-items: center;
            gap: 24px;
            height: 56px;
        }
        header .brand { font-weight: 700; letter-spacing: -0.02em; }
        header nav { display: flex; gap: 16px; flex: 1; }
        header nav a { color: var(--muted); font-weight: 500; }
        header nav a:hover { color: var(--text); text-decoration: none; }
        header form button {
            background: none;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 6px 12px;
            color: var(--muted);
            cursor: pointer;
            font: inherit;
        }
        header form button:hover { color: var(--text); border-color: var(--muted); }
        main { max-width: 1040px; margin: 0 auto; padding: 28px 24px 64px; }
        h1 { font-size: 22px; margin: 0 0 4px; letter-spacing: -0.02em; }
        h2 { font-size: 16px; margin: 32px 0 12px; }
        .muted { color: var(--muted); }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin: 20px 0; }
        .stat { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 16px; }
        .stat .value { font-size: 26px; font-weight: 700; }
        .stat .label { color: var(--muted); font-size: 13px; }
        table { width: 100%; border-collapse: collapse; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
        th, td { text-align: left; padding: 10px 14px; border-bottom: 1px solid var(--border); vertical-align: top; }
        th { font-size: 12px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted); }
        tr:last-child td { border-bottom: none; }
        .tag {
            display: inline-block;
            background: var(--accent-soft);
            color: var(--accent);
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 12px;
            margin: 1px 3px 1px 0;
        }
        .btn {
            display: inline-block;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 9px 16px;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }
        .btn:hover { filter: brightness(1.08); text-decoration: none; }
        .btn-ghost { background: none; color: var(--muted); border: 1px solid var(--border); }
        .btn-ghost:hover { color: var(--text); }
        .btn-danger { background: none; color: var(--accent); border: 1px solid var(--border); padding: 4px 10px; font-size: 13px; }
        form.inline { display: inline; }
        input[type="text"], input[type="email"], input[type="password"], textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font: inherit;
            background: var(--surface);
        }
        input:focus, textarea:focus { outline: 2px solid var(--accent-soft); border-color: var(--accent); }
        label { display: block; font-weight: 600; margin: 14px 0 6px; font-size: 14px; }
        .errors { background: var(--accent-soft); color: var(--accent); border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; }
        .errors ul { margin: 0; padding-left: 18px; }
        .status { background: #e5f3e8; color: #23663a; border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; }
        .search { display: flex; gap: 8px; margin: 16px 0; }
        .search input { max-width: 340px; }
        .card-link { display: flex; align-items: center; gap: 8px; }
        .card-link img { width: 20px; height: 20px; border-radius: 4px; }
        .truncate { max-width: 420px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .actions { display: flex; gap: 8px; align-items: center; }
        .pagination { margin-top: 16px; display: flex; gap: 6px; }
        .pagination a, .pagination span {
            padding: 6px 12px; border: 1px solid var(--border); border-radius: 8px;
            background: var(--surface); color: var(--muted);
        }
        .pagination .active { background: var(--accent); color: #fff; border-color: var(--accent); }
    </style>
</head>
<body>
<header>
    <span class="brand">📌 URL Wall</span>
    <nav>
        <a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) style="color: var(--text)" @endif>Dashboard</a>
        <a href="{{ route('admin.links.index') }}" @if(request()->routeIs('admin.links.*')) style="color: var(--text)" @endif>Enlaces</a>
    </nav>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit">Salir ({{ auth()->user()->name }})</button>
    </form>
</header>

<main>
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>
