<!doctype html>
<html lang="en">
<head>
    <title>:: Iconic :: Home</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Iconic Bootstrap 4.5.0 Admin Template">
    <meta name="author" content="WrapTheme, design by: ThemeMakker.com">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite('resources/js/app.js')
</head>
<body data-theme="light" class="font-nunito">
    <div id="wrapper" class="theme-cyan">
        @inertia
    </div>
</body>
</html>
