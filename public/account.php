<?php
require_once __DIR__ . '/../src/App/bootstrap.php';
require_once __DIR__ . '/../src/Auth/AuthService.php';

use App\Session;
use Auth\AuthService;

Session::start();
$user = Session::user();
if (!$user) {
    header('Location: /login.php');
    exit;
}
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mi cuenta — Ebonia</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/iconoir-icons/iconoir@main/css/iconoir.css">
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">
  <div class="max-w-4xl mx-auto p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <a href="/" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 transition-colors mb-3">
          <i class="iconoir-arrow-left"></i>
          <span class="text-sm">Volver al chat</span>
        </a>
        <h1 class="text-3xl font-bold text-slate-800">Mi cuenta</h1>
      </div>
      <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
        <?php 
          $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
          echo htmlspecialchars($initials);
        ?>
      </div>
    </div>

    <!-- Perfil -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-slate-800">Información personal</h2>
        <button id="edit-profile-btn" class="text-sm text-violet-600 hover:text-violet-700 font-medium flex items-center gap-1">
          <i class="iconoir-edit-pencil"></i>
          <span>Editar</span>
        </button>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="text-xs font-medium text-slate-500 uppercase tracking-wider">Nombre</label>
          <div class="mt-1 text-slate-800 font-medium"><?php echo htmlspecialchars($user['first_name']); ?></div>
        </div>
        <div>
          <label class="text-xs font-medium text-slate-500 uppercase tracking-wider">Apellidos</label>
          <div class="mt-1 text-slate-800 font-medium"><?php echo htmlspecialchars($user['last_name']); ?></div>
        </div>
        <div>
          <label class="text-xs font-medium text-slate-500 uppercase tracking-wider">Email</label>
          <div class="mt-1 text-slate-800 font-medium"><?php echo htmlspecialchars($user['email']); ?></div>
        </div>
        <div>
          <label class="text-xs font-medium text-slate-500 uppercase tracking-wider">Estado</label>
          <div class="mt-1">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
              <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
              Activo
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Preferencias -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
      <h2 class="text-lg font-semibold text-slate-800 mb-6">Preferencias</h2>
      
      <div class="space-y-4">
        <!-- Tema -->
        <div class="flex items-center justify-between py-3 border-b border-slate-100">
          <div>
            <div class="font-medium text-slate-800">Tema de interfaz</div>
            <div class="text-sm text-slate-500 mt-0.5">Personaliza la apariencia de la plataforma</div>
          </div>
          <select class="px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:border-violet-400 transition-colors">
            <option value="light">Claro</option>
            <option value="dark">Oscuro</option>
            <option value="auto">Automático</option>
          </select>
        </div>

        <!-- Densidad -->
        <div class="flex items-center justify-between py-3 border-b border-slate-100">
          <div>
            <div class="font-medium text-slate-800">Densidad de información</div>
            <div class="text-sm text-slate-500 mt-0.5">Espaciado de elementos en la interfaz</div>
          </div>
          <select class="px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:border-violet-400 transition-colors">
            <option value="comfortable">Cómodo</option>
            <option value="compact">Compacto</option>
          </select>
        </div>

        <!-- Notificaciones -->
        <div class="flex items-center justify-between py-3">
          <div>
            <div class="font-medium text-slate-800">Notificaciones</div>
            <div class="text-sm text-slate-500 mt-0.5">Recibir avisos sobre actividad importante</div>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" checked class="sr-only peer">
            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-violet-100 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
          </label>
        </div>
      </div>

      <div class="mt-6 pt-6 border-t border-slate-100">
        <button class="px-4 py-2 bg-violet-600 text-white rounded-lg font-medium hover:bg-violet-700 transition-colors text-sm">
          Guardar preferencias
        </button>
        <p class="text-xs text-slate-500 mt-3">
          <i class="iconoir-info-circle"></i> 
          Algunas preferencias estarán disponibles próximamente
        </p>
      </div>
    </div>

    <!-- Seguridad -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
      <h2 class="text-lg font-semibold text-slate-800 mb-6">Seguridad</h2>
      
      <div class="space-y-4">
        <div class="flex items-center justify-between py-3 border-b border-slate-100">
          <div>
            <div class="font-medium text-slate-800">Contraseña</div>
            <div class="text-sm text-slate-500 mt-0.5">Última actualización: Nunca</div>
          </div>
          <button class="px-4 py-2 text-sm font-medium text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
            Cambiar contraseña
          </button>
        </div>

        <div class="flex items-center justify-between py-3">
          <div>
            <div class="font-medium text-slate-800">Sesiones activas</div>
            <div class="text-sm text-slate-500 mt-0.5">Gestiona los dispositivos con acceso</div>
          </div>
          <button class="px-4 py-2 text-sm font-medium text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
            Ver sesiones
          </button>
        </div>
      </div>
    </div>

    <!-- Actividad -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
      <h2 class="text-lg font-semibold text-slate-800 mb-6">Actividad reciente</h2>
      
      <div class="space-y-4">
        <div class="flex items-start gap-3 py-3 border-b border-slate-100 last:border-0">
          <div class="h-10 w-10 rounded-lg bg-violet-50 flex items-center justify-center flex-shrink-0">
            <i class="iconoir-chat-bubble text-violet-600"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-medium text-slate-800">Conversaciones creadas</div>
            <div class="text-sm text-slate-500 mt-0.5">Esta semana: Próximamente</div>
          </div>
        </div>

        <div class="flex items-start gap-3 py-3 border-b border-slate-100 last:border-0">
          <div class="h-10 w-10 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
            <i class="iconoir-clock text-indigo-600"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-medium text-slate-800">Último acceso</div>
            <div class="text-sm text-slate-500 mt-0.5">
              <?php 
                if ($user['last_login_at']) {
                  $date = new DateTime($user['last_login_at']);
                  echo $date->format('d/m/Y H:i');
                } else {
                  echo 'Primera sesión';
                }
              ?>
            </div>
          </div>
        </div>

        <div class="flex items-start gap-3 py-3">
          <div class="h-10 w-10 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
            <i class="iconoir-check-circle text-emerald-600"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-medium text-slate-800">Cuenta creada</div>
            <div class="text-sm text-slate-500 mt-0.5">
              <?php 
                $created = new DateTime($user['created_at']);
                echo $created->format('d/m/Y');
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="mt-8 text-center text-sm text-slate-500">
      <p>© 2025 Grupo Ebone. Todos los derechos reservados.</p>
    </div>
  </div>

  <script>
    // Placeholder para futuras interacciones
    document.getElementById('edit-profile-btn')?.addEventListener('click', () => {
      alert('Próximamente: Edición de perfil');
    });

    // Toggle switches (placeholder)
    document.querySelectorAll('input[type="checkbox"]').forEach(toggle => {
      toggle.addEventListener('change', (e) => {
        console.log('Preferencia actualizada:', e.target.checked);
      });
    });

    // Selectores (placeholder)
    document.querySelectorAll('select').forEach(select => {
      select.addEventListener('change', (e) => {
        console.log('Preferencia actualizada:', e.target.value);
      });
    });
  </script>
</body>
</html>
