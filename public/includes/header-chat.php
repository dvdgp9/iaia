<?php
// Header específico para la página principal del chat
?>
<header class="h-[60px] px-6 border-b border-slate-200 bg-white/95 backdrop-blur-sm flex items-center justify-between shadow-sm sticky top-0 z-10">
  <!-- Título conversación -->
  <div class="flex items-center gap-3 min-w-0">
    <!-- Título conversación activa -->
    <div id="conv-title" class="hidden flex items-center gap-2">
      <i class="iconoir-chat-bubble text-[#23AAC5]"></i>
      <span class="text-sm font-medium text-slate-700 truncate max-w-md"></span>
    </div>
  </div>
  
  <!-- Acciones derecha -->
  <div class="flex items-center gap-3">
    <!-- Búsqueda (preparado futuro) -->
    <button class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-lg transition-colors" title="Buscar (próximamente)">
      <i class="iconoir-search text-xl"></i>
    </button>
    
    <!-- FAQ / Dudas rápidas -->
    <button id="faq-btn" class="p-2 text-slate-400 hover:text-[#23AAC5] hover:bg-[#23AAC5]/10 rounded-lg transition-colors" title="Dudas rápidas">
      <i class="iconoir-help-circle text-xl"></i>
    </button>
    
    <!-- Avatar + Dropdown -->
    <div class="relative" id="profile-dropdown-container">
      <button id="profile-btn" class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded-lg transition-colors">
        <div class="h-8 w-8 rounded-full gradient-brand flex items-center justify-center text-white text-sm font-semibold" id="user-avatar">?</div>
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
        <a href="/admin/users.php" id="admin-link" class="hidden w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2 border-t border-slate-100">
          <i class="iconoir-settings"></i>
          <span>Gestión de usuarios</span>
        </a>
        <a href="/admin/stats.php" id="stats-link" class="hidden w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2">
          <i class="iconoir-graph-up"></i>
          <span>Panel de control</span>
        </a>
        <button id="logout-btn" class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 transition-colors flex items-center gap-2 border-t border-slate-100">
          <i class="iconoir-log-out"></i>
          <span>Cerrar sesión</span>
        </button>
      </div>
    </div>
  </div>
</header>

<script>
// Lógica simple para dropdown de perfil
document.addEventListener('DOMContentLoaded', () => {
  const profileBtn = document.getElementById('profile-btn');
  const profileDropdown = document.getElementById('profile-dropdown');
  
  if(profileBtn && profileDropdown) {
    profileBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      profileDropdown.classList.toggle('hidden');
    });
    
    document.addEventListener('click', (e) => {
      if (!profileDropdown.contains(e.target) && !profileBtn.contains(e.target)) {
        profileDropdown.classList.add('hidden');
      }
    });
  }
});
</script>
