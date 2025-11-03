<?php ?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ebonia — IA Corporativa</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 text-slate-900">
  <div class="min-h-screen flex">
    <aside class="w-80 bg-white border-r border-slate-200 flex flex-col shadow-sm">
      <div class="p-5 border-b border-slate-200">
        <div class="flex items-center gap-3 mb-6">
          <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md">E</div>
          <div>
            <strong class="text-xl font-semibold text-slate-800">Ebonia</strong>
            <div class="text-xs text-slate-500">IA Corporativa</div>
          </div>
        </div>
        <button id="new-conv-btn" class="w-full py-2.5 px-4 rounded-lg bg-gradient-to-r from-violet-600 to-indigo-600 text-white font-medium shadow-md hover:shadow-lg hover:from-violet-700 hover:to-indigo-700 transition-all duration-200 flex items-center justify-center gap-2">
          <span class="text-lg">+</span> Nueva conversación
        </button>
      </div>
      <div class="flex-1 overflow-y-auto p-3">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 px-2">Conversaciones</div>
        <ul id="conv-list" class="space-y-1">
          <li class="text-slate-400 text-sm px-3 py-2">(vacío)</li>
        </ul>
      </div>
    </aside>
    <main class="flex-1 flex flex-col bg-white">
      <header class="px-6 py-4 border-b border-slate-200 bg-white/80 backdrop-blur-sm flex items-center justify-between shadow-sm">
        <div>
          <div class="text-xs font-medium text-slate-500 uppercase tracking-wide">Usuario actual</div>
          <div id="session-user" class="font-semibold text-slate-800 text-lg">No autenticado</div>
        </div>
        <div class="flex gap-2">
          <button id="login-btn" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg font-medium hover:bg-slate-200 transition-colors">Iniciar sesión</button>
          <button id="logout-btn" disabled class="px-4 py-2 bg-slate-100 text-slate-400 rounded-lg font-medium cursor-not-allowed">Cerrar sesión</button>
        </div>
      </header>
      <section class="flex-1 p-6 overflow-auto bg-gradient-to-b from-slate-50/50 to-white" id="messages"></section>
      <footer class="p-6 bg-white border-t border-slate-200 shadow-lg">
        <form id="chat-form" class="flex gap-3">
          <input id="chat-input" class="flex-1 border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-200 transition-all" placeholder="Escribe un mensaje..." />
          <button class="px-6 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 text-white rounded-xl font-medium shadow-md hover:shadow-lg hover:from-violet-700 hover:to-indigo-700 transition-all duration-200">Enviar</button>
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
      wrap.className = 'mb-4 flex ' + (role === 'user' ? 'justify-end' : 'justify-start');
      const bubble = document.createElement('div');
      bubble.className = role === 'user' 
        ? 'max-w-xl bg-gradient-to-br from-violet-600 to-indigo-600 text-white px-4 py-3 rounded-2xl rounded-tr-sm shadow-md' 
        : 'max-w-xl bg-white border border-slate-200 text-slate-800 px-4 py-3 rounded-2xl rounded-tl-sm shadow-sm';
      bubble.style.whiteSpace = 'pre-wrap';
      bubble.style.wordBreak = 'break-word';
      bubble.textContent = content;
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
        window.location.href = '/login.php';
      } catch(e){
        alert('Logout error: ' + e.message);
      }
    });

    async function loadConversations(){
      const data = await api('/api/conversations/list.php');
      const items = data.items || [];
      if(items.length === 0){
        convListEl.innerHTML = '<li class="text-slate-400 text-sm px-3 py-2">(vacío)</li>';
        return;
      }
      convListEl.innerHTML = '';
      for(const c of items){
        const li = document.createElement('li');
        const isActive = currentConversationId === c.id;
        li.className = 'group rounded-lg transition-all duration-200 ' + (isActive ? 'bg-gradient-to-r from-violet-50 to-indigo-50 shadow-sm' : 'hover:bg-slate-50');
        const container = document.createElement('div');
        container.className = 'flex items-center gap-2 p-2';
        const btn = document.createElement('button');
        btn.className = 'text-left flex-1 min-w-0';
        const titleEl = document.createElement('div');
        titleEl.className = 'font-medium text-sm truncate ' + (isActive ? 'text-violet-700' : 'text-slate-700 group-hover:text-slate-900');
        titleEl.textContent = c.title || `Conversación ${c.id}`;
        const timeEl = document.createElement('div');
        timeEl.className = 'text-xs text-slate-400 mt-0.5';
        timeEl.textContent = new Date(c.updated_at).toLocaleDateString('es-ES', {month: 'short', day: 'numeric'});
        btn.appendChild(titleEl);
        btn.appendChild(timeEl);
        btn.addEventListener('click', async () => {
          currentConversationId = c.id;
          await loadConversations();
          messagesEl.innerHTML = '';
          await loadMessages(c.id);
        });
        const actions = document.createElement('div');
        actions.className = 'flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity';
        const renameBtn = document.createElement('button');
        renameBtn.className = 'p-1.5 text-slate-400 hover:text-violet-600 hover:bg-violet-50 rounded transition-colors';
        renameBtn.innerHTML = '✏️';
        renameBtn.title = 'Renombrar';
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
        delBtn.className = 'p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors';
        delBtn.innerHTML = '🗑️';
        delBtn.title = 'Borrar';
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
        container.appendChild(btn);
        container.appendChild(actions);
        li.appendChild(container);
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
