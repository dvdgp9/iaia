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
<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 grid place-items-center p-4">
  <div class="w-full max-w-md">
    <div class="bg-white p-8 rounded-2xl shadow-xl border border-slate-200">
      <div class="flex items-center gap-3 mb-8">
        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-lg">E</div>
        <div>
          <strong class="text-2xl font-bold text-slate-800">Ebonia</strong>
          <div class="text-sm text-slate-500">IA Corporativa</div>
        </div>
      </div>
      <h1 class="text-2xl font-bold mb-2 text-slate-800">Iniciar sesión</h1>
      <p class="text-slate-500 mb-6 text-sm">Accede a tu espacio de trabajo</p>
      <form id="login-form" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
          <input id="email" type="email" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-200 transition-all" placeholder="tu@empresa.com" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Contraseña</label>
          <input id="password" type="password" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-200 transition-all" placeholder="••••••••" required />
        </div>
        <button class="w-full py-3 bg-gradient-to-r from-violet-600 to-indigo-600 text-white rounded-xl font-semibold shadow-md hover:shadow-lg hover:from-violet-700 hover:to-indigo-700 transition-all duration-200" id="submit-btn">Entrar</button>
        <p id="error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-2 hidden"></p>
      </form>
    </div>
    <p class="text-center text-slate-500 text-sm mt-6">
      © 2025 Grupo Ebone. Todos los derechos reservados.
    </p>
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
