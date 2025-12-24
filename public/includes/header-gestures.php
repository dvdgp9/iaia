<?php
// Header específico para páginas de gestos con navegación personalizada
// Variables esperadas: $pageTitle, $gestureIcon, $gestureColor, $backUrl (opcional)
$backUrl = $backUrl ?? '/gestos/';
$backText = $backText ?? 'Todos los gestos';
$gestureIcon = $gestureIcon ?? 'iconoir-magic-wand';
$gestureColor = $gestureColor ?? 'from-cyan-500 to-teal-600';
?>
<header class="h-[60px] px-6 border-b border-slate-200/50 glass-strong flex items-center justify-between shadow-sm shrink-0">
  <div class="flex items-center gap-4">
    <a href="<?php echo htmlspecialchars($backUrl); ?>" class="flex items-center gap-2 text-slate-600 hover:text-cyan-600 transition-smooth">
      <i class="iconoir-arrow-left text-lg"></i>
      <span class="text-sm font-medium"><?php echo htmlspecialchars($backText); ?></span>
    </a>
    <div class="h-6 w-px bg-slate-200"></div>
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-lg bg-gradient-to-br <?php echo htmlspecialchars($gestureColor); ?> flex items-center justify-center text-white shadow-md">
        <i class="<?php echo htmlspecialchars($gestureIcon); ?> text-sm"></i>
      </div>
      <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($pageTitle ?? 'Gesto'); ?></span>
    </div>
  </div>
  
  <!-- Acciones derecha (perfil) -->
  <div class="flex items-center gap-3">
    <!-- Avatar + Dropdown -->
    <div class="relative" id="profile-dropdown-container">
      <button id="profile-btn" class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded-lg transition-colors">
        <div class="h-8 w-8 rounded-full gradient-brand flex items-center justify-center text-white text-sm font-semibold" id="user-avatar">
          <?php echo strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? '', 0, 1)); ?>
        </div>
        <i class="iconoir-nav-arrow-down text-slate-400 text-sm"></i>
      </button>
      
      <!-- Dropdown menu -->
      <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-50">
        <div class="px-4 py-3 border-b border-slate-100">
          <div class="font-semibold text-slate-800 text-sm">
            <?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?>
          </div>
          <div class="text-xs text-slate-500 mt-0.5">
            <?php echo htmlspecialchars($user['email'] ?? ''); ?>
          </div>
        </div>
        <a href="/account.php" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2">
          <i class="iconoir-user"></i>
          <span>Mi cuenta</span>
        </a>
        
        <?php if (in_array('admin', $user['roles'] ?? [], true)): ?>
          <a href="/admin/users.php" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2 border-t border-slate-100">
            <i class="iconoir-settings"></i>
            <span>Gestión de usuarios</span>
          </a>
          <a href="/admin/stats.php" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2">
            <i class="iconoir-graph-up"></i>
            <span>Panel de control</span>
          </a>
        <?php endif; ?>
        
        <button id="logout-btn" onclick="window.location.href='/logout.php'" class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 transition-colors flex items-center gap-2 border-t border-slate-100">
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
