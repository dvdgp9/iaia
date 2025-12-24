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
$pageTitle = 'Escribir contenido';
$activeTab = 'gestures';
$gestureIcon = 'iconoir-page-edit';
$gestureColor = 'from-cyan-500 to-teal-600';
?><!DOCTYPE html>
<html lang="es">
<?php include __DIR__ . '/../includes/head.php'; ?>
<body class="bg-mesh text-slate-900 overflow-hidden">
  <div class="min-h-screen flex h-screen">
    <?php include __DIR__ . '/../includes/left-tabs.php'; ?>
    
    <!-- Sidebar de historial -->
    <aside id="history-sidebar" class="w-72 glass-strong border-r border-slate-200/50 flex flex-col shrink-0">
      <div class="p-4 border-b border-slate-200/50">
        <div class="flex items-center justify-between">
          <h2 class="font-semibold text-slate-800 flex items-center gap-2">
            <i class="iconoir-clock text-cyan-500"></i>
            Historial
          </h2>
          <button id="new-content-btn" class="p-1.5 text-slate-400 hover:text-cyan-500 hover:bg-cyan-50 rounded-lg transition-smooth" title="Nuevo contenido">
            <i class="iconoir-plus text-lg"></i>
          </button>
        </div>
      </div>
      
      <div id="history-list" class="flex-1 overflow-auto">
        <!-- Se carga dinámicamente -->
        <div class="p-4 text-center text-slate-400 text-sm">
          <i class="iconoir-refresh animate-spin"></i>
          Cargando...
        </div>
      </div>
    </aside>
    
    <!-- Main content area -->
    <main class="flex-1 flex flex-col overflow-hidden">
      <?php include __DIR__ . '/../includes/header-gestures.php'; ?>

      <!-- Scrollable content -->
      <div class="flex-1 overflow-auto p-6">
        <div class="max-w-4xl mx-auto">
          <!-- Header del gesto -->
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-teal-600 flex items-center justify-center text-white shadow-lg">
              <i class="iconoir-page-edit text-xl"></i>
            </div>
            <div>
              <h1 class="text-xl font-bold text-slate-900">Escribir contenido</h1>
              <p class="text-sm text-slate-600">Genera artículos, posts de blog o notas de prensa</p>
            </div>
          </div>
    
    <!-- Formulario del gesto -->
    <form id="write-article-form" class="space-y-6 glass-strong rounded-2xl border border-slate-200/50 p-6 shadow-sm">
      
      <!-- PASO 1: Tipo de contenido -->
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-3">¿Qué tipo de contenido necesitas?</label>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <label class="cursor-pointer">
            <input type="radio" name="content-type" value="informativo" class="hidden peer" checked />
            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 hover:border-cyan-400 transition-all h-full">
              <div class="flex items-center gap-2 mb-2">
                <i class="iconoir-journal-page text-xl text-cyan-700"></i>
                <span class="font-semibold text-slate-800">Artículo informativo</span>
              </div>
              <p class="text-xs text-slate-500">Noticias, actualidad, deportes, cultura. Contenido objetivo y directo.</p>
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="content-type" value="blog" class="hidden peer" />
            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 hover:border-cyan-400 transition-all h-full">
              <div class="flex items-center gap-2 mb-2">
                <i class="iconoir-post text-xl text-cyan-700"></i>
                <span class="font-semibold text-slate-800">Post de blog</span>
              </div>
              <p class="text-xs text-slate-500">Optimizado para SEO, con palabras clave y estructura web.</p>
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="content-type" value="nota-prensa" class="hidden peer" />
            <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 hover:border-cyan-400 transition-all h-full">
              <div class="flex items-center gap-2 mb-2">
                <i class="iconoir-megaphone text-xl text-cyan-700"></i>
                <span class="font-semibold text-slate-800">Nota de prensa</span>
              </div>
              <p class="text-xs text-slate-500">Comunicados oficiales con estructura periodística profesional.</p>
            </div>
          </label>
        </div>
      </div>
      
      <!-- Línea de negocio (siempre visible) -->
      <div class="flex gap-4 items-center p-3 bg-slate-50/80 rounded-xl border border-slate-200/50">
        <label class="text-sm font-medium text-slate-700 whitespace-nowrap">Línea de negocio:</label>
        <div class="flex flex-wrap gap-2">
          <label class="cursor-pointer">
            <input type="radio" name="business-line" value="ebone" class="hidden peer" checked />
            <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 peer-checked:text-cyan-700 hover:border-cyan-400 transition-all font-medium">
              Grupo Ebone
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="business-line" value="cubofit" class="hidden peer" />
            <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 peer-checked:text-cyan-700 hover:border-cyan-400 transition-all font-medium">
              CUBOFIT
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="business-line" value="uniges" class="hidden peer" />
            <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 peer-checked:text-cyan-700 hover:border-cyan-400 transition-all font-medium">
              UNIGES-3
            </div>
          </label>
        </div>
      </div>
      
      <!-- ========== CAMPOS ARTÍCULO INFORMATIVO ========== -->
      <div id="fields-informativo" class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Tema del artículo</label>
          <input type="text" id="info-topic" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all bg-white/80" placeholder="Ej: Nueva temporada de actividades acuáticas en los centros deportivos" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Categoría</label>
            <select id="info-category" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 transition-all bg-white/80">
              <option value="general">General</option>
              <option value="deportes">Deportes</option>
              <option value="cultura">Cultura</option>
              <option value="salud">Salud y bienestar</option>
              <option value="empresa">Corporativo</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Extensión</label>
            <select id="info-length" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 transition-all bg-white/80">
              <option value="300">Corto (~300 palabras)</option>
              <option value="500" selected>Medio (~500 palabras)</option>
              <option value="800">Largo (~800 palabras)</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Detalles adicionales <span class="font-normal text-slate-400">(opcional)</span></label>
          <textarea id="info-details" rows="2" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all resize-none bg-white/80" placeholder="Información extra, datos concretos, enfoque deseado..."></textarea>
        </div>
      </div>
      
      <!-- ========== CAMPOS POST DE BLOG ========== -->
      <div id="fields-blog" class="hidden space-y-4">
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Tema del post</label>
          <input type="text" id="blog-topic" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all bg-white/80" placeholder="Ej: 5 beneficios de hacer ejercicio por la mañana" />
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Palabras clave SEO <span class="font-normal text-slate-400">(separadas por comas)</span></label>
          <input type="text" id="blog-keywords" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all bg-white/80" placeholder="Ej: ejercicio matutino, rutina fitness, salud, bienestar" />
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
          <textarea id="blog-details" rows="2" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all resize-none bg-white/80" placeholder="Tono específico, datos a incluir, llamada a la acción..."></textarea>
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
              <div class="px-3 py-2 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 hover:border-cyan-400 transition-all flex items-center gap-1">
                <i class="iconoir-send-diagonal text-sm text-cyan-700"></i>
                <span>Lanzamiento</span>
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="press-type" value="evento" class="hidden peer" />
              <div class="px-3 py-2 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 hover:border-cyan-400 transition-all flex items-center gap-1">
                <i class="iconoir-calendar text-sm text-cyan-700"></i>
                <span>Evento</span>
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="press-type" value="nombramiento" class="hidden peer" />
              <div class="px-3 py-2 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 hover:border-cyan-400 transition-all flex items-center gap-1">
                <i class="iconoir-user-star text-sm text-cyan-700"></i>
                <span>Nombramiento</span>
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="press-type" value="convenio" class="hidden peer" />
              <div class="px-3 py-2 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 hover:border-cyan-400 transition-all flex items-center gap-1">
                <i class="iconoir-community text-sm text-cyan-700"></i>
                <span>Convenio</span>
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="press-type" value="premio" class="hidden peer" />
              <div class="px-3 py-2 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 hover:border-cyan-400 transition-all flex items-center gap-1">
                <i class="iconoir-medal text-sm text-cyan-700"></i>
                <span>Premio/Reconocimiento</span>
              </div>
            </label>
          </div>
        </div>
        
        <!-- Datos básicos con placeholders informativos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">¿Qué ocurre? <span class="text-red-500">*</span></label>
            <input type="text" id="press-what" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all bg-white/80" placeholder="El hecho o noticia principal" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">¿Quién lo hace?</label>
            <input type="text" id="press-who" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all bg-white/80" placeholder="Persona, empresa, organización..." />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">¿Cuándo?</label>
            <input type="text" id="press-when" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all bg-white/80" placeholder="Fecha, periodo, momento..." />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">¿Dónde?</label>
            <input type="text" id="press-where" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all bg-white/80" placeholder="Ubicación, lugar, ámbito..." />
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">¿Por qué?</label>
            <textarea id="press-why" rows="2" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all resize-none bg-white/80" placeholder="Motivo, causa, contexto (solo información segura y contrastada)"></textarea>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Información adicional <span class="font-normal text-slate-400">(opcional)</span></label>
            <textarea id="press-purpose" rows="2" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all resize-none bg-white/80" placeholder="Datos complementarios ya confirmados. No añadas nada que no tengas claro."></textarea>
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Declaración o cita textual <span class="font-normal text-slate-400">(opcional)</span></label>
          <div class="flex gap-2">
            <input type="text" id="press-quote-author" class="w-1/3 border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 transition-all bg-white/80" placeholder="Autor de la cita" />
            <input type="text" id="press-quote-text" class="flex-1 border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 transition-all bg-white/80" placeholder="Texto de la declaración..." />
          </div>
        </div>
        <p class="text-xs text-slate-500 italic">💡 Si dejas campos vacíos, el sistema generará la nota con la información disponible. Los campos obligatorios están marcados con *. La IA no debe inventar datos (fechas, nombres, cargos, cifras, etc.); revisa siempre que todo sea correcto.</p>
      </div>
      
      <!-- Botón generar -->
      <div class="flex justify-end pt-2 border-t border-slate-200/50">
        <button type="submit" id="generate-article-btn" class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-teal-600 hover:from-cyan-600 hover:to-teal-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
          <i class="iconoir-sparks"></i>
          <span>Generar contenido</span>
        </button>
      </div>
    </form>
    
    <!-- Resultado (oculto inicialmente) -->
    <div id="article-result" class="hidden mt-8 glass-strong rounded-2xl border border-slate-200/50 p-6 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-slate-800">Contenido generado</h2>
        <div class="flex gap-2">
          <button id="copy-article-btn" class="px-3 py-1.5 text-sm text-slate-600 hover:text-cyan-700 hover:bg-cyan-50 rounded-lg transition-smooth flex items-center gap-1.5">
            <i class="iconoir-copy"></i> Copiar
          </button>
          <button id="regenerate-article-btn" class="px-3 py-1.5 text-sm text-slate-600 hover:text-cyan-700 hover:bg-cyan-50 rounded-lg transition-smooth flex items-center gap-1.5">
            <i class="iconoir-refresh"></i> Regenerar
          </button>
        </div>
      </div>
      <div id="article-content" class="prose prose-slate max-w-none"></div>
    </div>
    
    <!-- Loading -->
    <div id="article-loading" class="hidden mt-8 text-center py-12">
      <div class="inline-flex items-center gap-3 px-6 py-4 bg-cyan-500/10 rounded-xl">
        <div class="w-5 h-5 border-2 border-cyan-500 border-t-transparent rounded-full animate-spin"></div>
        <span class="text-cyan-700 font-medium">Generando contenido...</span>
      </div>
    </div>
        </div><!-- /max-w-4xl -->
      </div><!-- /scrollable content -->
    </main>
  </div><!-- /main container -->

  <script src="/assets/js/gesture-write-article.js"></script>
</body>
</html>
