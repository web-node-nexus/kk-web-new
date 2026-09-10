<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login | KK Digital Solution')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/kk-logo-192.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
<script>
document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-pass-toggle]');
  if (!btn) return;
  const wrap = btn.closest('.kk-pass-wrap');
  const input = wrap?.querySelector('input');
  if (!input) return;
  const show = input.type === 'password';
  input.type = show ? 'text' : 'password';
  const open = btn.querySelector('.kk-eye-open');
  const closed = btn.querySelector('.kk-eye-closed');
  if (open && closed) {
    open.hidden = show;
    closed.hidden = !show;
  }
  btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
});
</script>
<style>
.kk-pass-wrap{position:relative;display:block}
.kk-pass-wrap .glass-input,.kk-pass-wrap input{width:100%;padding-right:48px}
.kk-pass-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:rgba(255,255,255,.7);cursor:pointer;padding:4px;line-height:0}
.kk-pass-toggle:hover{color:#fff}
</style>
</body>
</html>
