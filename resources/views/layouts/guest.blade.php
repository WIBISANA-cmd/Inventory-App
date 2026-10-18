<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="stylesheet" href="{{ asset('admin/assets/css/bootstrap.min.css') }}">
    </head>
    <body class="bg-light">
        <div class="container d-flex flex-column justify-content-center align-items-center min-vh-100 py-4">
            <div class="card shadow-sm p-4 w-100" style="max-width: 480px;">
                {{ $slot }}
            </div>
        </div>
        <script src="{{ asset('admin/assets/js/core/bootstrap.min.js') }}"></script>
    </body>
</html>

