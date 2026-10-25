<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · URL Wall</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            background: #f4f2ee;
            color: #22201c;
        }
        .box {
            width: 100%;
            max-width: 360px;
            background: #fff;
            border: 1px solid #e4e0d8;
            border-radius: 16px;
            padding: 32px;
        }
        h1 { font-size: 20px; margin: 0 0 4px; }
        p { color: #767065; margin: 0 0 20px; font-size: 14px; }
        label { display: block; font-weight: 600; margin: 14px 0 6px; font-size: 14px; }
        input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e4e0d8;
            border-radius: 10px;
            font: inherit;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            margin-top: 20px;
            background: #c8442e;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 11px;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }
        .errors { background: #f7e4e0; color: #c8442e; border-radius: 10px; padding: 10px 14px; font-size: 14px; }
    </style>
</head>
<body>
    <form method="POST" action="{{ route('admin.login.attempt') }}" class="box">
        @csrf

        <h1>📌 URL Wall</h1>
        <p>Panel de administración</p>

        @if ($errors->any())
            <div class="errors">{{ $errors->first() }}</div>
        @endif

        <label for="email">Correo</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">Entrar</button>
    </form>
</body>
</html>
