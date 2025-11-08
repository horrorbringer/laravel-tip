<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','Tracking Session System')</title>

  {{-- Favicon (optional) --}}
  <link rel="icon" href="{{ asset('images/maple.jpg') }}" type="image/x-icon">

  {{-- Icons --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  {{-- Base styles + your “glass / purple” theme --}}
  <style>
    :root{
      --ink:#0b1324; --muted:#6b7280; --bg:#f6f7fb; --panel:#fff; --line:#e6e9f2;
      --primary:#680e6a; --primary-600:#560c58; --primary-10:rgba(104,14,106,.10);
      --success:#16a34a; --danger:#ef4444; --warning:#d97706;
      --radius:14px; --shadow:0 10px 26px rgba(0,0,0,.06);
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{margin:0;color:var(--ink);background:var(--bg);font:14px/1.5 ui-sans-serif,system-ui,Segoe UI,Roboto,Inter,Arial}
    a{color:inherit;text-decoration:none}
    .container{max-width:1200px;margin:24px auto;padding:0 16px}

    /* Topbar */
    .topbar{position:sticky;top:0;z-index:50;background:#fff;border-bottom:1px solid var(--line)}
    .topbar-inner{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 16px}
    .brand{display:flex;align-items:center;gap:10px;font-weight:700}
    .brand .logo{width:28px;height:28px;border-radius:8px;background:linear-gradient(120deg,var(--primary),var(--primary-600))}
    .breadcrumbs{color:var(--muted);font-size:12px}

    /* Buttons / inputs (used by many pages) */
    .btn{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid var(--line);border-radius:10px;background:#fff;cursor:pointer}
    .btn.primary{background:linear-gradient(120deg,var(--primary),var(--primary-600));color:#fff;border-color:transparent}
    .btn.ghost{background:#fff}
    .input{padding:8px 10px;border:1px solid var(--line);border-radius:10px;background:#fff}
    .table-wrap{overflow:auto}

    /* Panel / card */
    .panel{background:#fff;border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow)}
    .panel .hd{padding:12px 14px;border-bottom:1px solid var(--line)}
    .panel .bd{padding:14px}

    /* Alerts */
    .alert{display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border-radius:12px;border:1px solid var(--line);background:#fff;margin:10px 0}
    .alert.success{background:#ecfdf5;border-color:#a7f3d0}
    .alert.error{background:#fef2f2;border-color:#fecaca}
    .alert.warning{background:#fffbeb;border-color:#fde68a}
    .alert .close{margin-left:auto;cursor:pointer;opacity:.6}

    /* Utility */
    .grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .dim{color:var(--muted);font-size:12px}
  </style>

  @stack('styles')
  @yield('head') {{-- optional per-page head hook --}}
</head>
<body>

  <header class="topbar">
    <div class="topbar-inner container" style="margin:0 auto">
      <div class="brand">
        <div class="logo"></div>
        <div>Tracking Session System</div>
      </div>
      <div class="breadcrumbs">
        @hasSection('title')
          @yield('title')
        @else
          Dashboard
        @endif
      </div>
      <div class="actions" style="display:flex;gap:8px">
        {{-- Example user / logout slot (replace as needed) --}}
        @auth
          <span class="dim">Hi, {{ \Illuminate\Support\Str::of(auth()->user()->name ?? 'User')->limit(18) }}</span>
          <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="btn">Logout</button>
          </form>
        @endauth
      </div>
    </div>
  </header>

  <main class="container">
    {{-- Flash alerts --}}
    @if(session('success'))
      <div class="alert success"><i class="fa fa-check-circle"></i><div>{{ session('success') }}</div><span class="close" onclick="this.parentElement.remove()">✕</span></div>
    @endif
    @if(session('error'))
      <div class="alert error"><i class="fa fa-circle-exclamation"></i><div>{{ session('error') }}</div><span class="close" onclick="this.parentElement.remove()">✕</span></div>
    @endif
    @if(session('warning'))
      <div class="alert warning"><i class="fa fa-triangle-exclamation"></i><div>{{ session('warning') }}</div><span class="close" onclick="this.parentElement.remove()">✕</span></div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
      <div class="alert error">
        <i class="fa fa-circle-exclamation"></i>
        <div>
          <b>There were some problems:</b>
          <ul style="margin:6px 0 0 16px">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        <span class="close" onclick="this.parentElement.remove()">✕</span>
      </div>
    @endif

    @yield('content')
  </main>

  {{-- Minimal JS helpers --}}
  <script>
    // auto-hide alerts after 6s
    setTimeout(() => document.querySelectorAll('.alert').forEach(a => a.remove()), 6000);
  </script>

  @stack('scripts')
  @yield('foot') {{-- optional per-page foot hook --}}
</body>
</html>
