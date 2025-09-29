<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    {{-- links css tailwind --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- font awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<body>
    <div class="container mx-auto max-w-1/3 sm:px-6 lg:px-8 mt-1">
        <div class="bg-white p-5 rounded shadow mb-5">
            <h1 class="text-xl text-center font-bold mb-4">Login</h1>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-4">
                    <label for="email" class="block text-gray-700">Email:</label>
                    <input type="email" id="email" name="email" class="w-full px-3 py-2 border rounded" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700">Password:</label>
                    <input type="password" id="password" name="password" class="w-full px-3 py-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="remember" class="form-checkbox">
                        <span class="ml-2 text-gray-700">Remember Me</span>
                    </label>
                    <label for="">
                        <a href="{{ route('auth.create') }}" class="text-blue-500 hover:underline">Don't have an account?</a>

                    </label>
                </div>
                {{-- divide --}}
                <div class="border-t my-4"></div>
            {{-- or login with Google and icon awesome --}}
            <div class="my-4 w-full">
                <a href="{{ route('google.login') }}" class="border-2 border-blue-500/70 text-black block w-full px-4 py-2 rounded hover:bg-blue-500 hover:text-white text-center">
                    <i class="fab fa-google"></i> Login with Google
                </a>
            </div>
                <div>
                    <button type="submit" class="bg-blue-500 w-full text-white px-4 py-2 rounded hover:bg-blue-600">Login</button>
                </div>

            </form>
        </div>
    </div>
</body>
</html>
