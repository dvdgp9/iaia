<?php
require_once __DIR__ . '/../../src/App/bootstrap.php';

use App\Session;

Session::start();
$user = Session::user();
if (!$user) {
    header('Location: /login.php');
    exit;
}
$csrfToken = $_SESSION['csrf_token'] ?? '';
$activeTab = 'voices';

// Configuración del header unificado
$headerBackUrl = '/';
$headerBackText = 'Inicio';
$headerTitle = 'Voces';
$headerSubtitle = 'Asistentes especializados';
$headerIcon = 'iconoir-voice-square';
$headerIconColor = 'from-violet-500 to-purple-600';
?><!DOCTYPE html>
<html lang="es">
<?php include __DIR__ . '/../includes/head.php'; ?>
<body class="bg-mesh text-slate-900 overflow-hidden">
  <div class="min-h-screen flex h-screen">
    <?php include __DIR__ . '/../includes/left-tabs.php'; ?>
    
    <!-- Main content -->
    <main class="flex-1 flex flex-col overflow-hidden">
      <?php include __DIR__ . '/../includes/header-unified.php'; ?>

      <!-- Content area -->
      <div class="flex-1 overflow-auto p-4 lg:p-6 pb-20 lg:pb-6">
        <div class="max-w-6xl mx-auto">
          
          <!-- Hero section -->
          <div class="text-center mb-6 lg:mb-10">
            <div class="w-14 h-14 lg:w-20 lg:h-20 rounded-2xl lg:rounded-3xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center mx-auto mb-4 lg:mb-6 shadow-xl animate-float">
              <i class="iconoir-voice-square text-2xl lg:text-4xl text-white"></i>
            </div>
            <h1 class="text-2xl lg:text-3xl font-bold text-slate-900 mb-2 lg:mb-3">Voces especializadas</h1>
            <p class="text-sm lg:text-base text-slate-600 max-w-2xl mx-auto px-4 lg:px-0">
              Cada voz es un asistente experto en un dominio específico, con conocimiento profundo y acceso a documentación especializada.
            </p>
          </div>

          <!-- Voces grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 mb-8">
            
            <!-- Voz: Lex -->
            <a href="/voices/lex.php" class="glass-strong rounded-3xl p-6 border border-slate-200/50 card-hover block">
              <div class="flex items-start gap-4 mb-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center text-white font-bold text-xl shadow-lg">
                  L
                </div>
                <div class="flex-1">
                  <h3 class="text-lg font-bold text-slate-900 mb-1">Lex</h3>
                  <p class="text-sm text-slate-500">Asistente Legal</p>
                </div>
                <span class="px-2 py-1 text-xs bg-emerald-100 text-emerald-700 rounded-full font-medium">Activo</span>
              </div>
              
              <p class="text-sm text-slate-600 mb-4">
                Experto en convenios colectivos, normativas laborales y documentación legal del Grupo Ebone. Responde consultas precisas sobre derechos, permisos y procedimientos.
              </p>
              
              <div class="flex items-center justify-between text-xs text-slate-400 pt-4 border-t border-slate-200/50">
                <div class="flex items-center gap-1">
                  <i class="iconoir-folder"></i>
                  <span>Documentación legal</span>
                </div>
                <div class="flex items-center gap-2 text-rose-600 font-medium">
                  <span>Usar voz</span>
                  <i class="iconoir-arrow-right"></i>
                </div>
              </div>
            </a>

            <!-- Voz: Cubo (próximamente) -->
            <div class="glass-strong rounded-3xl p-6 border border-slate-200/50 opacity-60">
              <div class="flex items-start gap-4 mb-4">
                <div class="w-14 h-14 rounded-2xl bg-slate-200 flex items-center justify-center text-slate-400 font-bold text-xl">
                  C
                </div>
                <div class="flex-1">
                  <h3 class="text-lg font-bold text-slate-500 mb-1">Cubo</h3>
                  <p class="text-sm text-slate-400">Asistente CUBOFIT</p>
                </div>
                <span class="px-2 py-1 text-xs bg-slate-100 text-slate-400 rounded-full">Soon</span>
              </div>
              
              <p class="text-sm text-slate-400 mb-4">
                Especialista en productos fitness, equipamiento deportivo y especificaciones técnicas de CUBOFIT.
              </p>
              
              <div class="flex items-center justify-between text-xs text-slate-300 pt-4 border-t border-slate-200/50">
                <div class="flex items-center gap-1">
                  <i class="iconoir-folder"></i>
                  <span>Próximamente</span>
                </div>
              </div>
            </div>

            <!-- Voz: Uniges (próximamente) -->
            <div class="glass-strong rounded-3xl p-6 border border-slate-200/50 opacity-60">
              <div class="flex items-start gap-4 mb-4">
                <div class="w-14 h-14 rounded-2xl bg-slate-200 flex items-center justify-center text-slate-400 font-bold text-xl">
                  U
                </div>
                <div class="flex-1">
                  <h3 class="text-lg font-bold text-slate-500 mb-1">Uniges</h3>
                  <p class="text-sm text-slate-400">Asistente UNIGES-3</p>
                </div>
                <span class="px-2 py-1 text-xs bg-slate-100 text-slate-400 rounded-full">Soon</span>
              </div>
              
              <p class="text-sm text-slate-400 mb-4">
                Experto en gestión de instalaciones deportivas, servicios municipales y operaciones de UNIGES-3.
              </p>
              
              <div class="flex items-center justify-between text-xs text-slate-300 pt-4 border-t border-slate-200/50">
                <div class="flex items-center gap-1">
                  <i class="iconoir-folder"></i>
                  <span>Próximamente</span>
                </div>
              </div>
            </div>

          </div>

          <!-- Info section -->
          <div class="glass rounded-3xl p-6 border border-slate-200/50">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 rounded-2xl bg-violet-100 flex items-center justify-center flex-shrink-0">
                <i class="iconoir-info-circle text-2xl text-violet-600"></i>
              </div>
              <div>
                <h3 class="font-semibold text-slate-800 mb-2">¿Qué son las voces?</h3>
                <p class="text-sm text-slate-600 mb-3">
                  Las voces son asistentes de IA especializados, cada uno con conocimiento profundo en un área específica del Grupo Ebone. A diferencia del chat general, cada voz tiene acceso a documentación especializada y está optimizada para responder consultas precisas en su dominio.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                  <div class="flex items-start gap-2">
                    <i class="iconoir-check text-violet-600 mt-0.5"></i>
                    <div>
                      <div class="font-medium text-sm text-slate-700">Conocimiento especializado</div>
                      <div class="text-xs text-slate-500">Cada voz domina su área</div>
                    </div>
                  </div>
                  <div class="flex items-start gap-2">
                    <i class="iconoir-check text-violet-600 mt-0.5"></i>
                    <div>
                      <div class="font-medium text-sm text-slate-700">Documentación específica</div>
                      <div class="text-xs text-slate-500">Acceso a fuentes verificadas</div>
                    </div>
                  </div>
                  <div class="flex items-start gap-2">
                    <i class="iconoir-check text-violet-600 mt-0.5"></i>
                    <div>
                      <div class="font-medium text-sm text-slate-700">Respuestas precisas</div>
                      <div class="text-xs text-slate-500">Con citas y referencias</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>
  </div>
  
  <!-- Bottom Navigation (móvil) -->
  <?php include __DIR__ . '/../includes/bottom-nav.php'; ?>
</body>
</html>
