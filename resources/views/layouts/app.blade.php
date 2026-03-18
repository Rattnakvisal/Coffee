<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/css/index.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f3f1ef] text-[#2f241f]">
    @yield('content')
</body>

</html>
