<?php
require_once __DIR__ . '/../src/App/bootstrap.php';

use App\Session;

$user = Session::user();
if ($user) {
    header('Location: /');
    exit;
}
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ebonia — Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 grid place-items-center">
  <div class="w-full max-w-sm bg-white p-6 rounded-lg shadow border">
    <div class="flex items-center gap-2 mb-4">
      <div class="h-8 w-8 rounded bg-indigo-600"></div>
      <strong class="text-lg">Ebonia</strong>
    </div>
    <h1 class="text-xl font-semibold mb-4">Acceder</h1>
    <form id="login-form" class="space-y-3">
      <div>
        <label class="block text-sm mb-1">Email</label>
        <input id="email" type="email" class="w-full border rounded px-3 py-2" placeholder="tu@empresa.com" required />
      </div>
      <div>
        <label class="block text-sm mb-1">Password</label>
        <input id="password" type="password" class="w-full border rounded px-3 py-2" placeholder="••••••••" required />
      </div>
      <button class="w-full py-2 bg-indigo-600 text-white rounded" id="submit-btn">Entrar</button>
      <p id="error" class="text-sm text-red-600 hidden"></p>
    </form>
  </div>
  <script type="module">
    const form = document.getElementById('login-form');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const errorEl = document.getElementById('error');
    const submitBtn = document.getElementById('submit-btn');

    async function api(path, opts={}){
      const res = await fetch(path, {
        method: opts.method || 'GET',
        headers: { 'Content-Type': 'application/json' },
        body: opts.body ? JSON.stringify(opts.body) : undefined,
        credentials: 'include'
      });
      const data = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(data?.error?.message || res.statusText);
      return data;
    }

    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      errorEl.classList.add('hidden');
      submitBtn.disabled = true;
      submitBtn.textContent = 'Entrando...';
      try {
        await api('/api/auth/login.php', { method: 'POST', body: { email: email.value.trim(), password: password.value } });
        window.location.href = '/';
      } catch(err){
        errorEl.textContent = err.message;
        errorEl.classList.remove('hidden');
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Entrar';
      }
    });
  </script>
</body>
</html>
