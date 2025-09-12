<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    {{-- links css tailwind --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <div class="p-5 rounded shadow mb-5 shadow-black/10">
            <h1 class="text-xl font-bold mb-4">Register</h1>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-gray-700">Name:</label>
                    <input type="text" id="name" name="name" class="w-full px-3 py-2 border rounded border-gray-300 focus:outline-[3px] focus:outline-slate-700/20" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700">Email:</label>
                    <input type="email" id="email" name="email" class="w-full px-3 py-2 border rounded border-gray-300 focus:outline-[3px] focus:outline-slate-700/20" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700">Password:</label>
                    <input type="password" id="password" name="password" class="w-full px-3 py-2 border rounded border-gray-300 focus:outline-[3px] focus:outline-slate-700/20" required>
                </div>
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-gray-700">Confirm Password:</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="w-full px-3 py-2 border rounded border-gray-300 focus:outline-[3px] focus:outline-slate-700/20" required>
                </div>
                <div class="mb-4">
                    <a href="{{ route('login') }}" class="text-blue-500 hover:underline
                        ">Already have an account? Login</a>
                </div>
                <div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Register</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
