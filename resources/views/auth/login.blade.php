<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Restaurante POS</title>
    @viteReactRefresh
    @vite(['resources/css/tailwind.css', 'resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 antialiased">

    @php
        $loginProps = [
            "csrfToken" => csrf_token(),
            "loginRoute" => route('login.perform'),
            "oldEmail" => old('email'),
            "errorMessage" => $errors->first('email'),
        ];
    @endphp

    <div
        data-react-component="Login"
        data-react-props='@json($loginProps)'
    ></div>

</body>
</html>