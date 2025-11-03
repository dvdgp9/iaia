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
      <div class="mb-2 flex items-center justify-between">
        <div class="text-sm text-gray-500">Conversaciones</div>
        <button id="new-conv-btn" class="text-xs px-2 py-1 rounded bg-indigo-600 text-white">Nueva</button>
      </div>
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
    const convListEl = document.getElementById('conv-list');
    const newConvBtn = document.getElementById('new-conv-btn');

    let csrf = null;
    let currentConversationId = null;

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

    // Restaurar sesión al cargar (si existe cookie de sesión)
    (async function initSession(){
      try {
        const res = await fetch('/api/auth/me.php', { credentials: 'include' });
        if (res.status === 401) {
          window.location.href = '/login.php';
          return;
        }
        const data = await res.json();
        csrf = data.csrf_token || null;
        sessionUser.textContent = `${data.user.first_name} ${data.user.last_name} (${data.user.email})`;
        loginBtn.disabled = true;
        logoutBtn.disabled = false;
        await loadConversations();
      } catch (_) {
        window.location.href = '/login.php';
      }
    })();

    loginBtn.addEventListener('click', ()=>{ window.location.href = '/login.php'; });

    logoutBtn.addEventListener('click', async ()=>{
      try {
        await api('/api/auth/logout.php', { method: 'POST' });
        csrf = null;
        sessionUser.textContent = 'No autenticado';
        loginBtn.disabled = false;
        logoutBtn.disabled = true;
        convListEl.innerHTML = '<li class="text-gray-500">(vacío)</li>';
        currentConversationId = null;
        messagesEl.innerHTML = '';
      } catch(e){
        alert('Logout error: ' + e.message);
      }
    });

    async function loadConversations(){
      const data = await api('/api/conversations/list.php');
      const items = data.items || [];
      if(items.length === 0){
        convListEl.innerHTML = '<li class="text-gray-500">(vacío)</li>';
        return;
      }
      convListEl.innerHTML = '';
      for(const c of items){
        const li = document.createElement('li');
        li.className = 'flex items-center justify-between gap-2';
        if (currentConversationId === c.id) li.classList.add('bg-gray-100');
        const btn = document.createElement('button');
        btn.className = 'text-left flex-1 px-2 py-1 rounded hover:bg-gray-100';
        btn.textContent = c.title || `Conversación ${c.id}`;
        btn.addEventListener('click', async () => {
          currentConversationId = c.id;
          await highlightActive();
          messagesEl.innerHTML = '';
          await loadMessages(c.id);
        });
        const actions = document.createElement('div');
        actions.className = 'flex items-center gap-1';
        const renameBtn = document.createElement('button');
        renameBtn.className = 'text-xs text-gray-500 hover:text-gray-800 px-1 py-0.5';
        renameBtn.textContent = 'Renombrar';
        renameBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          const title = prompt('Nuevo título', c.title || '');
          if (!title) return;
          try {
            await api('/api/conversations/rename.php', { method: 'POST', body: { id: c.id, title } });
            await loadConversations();
          } catch (err) {
            alert('Error al renombrar: ' + err.message);
          }
        });
        const delBtn = document.createElement('button');
        delBtn.className = 'text-xs text-red-600 hover:text-red-800 px-1 py-0.5';
        delBtn.textContent = 'Borrar';
        delBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          if (!confirm('¿Borrar conversación?')) return;
          try {
            await api('/api/conversations/delete.php', { method: 'POST', body: { id: c.id } });
            if (currentConversationId === c.id) {
              currentConversationId = null;
              messagesEl.innerHTML = '';
            }
            await loadConversations();
          } catch (err) {
            alert('Error al borrar: ' + err.message);
          }
        });
        actions.appendChild(renameBtn);
        actions.appendChild(delBtn);
        li.appendChild(btn);
        li.appendChild(actions);
        convListEl.appendChild(li);
      }
    }

    async function loadMessages(conversationId){
      const data = await api(`/api/messages/list.php?conversation_id=${encodeURIComponent(conversationId)}`);
      messagesEl.innerHTML = '';
      for(const m of (data.items || [])){
        append(m.role, m.content);
      }
    }

    newConvBtn.addEventListener('click', async ()=>{
      try{
        const res = await api('/api/conversations/create.php', { method: 'POST', body: {} });
        currentConversationId = res.id;
        await loadConversations();
        messagesEl.innerHTML = '';
      }catch(e){
        alert('Error al crear conversación: ' + e.message);
      }
    });

    formEl.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const text = inputEl.value.trim();
      if(!text) return;
      append('user', text);
      inputEl.value = '';
      try {
        const data = await api('/api/chat.php', { method: 'POST', body: { conversation_id: currentConversationId, message: text } });
        if (!currentConversationId && data.conversation && data.conversation.id) {
          currentConversationId = data.conversation.id;
          await loadConversations();
        }
        append('assistant', data.message.content);
      } catch(e){
        append('assistant', 'Error: ' + e.message);
      }
    });

    async function highlightActive(){
      const items = convListEl.querySelectorAll('li');
      items.forEach(li => li.classList.remove('bg-gray-100'));
      // volver a poner la clase sobre el seleccionado en el próximo render de lista
    }
  </script>
</body>
</html>
