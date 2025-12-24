<?php
require_once __DIR__ . '/../../src/App/bootstrap.php';
require_once __DIR__ . '/../../src/App/Session.php';

use App\Session;

Session::start();
$user = Session::user();
if (!$user) {
    header('Location: /login.php');
    exit;
}

// Verificar si es superadmin
$isSuperadmin = in_array('admin', $user['roles'] ?? [], true);
if (!$isSuperadmin) {
    header('Location: /');
    exit;
}
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestión de usuarios — Ebonia</title>
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <link rel="apple-touch-icon" href="/assets/images/isotipo.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/iconoir-icons/iconoir@main/css/iconoir.css">
  <style>
    /* Estilos base para el layout */
    .gradient-brand { background: linear-gradient(135deg, #23AAC5 0%, #115c6c 100%); }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  </style>
</head>
<body class="bg-slate-50 text-slate-900 overflow-hidden">
  <div class="min-h-screen flex h-screen">
    <?php 
    $activeTab = '';
    $pageTitle = 'Gestión de usuarios';
    include __DIR__ . '/../includes/left-tabs.php'; 
    ?>

    <main class="flex-1 flex flex-col min-w-0">
      <?php include __DIR__ . '/../includes/header-unified.php'; ?>

      <div class="flex-1 overflow-auto bg-slate-50">
        <div class="max-w-7xl mx-auto p-6">
          <!-- Header -->
          <div class="flex items-center justify-between mb-8 mt-6">
            <div>
              <h1 class="text-3xl font-bold text-slate-800">Gestión de usuarios</h1>
              <p class="text-slate-600 mt-1">Crear, editar y gestionar cuentas de usuario</p>
            </div>
            <button id="create-user-btn" class="px-4 py-2 bg-gradient-to-r from-[#23AAC5] to-[#115c6c] text-white rounded-lg font-medium hover:opacity-90 hover:shadow-lg transition-all flex items-center gap-2 shadow-md">
              <i class="iconoir-plus-circle"></i>
              <span>Nuevo usuario</span>
            </button>
          </div>

          <!-- Búsqueda y filtros -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-6">
      <div class="flex items-center gap-4">
        <div class="flex-1 relative">
          <i class="iconoir-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
          <input type="text" id="search-input" placeholder="Buscar por nombre, email..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-colors">
        </div>
        <select id="status-filter" class="px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-colors">
          <option value="">Todos los estados</option>
          <option value="active">Activos</option>
          <option value="disabled">Deshabilitados</option>
        </select>
      </div>
    </div>

    <!-- Lista de usuarios -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div id="users-loading" class="text-center py-12">
        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-[#23AAC5] border-r-transparent"></div>
        <p class="text-sm text-slate-500 mt-3">Cargando usuarios...</p>
      </div>

      <div id="users-container" class="hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Usuario</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Departamento</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Último acceso</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody id="users-list" class="divide-y divide-slate-200">
              <!-- Se llena dinámicamente -->
            </tbody>
          </table>
        </div>
        <div id="no-results" class="hidden text-center py-12">
          <i class="iconoir-search text-4xl text-slate-300"></i>
          <p class="text-slate-500 mt-3">No se encontraron usuarios</p>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</main>
</div>

  <!-- Modal confirmación eliminar -->
  <div id="delete-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
      <div class="flex items-center gap-3 mb-4">
        <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
          <i class="iconoir-warning-triangle text-red-600 text-2xl"></i>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-slate-800">Eliminar usuario</h3>
          <p class="text-sm text-slate-600 mt-0.5">Esta acción no se puede deshacer</p>
        </div>
      </div>

      <p class="text-slate-700 mb-6">
        ¿Estás seguro de que deseas eliminar al usuario <strong id="delete-user-name" class="text-slate-900"></strong>?
        <br><br>
        Se eliminarán todas sus conversaciones, mensajes y datos asociados de forma permanente.
      </p>

      <div class="flex gap-3">
        <button id="confirm-delete-btn" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors text-sm">
          Sí, eliminar usuario
        </button>
        <button id="cancel-delete-btn" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors text-sm">
          Cancelar
        </button>
      </div>
    </div>
  </div>

  <!-- Modal crear/editar usuario -->
  <div id="user-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-slate-800" id="modal-title">Nuevo usuario</h3>
        <button id="close-modal-btn" class="p-1 text-slate-400 hover:text-slate-600 transition-colors">
          <i class="iconoir-xmark text-xl"></i>
        </button>
      </div>

      <form id="user-form" class="space-y-4">
        <input type="hidden" id="user-id">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium text-slate-700 block mb-2">Nombre *</label>
            <input type="text" id="user-first-name" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-colors" required>
          </div>
          <div>
            <label class="text-sm font-medium text-slate-700 block mb-2">Apellidos *</label>
            <input type="text" id="user-last-name" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-colors" required>
          </div>
        </div>

        <div>
          <label class="text-sm font-medium text-slate-700 block mb-2">Email *</label>
          <input type="email" id="user-email" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-colors" required>
        </div>

        <div>
          <label class="text-sm font-medium text-slate-700 block mb-2">Departamento</label>
          <select id="user-department" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-colors">
            <option value="">Sin asignar</option>
            <!-- Se llena dinámicamente -->
          </select>
        </div>

        <div id="password-section">
          <label class="text-sm font-medium text-slate-700 block mb-2">
            <span id="password-label">Contraseña *</span>
          </label>
          <input type="password" id="user-password" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-colors" minlength="8">
          <p class="text-xs text-slate-500 mt-1" id="password-hint">Mínimo 8 caracteres</p>
        </div>

        <div class="flex items-center gap-4 pt-2 border-t border-slate-100">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" id="user-superadmin" class="w-4 h-4 text-[#23AAC5] border-slate-300 rounded focus:ring-[#23AAC5]">
            <span class="text-sm font-medium text-slate-700">Superadministrador</span>
          </label>
          
          <label class="flex items-center gap-2 cursor-pointer" id="status-toggle-container">
            <input type="checkbox" id="user-status" class="w-4 h-4 text-[#23AAC5] border-slate-300 rounded focus:ring-[#23AAC5]" checked>
            <span class="text-sm font-medium text-slate-700">Cuenta activa</span>
          </label>
        </div>

        <div id="user-error" class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"></div>

        <div class="flex gap-3 pt-2">
          <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-[#23AAC5] to-[#115c6c] text-white rounded-lg font-medium hover:opacity-90 transition-all text-sm shadow-md">
            <span id="submit-text">Crear usuario</span>
          </button>
          <button type="button" id="cancel-modal-btn" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors text-sm">
            Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const csrf = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    let allUsers = [];
    let allDepartments = [];
    let isEditMode = false;
    
    // API helper
    async function api(path, opts = {}) {
      const res = await fetch(path, {
        method: opts.method || 'GET',
        headers: {
          'Content-Type': 'application/json',
          ...(csrf ? { 'X-CSRF-Token': csrf } : {})
        },
        body: opts.body ? JSON.stringify(opts.body) : undefined,
        credentials: 'include'
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data?.error?.message || res.statusText);
      return data;
    }

    // Cargar usuarios
    async function loadUsers() {
      try {
        const data = await api('/api/admin/users/list.php');
        allUsers = data.users || [];
        renderUsers();
        document.getElementById('users-loading').classList.add('hidden');
        document.getElementById('users-container').classList.remove('hidden');
      } catch (err) {
        document.getElementById('users-loading').innerHTML = '<p class="text-sm text-red-600">Error al cargar usuarios</p>';
      }
    }

    // Cargar departamentos
    async function loadDepartments() {
      try {
        const data = await api('/api/admin/departments/list.php');
        allDepartments = data.departments || [];
        renderDepartmentOptions();
      } catch (err) {
        console.error('Error al cargar departamentos:', err);
      }
    }

    // Renderizar opciones de departamento
    function renderDepartmentOptions() {
      const select = document.getElementById('user-department');
      const currentOptions = select.innerHTML;
      const firstOption = '<option value="">Sin asignar</option>';
      
      const options = allDepartments.map(d => 
        `<option value="${d.id}">${escapeHtml(d.name)}</option>`
      ).join('');
      
      select.innerHTML = firstOption + options;
    }

    // Renderizar usuarios
    function renderUsers() {
      const container = document.getElementById('users-list');
      const noResults = document.getElementById('no-results');
      const searchTerm = document.getElementById('search-input').value.toLowerCase();
      const statusFilter = document.getElementById('status-filter').value;

      let filtered = allUsers.filter(u => {
        const matchesSearch = !searchTerm || 
          u.first_name.toLowerCase().includes(searchTerm) ||
          u.last_name.toLowerCase().includes(searchTerm) ||
          u.email.toLowerCase().includes(searchTerm);
        
        const matchesStatus = !statusFilter || u.status === statusFilter;
        
        return matchesSearch && matchesStatus;
      });

      if (filtered.length === 0) {
        container.innerHTML = '';
        noResults.classList.remove('hidden');
        return;
      }

      noResults.classList.add('hidden');
      container.innerHTML = filtered.map(u => {
        const statusBadge = u.status === 'active'
          ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Activo</span>'
          : '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Deshabilitado</span>';
        
        const lastLogin = u.last_login_at 
          ? new Date(u.last_login_at).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' })
          : '<span class="text-slate-400">Nunca</span>';
        
        const superadminBadge = u.is_superadmin 
          ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-[#23AAC5]/10 text-[#23AAC5] ml-2">Admin</span>'
          : '';

        return `
          <tr class="hover:bg-slate-50 transition-colors">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-[#23AAC5] to-[#115c6c] flex items-center justify-center text-white font-semibold text-sm">
                  ${escapeHtml(u.first_name[0] + u.last_name[0]).toUpperCase()}
                </div>
                <div>
                  <div class="font-medium text-slate-800">
                    ${escapeHtml(u.first_name)} ${escapeHtml(u.last_name)}
                    ${superadminBadge}
                  </div>
                  <div class="text-xs text-slate-500">ID: ${u.id}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-slate-600">${escapeHtml(u.email)}</td>
            <td class="px-6 py-4 text-sm text-slate-600">${escapeHtml(u.department_name || 'Sin asignar')}</td>
            <td class="px-6 py-4">${statusBadge}</td>
            <td class="px-6 py-4 text-sm text-slate-600">${lastLogin}</td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end gap-2">
                <button onclick="editUser(${u.id})" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-[#23AAC5] hover:text-[#115c6c] hover:bg-[#23AAC5]/5 rounded-lg transition-colors">
                  <i class="iconoir-edit-pencil"></i>
                  <span>Editar</span>
                </button>
                <button onclick="confirmDeleteUser(${u.id}, '${escapeHtml(u.first_name)} ${escapeHtml(u.last_name)}')" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                  <i class="iconoir-trash"></i>
                  <span>Eliminar</span>
                </button>
              </div>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Abrir modal crear
    document.getElementById('create-user-btn').addEventListener('click', () => {
      isEditMode = false;
      document.getElementById('modal-title').textContent = 'Nuevo usuario';
      document.getElementById('submit-text').textContent = 'Crear usuario';
      document.getElementById('user-form').reset();
      document.getElementById('user-id').value = '';
      document.getElementById('user-status').checked = true;
      document.getElementById('password-label').innerHTML = 'Contraseña *';
      document.getElementById('password-hint').textContent = 'Mínimo 8 caracteres';
      document.getElementById('user-password').required = true;
      document.getElementById('status-toggle-container').classList.add('hidden');
      document.getElementById('user-error').classList.add('hidden');
      document.getElementById('user-modal').classList.remove('hidden');
    });

    // Editar usuario
    window.editUser = function(userId) {
      const user = allUsers.find(u => u.id === userId);
      if (!user) return;
      
      isEditMode = true;
      document.getElementById('modal-title').textContent = 'Editar usuario';
      document.getElementById('submit-text').textContent = 'Guardar cambios';
      document.getElementById('user-id').value = user.id;
      document.getElementById('user-first-name').value = user.first_name;
      document.getElementById('user-last-name').value = user.last_name;
      document.getElementById('user-email').value = user.email;
      document.getElementById('user-department').value = user.department_id || '';
      document.getElementById('user-superadmin').checked = !!user.is_superadmin;
      document.getElementById('user-status').checked = user.status === 'active';
      document.getElementById('user-password').value = '';
      document.getElementById('user-password').required = false;
      document.getElementById('password-label').innerHTML = 'Nueva contraseña <span class="text-slate-400">(dejar vacío para mantener)</span>';
      document.getElementById('password-hint').textContent = 'Solo completar si deseas cambiar la contraseña';
      document.getElementById('status-toggle-container').classList.remove('hidden');
      document.getElementById('user-error').classList.add('hidden');
      document.getElementById('user-modal').classList.remove('hidden');
    };

    // Cerrar modal
    [document.getElementById('close-modal-btn'), document.getElementById('cancel-modal-btn')].forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('user-modal').classList.add('hidden');
      });
    });

    // Submit formulario
    document.getElementById('user-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const errorEl = document.getElementById('user-error');
      errorEl.classList.add('hidden');

      const userId = document.getElementById('user-id').value;
      const firstName = document.getElementById('user-first-name').value.trim();
      const lastName = document.getElementById('user-last-name').value.trim();
      const email = document.getElementById('user-email').value.trim();
      const departmentId = document.getElementById('user-department').value || null;
      const password = document.getElementById('user-password').value;
      const isSuperadmin = document.getElementById('user-superadmin').checked;
      const isActive = document.getElementById('user-status').checked;

      const submitBtn = e.target.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.textContent = isEditMode ? 'Guardando...' : 'Creando...';

      try {
        if (isEditMode) {
          await api('/api/admin/users/update.php', {
            method: 'POST',
            body: {
              id: parseInt(userId),
              first_name: firstName,
              last_name: lastName,
              email: email,
              department_id: departmentId,
              status: isActive ? 'active' : 'disabled',
              is_superadmin: isSuperadmin,
              new_password: password
            }
          });
        } else {
          await api('/api/admin/users/create.php', {
            method: 'POST',
            body: {
              first_name: firstName,
              last_name: lastName,
              email: email,
              password: password,
              department_id: departmentId,
              is_superadmin: isSuperadmin
            }
          });
        }

        document.getElementById('user-modal').classList.add('hidden');
        await loadUsers();
      } catch (err) {
        errorEl.textContent = err.message;
        errorEl.classList.remove('hidden');
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = isEditMode ? 'Guardar cambios' : 'Crear usuario';
      }
    });

    // Eliminar usuario
    let userToDelete = null;
    
    window.confirmDeleteUser = function(userId, userName) {
      userToDelete = userId;
      document.getElementById('delete-user-name').textContent = userName;
      document.getElementById('delete-modal').classList.remove('hidden');
    };

    document.getElementById('cancel-delete-btn').addEventListener('click', () => {
      document.getElementById('delete-modal').classList.add('hidden');
      userToDelete = null;
    });

    // Cerrar modal al hacer clic fuera
    document.getElementById('delete-modal').addEventListener('click', (e) => {
      if (e.target.id === 'delete-modal') {
        document.getElementById('delete-modal').classList.add('hidden');
        userToDelete = null;
      }
    });

    document.getElementById('confirm-delete-btn').addEventListener('click', async () => {
      if (!userToDelete) return;

      const btn = document.getElementById('confirm-delete-btn');
      btn.disabled = true;
      btn.textContent = 'Eliminando...';

      try {
        await api('/api/admin/users/delete.php', {
          method: 'POST',
          body: { id: userToDelete }
        });

        document.getElementById('delete-modal').classList.add('hidden');
        userToDelete = null;
        await loadUsers();
      } catch (err) {
        alert('Error al eliminar usuario: ' + err.message);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Sí, eliminar usuario';
      }
    });

    // Búsqueda y filtros
    document.getElementById('search-input').addEventListener('input', renderUsers);
    document.getElementById('status-filter').addEventListener('change', renderUsers);

    // Escape HTML
    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str;
      return div.innerHTML;
    }

    // Cargar datos al inicio
    loadUsers();
    loadDepartments();
  </script>
</body>
</html>
