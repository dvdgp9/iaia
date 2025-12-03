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
$pageTitle = 'Gestos — Ebonia';
$activeTab = 'gestures';
?><!DOCTYPE html>
<html lang="es">
<?php include __DIR__ . '/../includes/head.php'; ?>
<body class="bg-mesh text-slate-900 overflow-hidden">
  <div class="min-h-screen flex h-screen">
    <?php include __DIR__ . '/../includes/left-tabs.php'; ?>
    
    <!-- Main content -->
    <main class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <header class="h-[60px] px-6 border-b border-slate-200/50 glass-strong flex items-center justify-between shadow-sm shrink-0">
        <div class="flex items-center gap-4">
          <a href="/" class="flex items-center gap-2 text-slate-600 hover:text-cyan-600 transition-smooth">
            <i class="iconoir-arrow-left text-lg"></i>
            <span class="text-sm font-medium">Inicio</span>
          </a>
          <div class="h-6 w-px bg-slate-200"></div>
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-500 to-teal-600 flex items-center justify-center text-white shadow-md">
              <i class="iconoir-magic-wand text-lg"></i>
            </div>
            <div>
              <span class="font-semibold text-slate-800">Gestos</span>
              <span class="text-xs text-slate-500 ml-2">Flujos automatizados</span>
            </div>
          </div>
        </div>
      </header>

      <!-- Content area -->
      <div class="flex-1 overflow-auto p-6">
        <div class="max-w-6xl mx-auto">
          
          <!-- Hero section -->
          <div class="text-center mb-10">
            <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-cyan-500 to-teal-600 flex items-center justify-center mx-auto mb-6 shadow-xl animate-float">
              <i class="iconoir-magic-wand text-4xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-slate-900 mb-3">Gestos</h1>
            <p class="text-base text-slate-600 max-w-2xl mx-auto">
              Flujos de trabajo automatizados para tareas comunes. Cada gesto te guía paso a paso para generar contenido de calidad.
            </p>
          </div>

          <!-- Gestos grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            
            <!-- Gesto: Escribir contenido -->
            <a href="/gestos/escribir-articulo.php" class="glass-strong rounded-3xl p-6 border border-slate-200/50 card-hover block">
              <div class="flex items-start gap-4 mb-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-500 to-teal-600 flex items-center justify-center text-white shadow-lg">
                  <i class="iconoir-page-edit text-2xl"></i>
                </div>
                <div class="flex-1">
                  <h3 class="text-lg font-bold text-slate-900 mb-1">Escribir contenido</h3>
                  <p class="text-sm text-slate-500">Artículos y blogs</p>
                </div>
                <span class="px-2 py-1 text-xs bg-emerald-100 text-emerald-700 rounded-full font-medium">Activo</span>
              </div>
              
              <p class="text-sm text-slate-600 mb-4">
                Genera artículos informativos, posts de blog o notas de prensa. Configura el tono, extensión y estilo que necesites.
              </p>
              
              <div class="flex items-center justify-between text-xs text-slate-400 pt-4 border-t border-slate-200/50">
                <div class="flex items-center gap-1">
                  <i class="iconoir-clock"></i>
                  <span>~3 min</span>
                </div>
                <div class="flex items-center gap-2 text-cyan-600 font-medium">
                  <span>Usar gesto</span>
                  <i class="iconoir-arrow-right"></i>
                </div>
              </div>
            </a>

            <!-- Gesto: Analizar documento (próximamente) -->
            <div class="glass-strong rounded-3xl p-6 border border-slate-200/50 opacity-60">
              <div class="flex items-start gap-4 mb-4">
                <div class="w-14 h-14 rounded-2xl bg-slate-200 flex items-center justify-center text-slate-400">
                  <i class="iconoir-search-window text-2xl"></i>
                </div>
                <div class="flex-1">
                  <h3 class="text-lg font-bold text-slate-500 mb-1">Analizar documento</h3>
                  <p class="text-sm text-slate-400">Extracción de información</p>
                </div>
                <span class="px-2 py-1 text-xs bg-slate-100 text-slate-400 rounded-full">Soon</span>
              </div>
              
              <p class="text-sm text-slate-400 mb-4">
                Sube un documento y obtén resúmenes, puntos clave o respuestas a preguntas específicas sobre el contenido.
              </p>
              
              <div class="flex items-center justify-between text-xs text-slate-300 pt-4 border-t border-slate-200/50">
                <div class="flex items-center gap-1">
                  <i class="iconoir-clock"></i>
                  <span>Próximamente</span>
                </div>
              </div>
            </div>

            <!-- Gesto: Generar email (próximamente) -->
            <div class="glass-strong rounded-3xl p-6 border border-slate-200/50 opacity-60">
              <div class="flex items-start gap-4 mb-4">
                <div class="w-14 h-14 rounded-2xl bg-slate-200 flex items-center justify-center text-slate-400">
                  <i class="iconoir-mail text-2xl"></i>
                </div>
                <div class="flex-1">
                  <h3 class="text-lg font-bold text-slate-500 mb-1">Generar email</h3>
                  <p class="text-sm text-slate-400">Comunicación profesional</p>
                </div>
                <span class="px-2 py-1 text-xs bg-slate-100 text-slate-400 rounded-full">Soon</span>
              </div>
              
              <p class="text-sm text-slate-400 mb-4">
                Crea emails profesionales a partir de una idea o contexto. Ajusta el tono para comunicación interna o externa.
              </p>
              
              <div class="flex items-center justify-between text-xs text-slate-300 pt-4 border-t border-slate-200/50">
                <div class="flex items-center gap-1">
                  <i class="iconoir-clock"></i>
                  <span>Próximamente</span>
                </div>
              </div>
            </div>

          </div>

          <!-- Info section -->
          <div class="glass rounded-3xl p-6 border border-slate-200/50">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 rounded-2xl bg-cyan-100 flex items-center justify-center flex-shrink-0">
                <i class="iconoir-info-circle text-2xl text-cyan-600"></i>
              </div>
              <div>
                <h3 class="font-semibold text-slate-800 mb-2">¿Qué son los gestos?</h3>
                <p class="text-sm text-slate-600 mb-3">
                  Los gestos son flujos de trabajo guiados que te ayudan a completar tareas complejas paso a paso. A diferencia del chat libre, cada gesto está optimizado para un objetivo específico y te pide solo la información necesaria.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                  <div class="flex items-start gap-2">
                    <i class="iconoir-check text-cyan-600 mt-0.5"></i>
                    <div>
                      <div class="font-medium text-sm text-slate-700">Guiado paso a paso</div>
                      <div class="text-xs text-slate-500">Sin complicaciones</div>
                    </div>
                  </div>
                  <div class="flex items-start gap-2">
                    <i class="iconoir-check text-cyan-600 mt-0.5"></i>
                    <div>
                      <div class="font-medium text-sm text-slate-700">Resultados consistentes</div>
                      <div class="text-xs text-slate-500">Calidad garantizada</div>
                    </div>
                  </div>
                  <div class="flex items-start gap-2">
                    <i class="iconoir-check text-cyan-600 mt-0.5"></i>
                    <div>
                      <div class="font-medium text-sm text-slate-700">Historial guardado</div>
                      <div class="text-xs text-slate-500">Reutiliza y edita</div>
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
</body>
</html>
