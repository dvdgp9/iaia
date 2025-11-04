<?php ?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ebonia — IA Corporativa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/iconoir-icons/iconoir@main/css/iconoir.css">
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
        <div class="flex items-center justify-between mb-2 px-2">
          <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Conversaciones</div>
          <select id="sort-select" class="text-xs border border-slate-200 rounded px-2 py-1 bg-white focus:outline-none focus:border-violet-400">
            <option value="updated_at">Recientes</option>
            <option value="favorite">Favoritos</option>
            <option value="created_at">Creación</option>
            <option value="title">Alfabético</option>
          </select>
        </div>
        <ul id="conv-list" class="space-y-1">
          <li class="text-slate-400 text-sm px-3 py-2">(vacío)</li>
        </ul>
      </div>
    </aside>
    <main class="flex-1 flex flex-col bg-white">
      <header class="h-[52px] px-6 border-b border-slate-200 bg-white/95 backdrop-blur-sm flex items-center justify-between shadow-sm sticky top-0 z-10">
        <!-- Logo -->
        <div class="flex items-center gap-4 min-w-0">
          <div class="text-base font-semibold text-slate-800 whitespace-nowrap">Ebonia</div>
          <!-- Título conversación activa -->
          <div id="conv-title" class="hidden text-sm text-slate-500 truncate max-w-md"></div>
        </div>
        
        <!-- Acciones derecha -->
        <div class="flex items-center gap-3">
          <!-- Búsqueda (preparado futuro) -->
          <button class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-lg transition-colors" title="Buscar (próximamente)">
            <i class="iconoir-search text-xl"></i>
          </button>
          
          <!-- Avatar + Dropdown -->
          <div class="relative" id="profile-dropdown-container">
            <button id="profile-btn" class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded-lg transition-colors">
              <div class="h-8 w-8 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-sm font-semibold" id="user-avatar">?</div>
              <i class="iconoir-nav-arrow-down text-slate-400 text-sm"></i>
            </button>
            
            <!-- Dropdown menu -->
            <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-50">
              <div class="px-4 py-3 border-b border-slate-100">
                <div id="session-user" class="font-semibold text-slate-800 text-sm">Cargando...</div>
                <div id="session-meta" class="text-xs text-slate-500 mt-0.5"></div>
              </div>
              <a href="/account.php" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2">
                <i class="iconoir-user"></i>
                <span>Mi cuenta</span>
              </a>
              <button id="logout-btn" class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 transition-colors flex items-center gap-2">
                <i class="iconoir-log-out"></i>
                <span>Cerrar sesión</span>
              </button>
            </div>
          </div>
        </div>
      </header>
      <section class="flex-1 overflow-auto bg-gradient-to-b from-slate-50/50 to-white relative" id="messages-container">
        <div id="context-warning" class="hidden mx-6 mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-start gap-3">
          <i class="iconoir-info-circle text-amber-600 text-lg mt-0.5"></i>
          <div class="flex-1 text-sm">
            <div class="font-medium text-amber-900">Conversación muy larga</div>
            <div class="text-amber-700 mt-0.5">Para optimizar el rendimiento, solo se envían los mensajes más recientes al asistente. El historial completo permanece guardado.</div>
          </div>
        </div>
        <div id="empty-state" class="absolute inset-0 flex items-center justify-center p-8">
          <div class="max-w-3xl w-full space-y-8">
            <div class="text-center space-y-2">
              <h2 class="text-4xl font-bold text-slate-800">¿En qué puedo ayudarte?</h2>
              <p class="text-slate-500">Hazme cualquier pregunta o selecciona una opción</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <button class="prompt-suggestion p-4 bg-white border-2 border-slate-200 rounded-xl hover:border-violet-300 hover:shadow-md transition-all text-left group">
                <div class="flex items-center gap-2 text-sm font-medium text-slate-700 group-hover:text-violet-700">
                  <i class="iconoir-light-bulb text-lg"></i>
                  <span>Resumir documento</span>
                </div>
                <div class="text-xs text-slate-500 mt-1">Analiza y extrae los puntos clave</div>
              </button>
              <button class="prompt-suggestion p-4 bg-white border-2 border-slate-200 rounded-xl hover:border-violet-300 hover:shadow-md transition-all text-left group">
                <div class="flex items-center gap-2 text-sm font-medium text-slate-700 group-hover:text-violet-700">
                  <i class="iconoir-graph-up text-lg"></i>
                  <span>Analizar datos</span>
                </div>
                <div class="text-xs text-slate-500 mt-1">Genera insights de tus datos</div>
              </button>
              <button class="prompt-suggestion p-4 bg-white border-2 border-slate-200 rounded-xl hover:border-violet-300 hover:shadow-md transition-all text-left group">
                <div class="flex items-center gap-2 text-sm font-medium text-slate-700 group-hover:text-violet-700">
                  <i class="iconoir-edit-pencil text-lg"></i>
                  <span>Redactar contenido</span>
                </div>
                <div class="text-xs text-slate-500 mt-1">Crea textos profesionales</div>
              </button>
              <button class="prompt-suggestion p-4 bg-white border-2 border-slate-200 rounded-xl hover:border-violet-300 hover:shadow-md transition-all text-left group">
                <div class="flex items-center gap-2 text-sm font-medium text-slate-700 group-hover:text-violet-700">
                  <i class="iconoir-search text-lg"></i>
                  <span>Buscar información</span>
                </div>
                <div class="text-xs text-slate-500 mt-1">Encuentra respuestas rápidas</div>
              </button>
            </div>
            <form id="chat-form-empty" class="max-w-2xl mx-auto">
              <div class="relative">
                <input id="chat-input-empty" class="w-full border-2 border-slate-300 rounded-2xl px-6 py-4 pr-14 text-lg focus:outline-none focus:border-violet-500 focus:ring-4 focus:ring-violet-100 transition-all shadow-lg" placeholder="Pregúntame lo que quieras..." />
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 p-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 text-white rounded-xl hover:shadow-lg transition-all">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
              </div>
            </form>
          </div>
        </div>
        <div id="messages" class="hidden p-6"></div>
      </section>
      <footer id="chat-footer" class="hidden p-6 bg-white border-t border-slate-200 shadow-lg">
        <form id="chat-form" class="max-w-4xl mx-auto flex gap-3">
          <input id="chat-input" class="flex-1 border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-200 transition-all" placeholder="Escribe un mensaje..." />
          <button type="submit" class="px-6 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 text-white rounded-xl font-medium shadow-md hover:shadow-lg hover:from-violet-700 hover:to-indigo-700 transition-all duration-200">Enviar</button>
        </form>
      </footer>
    </main>
  </div>
  <script type="module">
    const messagesEl = document.getElementById('messages');
    const messagesContainer = document.getElementById('messages-container');
    const emptyState = document.getElementById('empty-state');
    const chatFooter = document.getElementById('chat-footer');
    const inputEl = document.getElementById('chat-input');
    const inputEmptyEl = document.getElementById('chat-input-empty');
    const formEl = document.getElementById('chat-form');
    const formEmptyEl = document.getElementById('chat-form-empty');
    const logoutBtn = document.getElementById('logout-btn');
    const sessionUser = document.getElementById('session-user');
    const sessionMeta = document.getElementById('session-meta');
    const userAvatar = document.getElementById('user-avatar');
    const profileBtn = document.getElementById('profile-btn');
    const profileDropdown = document.getElementById('profile-dropdown');
    const convTitleEl = document.getElementById('conv-title');
    const convListEl = document.getElementById('conv-list');
    const newConvBtn = document.getElementById('new-conv-btn');
    const sortSelect = document.getElementById('sort-select');

    let csrf = null;
    let currentConversationId = null;
    let emptyConversationId = null; // id de conversación sin mensajes aún
    let currentUser = null;
    let currentConvTitle = null;

    function showChatMode(){
      emptyState.classList.add('hidden');
      messagesEl.classList.remove('hidden');
      chatFooter.classList.remove('hidden');
    }

    function showEmptyMode(){
      emptyState.classList.remove('hidden');
      messagesEl.classList.add('hidden');
      chatFooter.classList.add('hidden');
      messagesEl.innerHTML = '';
      document.getElementById('context-warning').classList.add('hidden');
      convTitleEl.classList.add('hidden');
      inputEmptyEl?.focus();
    }

    function escapeHtml(str){
      return str.replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
    }

    function mdToHtml(md){
      // escape first
      let s = escapeHtml(md);
      // headings
      s = s.replace(/^###\s+(.+)$/gm, '<h3 class="font-semibold text-base mb-1">$1<\/h3>');
      s = s.replace(/^##\s+(.+)$/gm, '<h2 class="font-semibold text-lg mb-1">$1<\/h2>');
      s = s.replace(/^#\s+(.+)$/gm, '<h1 class="font-semibold text-xl mb-1">$1<\/h1>');
      // bold and italics (basic)
      s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1<\/strong>');
      s = s.replace(/\*(.+?)\*/g, '<em>$1<\/em>');
      // inline code
      s = s.replace(/`([^`]+)`/g, '<code class="px-1 py-0.5 bg-slate-100 rounded">$1<\/code>');
      // line breaks
      s = s.replace(/\n/g, '<br>');
      return s;
    }

    function append(role, content){
      if(messagesEl.children.length === 0) showChatMode();
      const wrap = document.createElement('div');
      wrap.className = 'mb-4 flex ' + (role === 'user' ? 'justify-end' : 'justify-start');
      const bubble = document.createElement('div');
      bubble.className = role === 'user' 
        ? 'max-w-2xl bg-gradient-to-br from-violet-600 to-indigo-600 text-white px-5 py-3 rounded-2xl rounded-tr-sm shadow-md' 
        : 'max-w-2xl bg-white border border-slate-200 text-slate-800 px-5 py-3 rounded-2xl rounded-tl-sm shadow-sm';
      bubble.style.wordBreak = 'break-word';
      if (role === 'assistant') {
        bubble.innerHTML = mdToHtml(content);
      } else {
        bubble.textContent = content;
      }
      wrap.appendChild(bubble);
      messagesEl.appendChild(wrap);
      messagesContainer.scrollTop = messagesContainer.scrollHeight;
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
        currentUser = data.user;
        
        // Actualizar UI de perfil
        const fullName = `${data.user.first_name} ${data.user.last_name}`;
        sessionUser.textContent = fullName;
        sessionMeta.textContent = data.user.email;
        
        // Avatar con iniciales
        const initials = `${data.user.first_name[0]}${data.user.last_name[0]}`.toUpperCase();
        userAvatar.textContent = initials;
        
        await loadConversations();
      } catch (_) {
        window.location.href = '/login.php';
      }
    })();

    // Toggle dropdown perfil
    profileBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      profileDropdown.classList.toggle('hidden');
    });
    
    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', (e) => {
      if (!profileDropdown.classList.contains('hidden') && 
          !e.target.closest('#profile-dropdown-container')) {
        profileDropdown.classList.add('hidden');
      }
    });
    
    sortSelect.addEventListener('change', () => loadConversations());

    logoutBtn.addEventListener('click', async (e)=>{
      e.stopPropagation();
      try {
        await api('/api/auth/logout.php', { method: 'POST' });
        window.location.href = '/login.php';
      } catch(e){
        alert('Logout error: ' + e.message);
      }
    });

    async function loadConversations(){
      const sort = sortSelect.value || 'updated_at';
      const data = await api(`/api/conversations/list.php?sort=${encodeURIComponent(sort)}`);
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
        btn.className = 'text-left flex-1 min-w-0 flex items-center gap-2';
        
        // Icono de estrella favorito
        const starBtn = document.createElement('button');
        starBtn.className = 'flex-shrink-0 transition-colors';
        starBtn.innerHTML = c.is_favorite 
          ? '<i class="iconoir-star-solid text-amber-500"></i>'
          : '<i class="iconoir-star text-slate-300 group-hover:text-slate-400"></i>';
        starBtn.title = c.is_favorite ? 'Quitar de favoritos' : 'Añadir a favoritos';
        starBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          try {
            await api('/api/conversations/toggle_favorite.php', { method: 'POST', body: { id: c.id } });
            await loadConversations();
          } catch (err) {
            alert('Error al cambiar favorito: ' + err.message);
          }
        });
        
        const textContainer = document.createElement('div');
        textContainer.className = 'flex-1 min-w-0';
        const titleEl = document.createElement('div');
        titleEl.className = 'font-medium text-sm truncate ' + (isActive ? 'text-violet-700' : 'text-slate-700 group-hover:text-slate-900');
        titleEl.textContent = c.title || `Conversación ${c.id}`;
        const timeEl = document.createElement('div');
        timeEl.className = 'text-xs text-slate-400 mt-0.5';
        timeEl.textContent = new Date(c.updated_at).toLocaleDateString('es-ES', {month: 'short', day: 'numeric'});
        textContainer.appendChild(titleEl);
        textContainer.appendChild(timeEl);
        
        btn.appendChild(starBtn);
        btn.appendChild(textContainer);
        btn.addEventListener('click', async () => {
          currentConversationId = c.id;
          updateConvTitle(c.title);
          await loadConversations();
          messagesEl.innerHTML = '';
          await loadMessages(c.id);
        });
        const actions = document.createElement('div');
        actions.className = 'flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity';
        const renameBtn = document.createElement('button');
        renameBtn.className = 'p-1.5 text-slate-400 hover:text-violet-600 hover:bg-violet-50 rounded transition-colors';
        renameBtn.innerHTML = '<i class="iconoir-edit-pencil"></i>';
        renameBtn.title = 'Renombrar';
        renameBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          const title = prompt('Nuevo título', c.title || '');
          if (!title) return;
          try {
            await api('/api/conversations/rename.php', { method: 'POST', body: { id: c.id, title } });
            // Actualizar título en header si es la conversación activa
            if (currentConversationId === c.id) {
              updateConvTitle(title);
            }
            await loadConversations();
          } catch (err) {
            alert('Error al renombrar: ' + err.message);
          }
        });
        const delBtn = document.createElement('button');
        delBtn.className = 'p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors';
        delBtn.innerHTML = '<i class="iconoir-trash"></i>';
        delBtn.title = 'Borrar';
        delBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          if (!confirm('¿Borrar conversación?')) return;
          try {
            await api('/api/conversations/delete.php', { method: 'POST', body: { id: c.id } });
            if (currentConversationId === c.id) {
              currentConversationId = null;
              messagesEl.innerHTML = '';
              updateConvTitle(null);
              showEmptyMode();
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
      document.getElementById('context-warning').classList.add('hidden');
      const items = data.items || [];
      if(items.length > 0){
        showChatMode();
        for(const m of items){
          append(m.role, m.content);
        }
        emptyConversationId = null;
      } else {
        showEmptyMode();
        emptyConversationId = conversationId;
      }
    }
    
    function updateConvTitle(title) {
      if (title && title !== 'Nueva conversación') {
        currentConvTitle = title;
        convTitleEl.textContent = title;
        convTitleEl.classList.remove('hidden');
      } else {
        currentConvTitle = null;
        convTitleEl.classList.add('hidden');
      }
    }

    newConvBtn.addEventListener('click', async ()=>{
      try{
        // Si ya hay una conversación vacía sin mensajes, reutilizarla
        if (emptyConversationId) {
          currentConversationId = emptyConversationId;
          updateConvTitle(null);
          await loadConversations();
          showEmptyMode();
          return;
        }
        const res = await api('/api/conversations/create.php', { method: 'POST', body: {} });
        currentConversationId = res.id;
        emptyConversationId = res.id;
        updateConvTitle(null);
        await loadConversations();
        showEmptyMode();
      }catch(e){
        alert('Error al crear conversación: ' + e.message);
      }
    });

    async function handleSubmit(text){
      if(!text) return;
      append('user', text);
      try {
        const data = await api('/api/chat.php', { method: 'POST', body: { conversation_id: currentConversationId, message: text } });
        if (!currentConversationId && data.conversation && data.conversation.id) {
          currentConversationId = data.conversation.id;
          await loadConversations();
        }
        // Actualizar título tras auto-title
        if (data.conversation && data.conversation.id === currentConversationId) {
          const convData = await api(`/api/conversations/list.php`);
          const conv = convData.items?.find(c => c.id === currentConversationId);
          if (conv) updateConvTitle(conv.title);
        }
        // Al enviar el primer mensaje, ya no es conversación vacía
        if (emptyConversationId === currentConversationId) emptyConversationId = null;
        // Mostrar/ocultar aviso de truncamiento
        const warning = document.getElementById('context-warning');
        if (data.context_truncated) {
          warning.classList.remove('hidden');
        } else {
          warning.classList.add('hidden');
        }
        append('assistant', data.message.content);
      } catch(e){
        append('assistant', 'Error: ' + e.message);
      }
    }

    formEl.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const text = inputEl.value.trim();
      inputEl.value = '';
      await handleSubmit(text);
    });

    formEmptyEl.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const text = inputEmptyEl.value.trim();
      inputEmptyEl.value = '';
      await handleSubmit(text);
    });

    document.querySelectorAll('.prompt-suggestion').forEach(btn => {
      btn.addEventListener('click', async () => {
        const text = btn.querySelector('span').textContent.trim();
        inputEmptyEl.value = text;
        await handleSubmit(text);
      });
    });

    async function highlightActive(){
      const items = convListEl.querySelectorAll('li');
      items.forEach(li => li.classList.remove('bg-gray-100'));
      // volver a poner la clase sobre el seleccionado en el próximo render de lista
    }
  </script>
</body>
</html>
