<?php
require_once __DIR__ . '/../../src/App/bootstrap.php';
use App\Session;
Session::start();
$user = Session::user();
if (!$user) {
    header('Location: /login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Podcast desde artículo - EbonIA</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/iconoir-icons/iconoir@main/css/iconoir.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Inter', sans-serif; }
    .gradient-text {
      background: linear-gradient(135deg, #8b5cf6 0%, #d946ef 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .audio-player {
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
</head>
<body class="bg-gradient-to-br from-slate-50 via-violet-50/30 to-fuchsia-50/20 min-h-screen">
  
  <!-- Header -->
  <header class="bg-white/80 backdrop-blur-sm border-b border-slate-200/50 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <a href="/gestos/" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
          <i class="iconoir-arrow-left text-slate-600"></i>
        </a>
        <div>
          <h1 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <i class="iconoir-podcast text-violet-600"></i>
            Podcast desde artículo
          </h1>
          <p class="text-xs text-slate-500">Convierte cualquier artículo en un podcast con IA</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-sm text-slate-600"><?= htmlspecialchars($user['name'] ?? $user['email']) ?></span>
      </div>
    </div>
  </header>

  <!-- Main content -->
  <main class="max-w-4xl mx-auto px-4 py-8">
    
    <!-- Input Section -->
    <section id="input-section" class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-6 mb-6">
      <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
        <i class="iconoir-link text-violet-500"></i>
        Fuente del artículo
      </h2>
      
      <!-- Tabs -->
      <div class="flex gap-2 mb-4">
        <button type="button" data-tab="url" class="tab-btn active px-4 py-2 text-sm font-medium rounded-lg transition-all bg-violet-100 text-violet-700">
          <i class="iconoir-link mr-1"></i> URL
        </button>
        <button type="button" data-tab="text" class="tab-btn px-4 py-2 text-sm font-medium rounded-lg transition-all bg-slate-100 text-slate-600 hover:bg-slate-200">
          <i class="iconoir-text mr-1"></i> Texto
        </button>
        <button type="button" data-tab="pdf" class="tab-btn px-4 py-2 text-sm font-medium rounded-lg transition-all bg-slate-100 text-slate-600 hover:bg-slate-200">
          <i class="iconoir-page mr-1"></i> PDF
        </button>
      </div>

      <!-- URL Input -->
      <div id="tab-url" class="tab-content">
        <input type="url" id="article-url" 
               class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all"
               placeholder="https://ejemplo.com/articulo-interesante" />
        <p class="text-xs text-slate-500 mt-2">Pega la URL de cualquier artículo web</p>
      </div>

      <!-- Text Input -->
      <div id="tab-text" class="tab-content hidden">
        <textarea id="article-text" rows="8"
                  class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all resize-none"
                  placeholder="Pega aquí el texto del artículo..."></textarea>
        <p class="text-xs text-slate-500 mt-2">Copia y pega directamente el contenido del artículo</p>
      </div>

      <!-- PDF Input -->
      <div id="tab-pdf" class="tab-content hidden">
        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-violet-400 hover:bg-violet-50/50 transition-all">
          <i class="iconoir-upload text-2xl text-slate-400 mb-2"></i>
          <span class="text-sm text-slate-500">Arrastra un PDF o haz clic para seleccionar</span>
          <input type="file" id="article-pdf" accept=".pdf" class="hidden" />
        </label>
        <p id="pdf-filename" class="text-xs text-slate-500 mt-2 hidden"></p>
      </div>

      <!-- Generate Button -->
      <button type="button" id="generate-btn" 
              class="w-full mt-6 py-3 bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-600 hover:to-fuchsia-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
        <i class="iconoir-sparks"></i>
        <span>Generar Podcast</span>
      </button>
    </section>

    <!-- Progress Section -->
    <section id="progress-section" class="hidden bg-white rounded-2xl shadow-sm border border-slate-200/50 p-6 mb-6">
      <div class="flex items-center gap-4">
        <div class="flex gap-1">
          <div class="w-1 h-8 bg-violet-500 rounded-full wave-bar"></div>
          <div class="w-1 h-8 bg-violet-500 rounded-full wave-bar"></div>
          <div class="w-1 h-8 bg-violet-500 rounded-full wave-bar"></div>
          <div class="w-1 h-8 bg-violet-500 rounded-full wave-bar"></div>
          <div class="w-1 h-8 bg-violet-500 rounded-full wave-bar"></div>
        </div>
        <div>
          <p id="progress-text" class="text-sm font-medium text-slate-700">Procesando artículo...</p>
          <p id="progress-detail" class="text-xs text-slate-500">Esto puede tardar 1-2 minutos</p>
        </div>
      </div>
    </section>

    <!-- Result Section -->
    <section id="result-section" class="hidden">
      
      <!-- Audio Player -->
      <div class="audio-player rounded-2xl p-6 mb-6 text-white">
        <div class="flex items-start gap-4 mb-4">
          <div class="w-16 h-16 bg-white/10 rounded-xl flex items-center justify-center shrink-0">
            <i class="iconoir-podcast text-3xl"></i>
          </div>
          <div class="flex-1 min-w-0">
            <h3 id="podcast-title" class="font-semibold text-lg truncate">Podcast generado</h3>
            <p id="podcast-summary" class="text-sm text-white/70 line-clamp-2"></p>
          </div>
        </div>
        
        <audio id="audio-player" controls class="w-full mb-4" style="filter: invert(1) hue-rotate(180deg);"></audio>
        
        <div class="flex items-center justify-between text-sm">
          <span id="podcast-duration" class="text-white/60"></span>
          <div class="flex gap-2">
            <button id="download-btn" class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg transition-colors flex items-center gap-2">
              <i class="iconoir-download"></i> Descargar
            </button>
          </div>
        </div>
      </div>

      <!-- Script Section -->
      <details class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
        <summary class="px-6 py-4 cursor-pointer hover:bg-slate-50 transition-colors flex items-center gap-2">
          <i class="iconoir-page text-violet-500"></i>
          <span class="font-medium text-slate-700">Ver guion del podcast</span>
        </summary>
        <div class="px-6 pb-6 pt-2">
          <pre id="podcast-script" class="text-sm text-slate-600 whitespace-pre-wrap font-sans leading-relaxed max-h-96 overflow-y-auto"></pre>
        </div>
      </details>

      <!-- New Podcast Button -->
      <button type="button" id="new-podcast-btn" 
              class="w-full mt-6 py-3 border-2 border-violet-200 text-violet-600 font-semibold rounded-xl hover:bg-violet-50 transition-all flex items-center justify-center gap-2">
        <i class="iconoir-plus"></i>
        <span>Crear otro podcast</span>
      </button>
    </section>

    <!-- Error Section -->
    <section id="error-section" class="hidden bg-red-50 border border-red-200 rounded-2xl p-6 mb-6">
      <div class="flex items-start gap-3">
        <i class="iconoir-warning-triangle text-red-500 text-xl"></i>
        <div>
          <h3 class="font-medium text-red-800">Error al generar el podcast</h3>
          <p id="error-message" class="text-sm text-red-600 mt-1"></p>
        </div>
      </div>
      <button type="button" id="retry-btn" 
              class="mt-4 px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 font-medium rounded-lg transition-colors">
        Intentar de nuevo
      </button>
    </section>

  </main>

  <script src="/assets/js/gesture-podcast.js"></script>
</body>
</html>
