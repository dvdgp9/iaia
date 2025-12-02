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
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Escribir contenido — Ebonia</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/iconoir-icons/iconoir@main/css/iconoir.css">
  <script>window.CSRF_TOKEN = '<?php echo $csrfToken; ?>';</script>
  <style>
    .gradient-brand {
      background: linear-gradient(135deg, #23AAC5 0%, #115c6c 100%);
    }
  </style>
</head>
<body class="bg-slate-50 min-h-screen">
  <!-- Header -->
  <header class="h-[60px] px-6 border-b border-slate-200 bg-white/95 backdrop-blur-sm flex items-center justify-between shadow-sm sticky top-0 z-10">
    <div class="flex items-center gap-4">
      <a href="/#gestures" class="flex items-center gap-2 text-slate-600 hover:text-[#23AAC5] transition-colors">
        <i class="iconoir-arrow-left text-lg"></i>
        <span class="text-sm font-medium">Volver a gestos</span>
      </a>
      <div class="h-6 w-px bg-slate-200"></div>
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-[#23AAC5] flex items-center justify-center text-white">
          <i class="iconoir-page-edit text-sm"></i>
        </div>
        <span class="font-semibold text-slate-800">Escribir contenido</span>
      </div>
    </div>
    
    <div class="flex items-center gap-3">
      <a href="/" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-lg transition-colors" title="Ir al chat">
        <i class="iconoir-chat-bubble text-xl"></i>
      </a>
    </div>
  </header>

  <!-- Main content -->
  <main class="max-w-4xl mx-auto p-6">
    <!-- Header del gesto -->
    <div class="flex items-center gap-4 mb-6">
      <div class="w-12 h-12 rounded-xl bg-[#23AAC5] flex items-center justify-center text-white shadow-lg">
        <i class="iconoir-page-edit text-xl"></i>
      </div>
      <div>
        <h1 class="text-xl font-bold text-slate-900">Escribir contenido</h1>
        <p class="text-sm text-slate-600">Genera artículos, posts de blog o notas de prensa</p>
      </div>
    </div>
    
    <!-- Formulario del gesto -->
    <form id="write-article-form" class="space-y-6 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
      
      <!-- PASO 1: Tipo de contenido -->
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-3">¿Qué tipo de contenido necesitas?</label>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <label class="cursor-pointer">
            <input type="radio" name="content-type" value="informativo" class="hidden peer" checked />
            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-[#23AAC5] peer-checked:bg-[#23AAC5]/10 hover:border-[#23AAC5]/60 transition-all h-full">
              <div class="flex items-center gap-2 mb-2">
                <i class="iconoir-journal-page text-xl text-[#115c6c]"></i>
                <span class="font-semibold text-slate-800">Artículo informativo</span>
              </div>
              <p class="text-xs text-slate-500">Noticias, actualidad, deportes, cultura. Contenido objetivo y directo.</p>
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="content-type" value="blog" class="hidden peer" />
            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-[#23AAC5] peer-checked:bg-[#23AAC5]/10 hover:border-[#23AAC5]/60 transition-all h-full">
              <div class="flex items-center gap-2 mb-2">
                <i class="iconoir-post text-xl text-[#115c6c]"></i>
                <span class="font-semibold text-slate-800">Post de blog</span>
              </div>
              <p class="text-xs text-slate-500">Optimizado para SEO, con palabras clave y estructura web.</p>
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="content-type" value="nota-prensa" class="hidden peer" />
            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-[#23AAC5] peer-checked:bg-[#23AAC5]/10 hover:border-[#23AAC5]/60 transition-all h-full">
              <div class="flex items-center gap-2 mb-2">
                <i class="iconoir-megaphone text-xl text-[#115c6c]"></i>
                <span class="font-semibold text-slate-800">Nota de prensa</span>
              </div>
              <p class="text-xs text-slate-500">Comunicados oficiales con estructura periodística profesional.</p>
            </div>
          </label>
        </div>
      </div>
      
      <!-- Línea de negocio (siempre visible) -->
      <div class="flex gap-4 items-center p-3 bg-slate-50 rounded-xl border border-slate-200">
        <label class="text-sm font-medium text-slate-700 whitespace-nowrap">Línea de negocio:</label>
        <div class="flex flex-wrap gap-2">
          <label class="cursor-pointer">
            <input type="radio" name="business-line" value="ebone" class="hidden peer" checked />
            <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-[#23AAC5] peer-checked:bg-[#23AAC5]/10 peer-checked:text-[#115c6c] hover:border-[#23AAC5]/60 transition-all font-medium">
              Grupo Ebone
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="business-line" value="cubofit" class="hidden peer" />
            <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-[#23AAC5] peer-checked:bg-[#23AAC5]/10 peer-checked:text-[#115c6c] hover:border-[#23AAC5]/60 transition-all font-medium">
              CUBOFIT
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="business-line" value="uniges" class="hidden peer" />
            <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-[#23AAC5] peer-checked:bg-[#23AAC5]/10 peer-checked:text-[#115c6c] hover:border-[#23AAC5]/60 transition-all font-medium">
              UNIGES-3
            </div>
          </label>
        </div>
      </div>
      
      <!-- ========== CAMPOS ARTÍCULO INFORMATIVO ========== -->
      <div id="fields-informativo" class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Tema del artículo</label>
          <input type="text" id="info-topic" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-all" placeholder="Ej: Nueva temporada de actividades acuáticas en los centros deportivos" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Categoría</label>
            <select id="info-category" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] transition-all bg-white">
              <option value="general">General</option>
              <option value="deportes">Deportes</option>
              <option value="cultura">Cultura</option>
              <option value="salud">Salud y bienestar</option>
              <option value="empresa">Corporativo</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Extensión</label>
            <select id="info-length" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] transition-all bg-white">
              <option value="300">Corto (~300 palabras)</option>
              <option value="500" selected>Medio (~500 palabras)</option>
              <option value="800">Largo (~800 palabras)</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Detalles adicionales <span class="font-normal text-slate-400">(opcional)</span></label>
          <textarea id="info-details" rows="2" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-all resize-none" placeholder="Información extra, datos concretos, enfoque deseado..."></textarea>
        </div>
      </div>
      
      <!-- ========== CAMPOS POST DE BLOG ========== -->
      <div id="fields-blog" class="hidden space-y-4">
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Tema del post</label>
          <input type="text" id="blog-topic" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-all" placeholder="Ej: 5 beneficios de hacer ejercicio por la mañana" />
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Palabras clave SEO <span class="font-normal text-slate-400">(separadas por comas)</span></label>
          <input type="text" id="blog-keywords" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-all" placeholder="Ej: ejercicio matutino, rutina fitness, salud, bienestar" />
        </div>
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
          <div class="flex items-center gap-2 text-emerald-700">
            <i class="iconoir-check-circle"></i>
            <span class="text-sm font-medium">Configuración SEO automática</span>
          </div>
          <p class="text-xs text-emerald-600 mt-1">600-1000 palabras • Estructura H2/H3 • Meta descripción • Intro con palabra clave • CTA final</p>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Instrucciones adicionales <span class="font-normal text-slate-400">(opcional)</span></label>
          <textarea id="blog-details" rows="2" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-all resize-none" placeholder="Tono específico, datos a incluir, llamada a la acción..."></textarea>
        </div>
      </div>
      
      <!-- ========== CAMPOS NOTA DE PRENSA ========== -->
      <div id="fields-nota-prensa" class="hidden space-y-4">
        <!-- Tipo de anuncio -->
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Tipo de anuncio</label>
          <div class="flex flex-wrap gap-2">
            <label class="cursor-pointer">
              <input type="radio" name="press-type" value="lanzamiento" class="hidden peer" checked />
              <div class="px-3 py-2 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-[#23AAC5] peer-checked:bg-[#23AAC5]/10 hover:border-[#23AAC5]/60 transition-all flex items-center gap-1">
                <i class="iconoir-send-diagonal text-sm text-[#115c6c]"></i>
                <span>Lanzamiento</span>
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="press-type" value="evento" class="hidden peer" />
              <div class="px-3 py-2 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-[#23AAC5] peer-checked:bg-[#23AAC5]/10 hover:border-[#23AAC5]/60 transition-all flex items-center gap-1">
                <i class="iconoir-calendar text-sm text-[#115c6c]"></i>
                <span>Evento</span>
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="press-type" value="nombramiento" class="hidden peer" />
              <div class="px-3 py-2 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-[#23AAC5] peer-checked:bg-[#23AAC5]/10 hover:border-[#23AAC5]/60 transition-all flex items-center gap-1">
                <i class="iconoir-user-star text-sm text-[#115c6c]"></i>
                <span>Nombramiento</span>
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="press-type" value="convenio" class="hidden peer" />
              <div class="px-3 py-2 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-[#23AAC5] peer-checked:bg-[#23AAC5]/10 hover:border-[#23AAC5]/60 transition-all flex items-center gap-1">
                <i class="iconoir-community text-sm text-[#115c6c]"></i>
                <span>Convenio</span>
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="press-type" value="premio" class="hidden peer" />
              <div class="px-3 py-2 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-[#23AAC5] peer-checked:bg-[#23AAC5]/10 hover:border-[#23AAC5]/60 transition-all flex items-center gap-1">
                <i class="iconoir-medal text-sm text-[#115c6c]"></i>
                <span>Premio/Reconocimiento</span>
              </div>
            </label>
          </div>
        </div>
        
        <!-- Datos básicos con placeholders informativos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">¿Qué ocurre? <span class="text-red-500">*</span></label>
            <input type="text" id="press-what" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-all" placeholder="El hecho o noticia principal" required />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">¿Quién lo hace?</label>
            <input type="text" id="press-who" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-all" placeholder="Persona, empresa, organización..." />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">¿Cuándo?</label>
            <input type="text" id="press-when" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-all" placeholder="Fecha, periodo, momento..." />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">¿Dónde?</label>
            <input type="text" id="press-where" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-all" placeholder="Ubicación, lugar, ámbito..." />
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">¿Por qué?</label>
            <textarea id="press-why" rows="2" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-all resize-none" placeholder="Motivo, causa, contexto (solo información segura y contrastada)"></textarea>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Información adicional <span class="font-normal text-slate-400">(opcional)</span></label>
            <textarea id="press-purpose" rows="2" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-all resize-none" placeholder="Datos complementarios ya confirmados. No añadas nada que no tengas claro."></textarea>
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Declaración o cita textual <span class="font-normal text-slate-400">(opcional)</span></label>
          <div class="flex gap-2">
            <input type="text" id="press-quote-author" class="w-1/3 border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] transition-all" placeholder="Autor de la cita" />
            <input type="text" id="press-quote-text" class="flex-1 border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] transition-all" placeholder="Texto de la declaración..." />
          </div>
        </div>
        <p class="text-xs text-slate-500 italic">💡 Si dejas campos vacíos, el sistema generará la nota con la información disponible. Los campos obligatorios están marcados con *. La IA no debe inventar datos (fechas, nombres, cargos, cifras, etc.); revisa siempre que todo sea correcto.</p>
      </div>
      
      <!-- Botón generar -->
      <div class="flex justify-end pt-2 border-t border-slate-100">
        <button type="submit" id="generate-article-btn" class="px-6 py-3 bg-[#23AAC5] hover:bg-[#115c6c] text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
          <i class="iconoir-sparks"></i>
          <span>Generar contenido</span>
        </button>
      </div>
    </form>
    
    <!-- Resultado (oculto inicialmente) -->
    <div id="article-result" class="hidden mt-8 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-slate-800">Contenido generado</h2>
        <div class="flex gap-2">
          <button id="copy-article-btn" class="px-3 py-1.5 text-sm text-slate-600 hover:text-[#115c6c] hover:bg-[#23AAC5]/10 rounded-lg transition-colors flex items-center gap-1.5">
            <i class="iconoir-copy"></i> Copiar
          </button>
          <button id="regenerate-article-btn" class="px-3 py-1.5 text-sm text-slate-600 hover:text-[#115c6c] hover:bg-[#23AAC5]/10 rounded-lg transition-colors flex items-center gap-1.5">
            <i class="iconoir-refresh"></i> Regenerar
          </button>
        </div>
      </div>
      <div id="article-content" class="prose prose-slate max-w-none"></div>
    </div>
    
    <!-- Loading -->
    <div id="article-loading" class="hidden mt-8 text-center py-12">
      <div class="inline-flex items-center gap-3 px-6 py-4 bg-[#23AAC5]/10 rounded-xl">
        <div class="w-5 h-5 border-2 border-[#23AAC5] border-t-transparent rounded-full animate-spin"></div>
        <span class="text-[#115c6c] font-medium">Generando contenido...</span>
      </div>
    </div>
  </main>

  <script src="/assets/js/gesture-write-article.js"></script>
</body>
</html>
