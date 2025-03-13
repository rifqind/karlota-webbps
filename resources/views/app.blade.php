<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Karlota') }}</title>
    <meta name="description" content="{{ $page['description'] ?? 'Karlota - Aplikasi Rekonsiliasi BPS se-Sulawesi Utara' }}">
    <meta name="keywords" content="karlota,rekon pdrb sulawesi utara,pdrb, rekonsiliasi,bps provinsi sulawesi utara">
    <meta name="author" content="BPS Provinsi Sulawesi Utara">
    <meta name="language" content="id-ID">
    <meta name="application-name" content="Karlota">
    <!-- <meta name="google-site-verification" content="14ze682NdRlAj4rweYKqsuSWUSY-s5AsMBsvdepxDWg" /> -->
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ url('') }}/images/karlota-logo.png" />
    <!-- Scripts -->
    @routes(nonce: Vite::cspNonce())
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>