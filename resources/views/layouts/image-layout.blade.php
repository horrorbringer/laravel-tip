<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title_content', 'Image Upload')</title>

    {{-- CSRF for fetch --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Tailwind CDN (simple for this page) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Font Awesome for icons --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="min-h-screen bg-slate-100">
    <div class="max-w-5xl mx-auto py-10">
        @yield('content')
    </div>
</body>
</html>
