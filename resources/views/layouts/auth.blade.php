<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Masuk ke SOTA-GIS — Sistem pemetaan kerentanan sosial ekonomi Kampung Sota.">
    <title>Masuk — SOTA-GIS</title>

    {{-- Plus Jakarta Sans (PRD 6.3) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    {{-- Favicon Browser --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logosmall.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-paper p-4">

    <div class="w-full max-w-[400px]">
        @yield('content')
    </div>

</body>
</html>
