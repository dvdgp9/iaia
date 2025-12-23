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
$pageTitle = 'Podcast desde artículo — Ebonia';
$activeTab = 'gestures';
?><!DOCTYPE html>
<html lang="es">
<?php include __DIR__ . '/../includes/head.php'; ?>
<body class="bg-mesh text-slate-900 overflow-hidden">
  <style>
    .audio-player-dark {
      background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
    }
    @keyframes pulse-wave {
      0%, 100% { transform: scaleY(0.5); }
      50% { transform: scaleY(1); }
    }
    .wave-bar {
      animation: pulse-wave 1s ease-in-out infinite;
    }
    .wave-bar:nth-child(2) { animation-delay: 0.1s; }
    .wave-bar:nth-child(3) { animation-delay: 0.2s; }
    .wave-bar:nth-child(4) { animation-delay: 0.3s; }
    .wave-bar:nth-child(5) { animation-delay: 0.4s; }
  </style>
  <div class="min-h-screen flex h-screen">
    <?php include __DIR__ . '/../includes/left-tabs.php'; ?>
    
    <!-- Sidebar de historial -->
    <aside id="history-sidebar" class="w-72 glass-strong border-r border-slate-200/50 flex flex-col shrink-0">
      <div class="p-4 border-b border-slate-200/50">
        <div class="flex items-center justify-between">
          <h2 class="font-semibold text-slate-800 flex items-center gap-2">
            <i class="iconoir-clock text-violet-500"></i>
            Historial
          </h2>
          <button id="new-podcast-sidebar-btn" class="p-1.5 text-slate-400 hover:text-violet-500 hover:bg-violet-50 rounded-lg transition-smooth" title="Nuevo podcast">
            <i class="iconoir-plus text-lg"></i>
          </button>
        </div>
      </div>
      
      <div id="history-list" class="flex-1 overflow-auto">
        <div class="p-4 text-center text-slate-400 text-sm">
          <i class="iconoir-refresh animate-spin"></i>
          Cargando...
        </div>
      </div>
    </aside>
    
    <!-- Main content area -->
    <main class="flex-1 flex flex-col overflow-hidden">
      <!-- Header del gesto -->
      <header class="h-[60px] px-6 border-b border-slate-200/50 glass-strong flex items-center justify-between shadow-sm shrink-0">
        <div class="flex items-center gap-4">
          <a href="/gestos/" class="flex items-center gap-2 text-slate-600 hover:text-violet-600 transition-smooth">
            <i class="iconoir-arrow-left text-lg"></i>
            <span class="text-sm font-medium">Todos los gestos</span>
          </a>
          <div class="h-6 w-px bg-slate-200"></div>
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center text-white shadow-md">
              <i class="iconoir-podcast text-sm"></i>
            </div>
            <span class="font-semibold text-slate-800">Podcast desde artículo</span>
          </div>
        </div>
      </header>

      <!-- Two-column layout -->
      <div class="flex-1 flex overflow-hidden">
        
        <!-- LEFT: Configuration panel -->
        <div class="w-[420px] shrink-0 border-r border-slate-200/50 overflow-auto p-5">
          <form id="podcast-form" class="space-y-5">
            
            <!-- Fuente del artículo -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                <i class="iconoir-link text-violet-500 mr-1"></i>
                Fuente del artículo
              </label>
              
              <!-- Tabs -->
              <div class="flex gap-2 mb-3">
                <button type="button" data-tab="url" class="tab-btn active px-3 py-1.5 text-xs font-medium rounded-lg transition-all bg-violet-100 text-violet-700">
                  <i class="iconoir-link mr-1"></i> URL
                </button>
                <button type="button" data-tab="text" class="tab-btn px-3 py-1.5 text-xs font-medium rounded-lg transition-all bg-slate-100 text-slate-600 hover:bg-slate-200">
                  <i class="iconoir-text mr-1"></i> Texto
                </button>
                <button type="button" data-tab="pdf" class="tab-btn px-3 py-1.5 text-xs font-medium rounded-lg transition-all bg-slate-100 text-slate-600 hover:bg-slate-200">
                  <i class="iconoir-page mr-1"></i> PDF
                </button>
              </div>

              <!-- URL Input -->
              <div id="tab-url" class="tab-content">
                <input type="url" id="article-url" 
                       class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all text-sm"
                       placeholder="https://ejemplo.com/articulo-interesante" />
                <p class="text-xs text-slate-500 mt-1">Pega la URL de cualquier artículo web</p>
              </div>

              <!-- Text Input -->
              <div id="tab-text" class="tab-content hidden">
                <textarea id="article-text" rows="6"
                          class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all resize-none text-sm"
                          placeholder="Pega aquí el texto del artículo..."></textarea>
                <p class="text-xs text-slate-500 mt-1">Copia y pega directamente el contenido</p>
              </div>

              <!-- PDF Input -->
              <div id="tab-pdf" class="tab-content hidden">
                <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-violet-400 hover:bg-violet-50/50 transition-all">
                  <i class="iconoir-upload text-xl text-slate-400 mb-1"></i>
                  <span class="text-xs text-slate-500">Arrastra un PDF o haz clic</span>
                  <input type="file" id="article-pdf" accept=".pdf" class="hidden" />
                </label>
                <p id="pdf-filename" class="text-xs text-slate-500 mt-1 hidden"></p>
              </div>
            </div>
            
            <!-- Botón generar -->
            <button type="submit" id="generate-btn" class="w-full py-3 bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-600 hover:to-fuchsia-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
              <i class="iconoir-sparks"></i>
              <span>Generar Podcast</span>
            </button>
            
            <!-- Progress (dentro del form, debajo del botón) -->
            <div id="progress-panel" class="hidden bg-violet-50 rounded-xl p-4 border border-violet-200">
              <div class="flex items-center gap-3">
                <div class="flex gap-0.5">
                  <div class="w-1 h-6 bg-violet-500 rounded-full wave-bar"></div>
                  <div class="w-1 h-6 bg-violet-500 rounded-full wave-bar"></div>
                  <div class="w-1 h-6 bg-violet-500 rounded-full wave-bar"></div>
                  <div class="w-1 h-6 bg-violet-500 rounded-full wave-bar"></div>
                  <div class="w-1 h-6 bg-violet-500 rounded-full wave-bar"></div>
                </div>
                <div>
                  <p id="progress-text" class="text-sm font-medium text-violet-700">Procesando...</p>
                  <p id="progress-detail" class="text-xs text-violet-500">Esto puede tardar 1-2 minutos</p>
                </div>
              </div>
            </div>
            
            <!-- Error (dentro del form) -->
            <div id="error-panel" class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
              <div class="flex items-start gap-2">
                <i class="iconoir-warning-triangle text-red-500"></i>
                <div>
                  <p class="text-sm font-medium text-red-800">Error</p>
                  <p id="error-message" class="text-xs text-red-600 mt-0.5"></p>
                </div>
              </div>
            </div>
          </form>
        </div>
        
        <!-- RIGHT: Result panel -->
        <div class="flex-1 overflow-auto p-6 bg-slate-50/30">
          
          <!-- Estado inicial: placeholder -->
          <div id="result-placeholder" class="h-full flex items-center justify-center">
            <div class="text-center max-w-sm">
              <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-violet-500/20 to-fuchsia-600/20 flex items-center justify-center mx-auto mb-5">
                <i class="iconoir-podcast text-4xl text-violet-400"></i>
              </div>
              <h3 class="text-lg font-semibold text-slate-700 mb-2">Tu podcast aparecerá aquí</h3>
              <p class="text-sm text-slate-500">Pega una URL o texto a la izquierda y pulsa "Generar Podcast"</p>
            </div>
          </div>
          
          <!-- Resultado -->
          <div id="podcast-result" class="hidden space-y-4">
            
            <!-- Audio Player -->
            <div class="audio-player-dark rounded-2xl p-5 text-white">
              <div class="flex items-start gap-4 mb-4">
                <div class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center shrink-0">
                  <i class="iconoir-podcast text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <h3 id="podcast-title" class="font-semibold text-base truncate">Podcast generado</h3>
                  <p id="podcast-summary" class="text-sm text-white/70 line-clamp-2 mt-1"></p>
                </div>
              </div>
              
              <audio id="audio-player" controls class="w-full mb-3" style="filter: invert(1) hue-rotate(180deg);"></audio>
              
              <div class="flex items-center justify-between text-sm">
                <span id="podcast-duration" class="text-white/60 text-xs"></span>
                <button id="download-btn" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 rounded-lg transition-colors flex items-center gap-2 text-xs">
                  <i class="iconoir-download"></i> Descargar MP3
                </button>
              </div>
            </div>

            <!-- Script Section -->
            <details class="glass-strong rounded-xl border border-slate-200/50 overflow-hidden">
              <summary class="px-4 py-3 cursor-pointer hover:bg-slate-50 transition-colors flex items-center gap-2 text-sm">
                <i class="iconoir-page text-violet-500"></i>
                <span class="font-medium text-slate-700">Ver guion del podcast</span>
              </summary>
              <div class="px-4 pb-4 pt-2">
                <pre id="podcast-script" class="text-xs text-slate-600 whitespace-pre-wrap font-sans leading-relaxed max-h-80 overflow-y-auto"></pre>
              </div>
            </details>
            
          </div>
        </div>
        
      </div><!-- /two-column layout -->
    </main>
  </div><!-- /main container -->

  <script src="/assets/js/gesture-podcast.js"></script>
</body>
</html>
