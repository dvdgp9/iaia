<?php ?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ebonia — MVP</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <div class="min-h-screen grid grid-cols-12">
    <aside class="col-span-3 border-r bg-white p-4">
      <div class="flex items-center gap-2 mb-4">
        <div class="h-8 w-8 rounded bg-indigo-600"></div>
        <strong class="text-lg">Ebonia</strong>
      </div>
      <div class="mb-3 text-sm text-gray-500">Conversaciones</div>
      <ul id="conv-list" class="space-y-2 text-sm">
        <li class="text-gray-500">(vacío)</li>
      </ul>
    </aside>
    <main class="col-span-9 flex flex-col">
      <header class="p-4 border-b bg-white flex items-center justify-between">
        <div>
          <div class="text-sm text-gray-500">Sesión</div>
          <div id="session-user" class="font-medium">No autenticado</div>
        </div>
        <div class="flex gap-2">
          <button id="login-btn" class="px-3 py-1.5 bg-indigo-600 text-white rounded">Login</button>
          <button id="logout-btn" disabled class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded">Logout</button>
        </div>
      </header>
      <section class="flex-1 p-4 overflow-auto" id="messages"></section>
      <footer class="p-4 bg-white border-t">
        <form id="chat-form" class="flex gap-2">
          <input id="chat-input" class="flex-1 border rounded px-3 py-2" placeholder="Escribe un mensaje..." />
          <button class="px-4 py-2 bg-indigo-600 text-white rounded">Enviar</button>
        </form>
      </footer>
    </main>
  </div>
  <script type="module">
    const messagesEl = document.getElementById('messages');
    const inputEl = document.getElementById('chat-input');
    const formEl = document.getElementById('chat-form');
    const loginBtn = document.getElementById('login-btn');
    const logoutBtn = document.getElementById('logout-btn');
    const sessionUser = document.getElementById('session-user');

    let csrf = null;

    function append(role, content){
      const wrap = document.createElement('div');
      wrap.className = 'mb-3';
      const badge = document.createElement('div');
      badge.className = 'text-xs text-gray-500';
      badge.textContent = role === 'user' ? 'Tú' : 'Asistente';
      const bubble = document.createElement('div');
      bubble.className = role === 'user' ? 'inline-block bg-indigo-600 text-white px-3 py-2 rounded-lg' : 'inline-block bg-gray-200 text-gray-900 px-3 py-2 rounded-lg';
      bubble.textContent = content;
      wrap.appendChild(badge);
      wrap.appendChild(bubble);
      messagesEl.appendChild(wrap);
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    async function api(path, opts={}){
      const res = await fetch(path, {
        method: opts.method || 'GET',
        headers: {
          'Content-Type': 'application/json',
          ...(csrf ? { 'X-CSRF-Token': csrf } : {})
        },
        body: opts.body ? JSON.stringify(opts.body) : undefined,
        credentials: 'include'
      });
      const data = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(data?.error?.message || res.statusText);
      return data;
    }

    loginBtn.addEventListener('click', async ()=>{
      const email = prompt('Email', 'admin@example.com');
      const password = prompt('Password', 'admin1234');
      if(!email || !password) return;
      try {
        const data = await api('/api/auth/login.php', { method: 'POST', body: { email, password } });
        csrf = data.csrf_token;
        sessionUser.textContent = `${data.user.first_name} ${data.user.last_name} (${data.user.email})`;
        loginBtn.disabled = true;
        logoutBtn.disabled = false;
      } catch(e){
        alert('Login error: ' + e.message);
      }
    });

    logoutBtn.addEventListener('click', async ()=>{
      try {
        await api('/api/auth/logout.php', { method: 'POST' });
        csrf = null;
        sessionUser.textContent = 'No autenticado';
        loginBtn.disabled = false;
        logoutBtn.disabled = true;
      } catch(e){
        alert('Logout error: ' + e.message);
      }
    });

    formEl.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const text = inputEl.value.trim();
      if(!text) return;
      append('user', text);
      inputEl.value = '';
      try {
        const data = await api('/api/chat.php', { method: 'POST', body: { conversation_id: null, message: text } });
        append('assistant', data.message.content);
      } catch(e){
        append('assistant', 'Error: ' + e.message);
      }
    });
  </script>
</body>
</html>
