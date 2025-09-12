<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Document</title>
    {{-- links css tailwind --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">


</head>

<body class="bg-gray-200 container mx-auto sm:px-6 lg:px-8">
    <nav class="bg-white p-5 rounded shadow mb-5">
        <ul class="flex space-x-4">
            <li><a href="#" class="text-blue-500 hover:underline">Home</a></li>
            <li> <a class="text-blue-500 hover:underline" href="{{url('invoice-pdf')}}">download pdf khmer</a> </li>
            <li><a href="{{ route('images.index') }}" class="text-blue-500 hover:underline">Upload Images</a></li>
            <li class="ml-auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-red-500 hover:underline rounded-md cursor-pointer bg-red-50 px-3.5 py-2.5 text-sm font-semibold text-red-600 shadow-xs hover:bg-red-100">Logout</button>
                </form>
            </li>
        </ul>
    </nav>
    {{-- main content --}}
    <main class="bg-white p-5 rounded shadow">
        <h1 class="text-2xl font-bold mb-4"> @yield('title_content', 'Welcome to the Dashboard') </h1>
        @yield('content')
    </main>

</body>
</html>
