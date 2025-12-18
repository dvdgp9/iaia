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
$pageTitle = 'Redes Sociales — Ebonia';
$activeTab = 'gestures';
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
            <i class="iconoir-clock text-violet-500"></i>
            Historial
          </h2>
          <button id="new-post-btn" class="p-1.5 text-slate-400 hover:text-violet-500 hover:bg-violet-50 rounded-lg transition-smooth" title="Nueva publicación">
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
              <i class="iconoir-send-diagonal text-sm"></i>
            </div>
            <span class="font-semibold text-slate-800">Redes Sociales</span>
          </div>
        </div>
      </header>

      <!-- Scrollable content -->
      <div class="flex-1 overflow-auto p-6">
        <div class="max-w-4xl mx-auto">
          <!-- Header del gesto -->
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center text-white shadow-lg">
              <i class="iconoir-send-diagonal text-xl"></i>
            </div>
            <div>
              <h1 class="text-xl font-bold text-slate-900">Redes Sociales</h1>
              <p class="text-sm text-slate-600">Construye publicaciones con decisiones editoriales guiadas</p>
            </div>
          </div>
    
    <!-- Formulario del gesto -->
    <form id="social-media-form" class="space-y-6 glass-strong rounded-2xl border border-slate-200/50 p-6 shadow-sm">
      
      <!-- INPUT DE CONTEXTO (obligatorio) -->
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2">
          ¿De qué va la publicación? <span class="text-red-500">*</span>
        </label>
        <textarea id="post-context" rows="3" class="w-full border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all resize-none bg-white/80" placeholder="Ej: Hoy han terminado las obras del nuevo CUBOFIT, recordatorio de servicios de UNIGES-3, formación interna de monitores..."></textarea>
        <p class="text-xs text-slate-400 mt-1.5">No es el copy final. Puede ser una frase, una idea o una nota.</p>
      </div>
      
      <!-- INTENCIÓN PRINCIPAL (obligatorio) -->
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-3">
          Intención principal <span class="text-red-500">*</span>
        </label>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
          <label class="cursor-pointer">
            <input type="radio" name="intention" value="informar" class="hidden peer" checked />
            <div class="p-3 border-2 border-slate-200 rounded-xl peer-checked:border-violet-500 peer-checked:bg-violet-500/10 hover:border-violet-400 transition-all text-center">
              <i class="iconoir-megaphone text-xl text-violet-700 block mb-1"></i>
              <span class="text-sm font-medium text-slate-700">Informar</span>
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="intention" value="reforzar-marca" class="hidden peer" />
            <div class="p-3 border-2 border-slate-200 rounded-xl peer-checked:border-violet-500 peer-checked:bg-violet-500/10 hover:border-violet-400 transition-all text-center">
              <i class="iconoir-community text-xl text-violet-700 block mb-1"></i>
              <span class="text-sm font-medium text-slate-700">Reforzar marca</span>
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="intention" value="conectar" class="hidden peer" />
            <div class="p-3 border-2 border-slate-200 rounded-xl peer-checked:border-violet-500 peer-checked:bg-violet-500/10 hover:border-violet-400 transition-all text-center">
              <i class="iconoir-chat-bubble text-xl text-violet-700 block mb-1"></i>
              <span class="text-sm font-medium text-slate-700">Conectar</span>
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="intention" value="activar" class="hidden peer" />
            <div class="p-3 border-2 border-slate-200 rounded-xl peer-checked:border-violet-500 peer-checked:bg-violet-500/10 hover:border-violet-400 transition-all text-center">
              <i class="iconoir-flash text-xl text-violet-700 block mb-1"></i>
              <span class="text-sm font-medium text-slate-700">Activar interés</span>
            </div>
          </label>
          <label class="cursor-pointer">
            <input type="radio" name="intention" value="aportar-valor" class="hidden peer" />
            <div class="p-3 border-2 border-slate-200 rounded-xl peer-checked:border-violet-500 peer-checked:bg-violet-500/10 hover:border-violet-400 transition-all text-center">
              <i class="iconoir-light-bulb text-xl text-violet-700 block mb-1"></i>
              <span class="text-sm font-medium text-slate-700">Aportar valor</span>
            </div>
          </label>
        </div>
      </div>
      
      <!-- LÍNEA DE NEGOCIO + CANAL (obligatorios) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Línea de negocio -->
        <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/50">
          <label class="text-sm font-medium text-slate-700 block mb-2">Línea de negocio <span class="text-red-500">*</span></label>
          <div class="flex flex-wrap gap-2">
            <label class="cursor-pointer">
              <input type="radio" name="business-line" value="ebone" class="hidden peer" checked />
              <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                Grupo Ebone
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="business-line" value="cubofit" class="hidden peer" />
              <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                CUBOFIT
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="business-line" value="uniges" class="hidden peer" />
              <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                UNIGES-3
              </div>
            </label>
          </div>
        </div>
        
        <!-- Canal -->
        <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/50">
          <label class="text-sm font-medium text-slate-700 block mb-2">Canal <span class="text-red-500">*</span></label>
          <div class="flex flex-wrap gap-2">
            <label class="cursor-pointer">
              <input type="radio" name="channel" value="instagram" class="hidden peer" checked />
              <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium flex items-center gap-1">
                <i class="iconoir-instagram"></i> Instagram
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="channel" value="facebook" class="hidden peer" />
              <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium flex items-center gap-1">
                <i class="iconoir-facebook"></i> Facebook
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="channel" value="linkedin" class="hidden peer" />
              <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium flex items-center gap-1">
                <i class="iconoir-linkedin"></i> LinkedIn
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="channel" value="transversal" class="hidden peer" />
              <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium flex items-center gap-1">
                <i class="iconoir-multi-window"></i> Transversal
              </div>
            </label>
          </div>
        </div>
      </div>
      
      <!-- OPCIONES AVANZADAS (colapsable) -->
      <details class="group">
        <summary class="cursor-pointer text-sm font-medium text-slate-600 hover:text-violet-600 transition-colors flex items-center gap-2">
          <i class="iconoir-settings text-lg"></i>
          Opciones avanzadas
          <i class="iconoir-nav-arrow-down text-xs transition-transform group-open:rotate-180"></i>
        </summary>
        
        <div class="mt-4 space-y-4 pt-4 border-t border-slate-200/50">
          
          <!-- ENFOQUE NARRATIVO (opcional) -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">
              Enfoque narrativo <span class="text-slate-400 font-normal">(opcional)</span>
            </label>
            <p class="text-xs text-slate-400 mb-3">Define desde dónde se cuenta la historia. Si no seleccionas, EbonIA lo deduce.</p>
            <div class="flex flex-wrap gap-2">
              <label class="cursor-pointer">
                <input type="radio" name="narrative" value="" class="hidden peer" checked />
                <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                  <i class="iconoir-sparks"></i> Automático
                </div>
              </label>
              <label class="cursor-pointer">
                <input type="radio" name="narrative" value="personas" class="hidden peer" />
                <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                  <i class="iconoir-group"></i> Personas / equipo
                </div>
              </label>
              <label class="cursor-pointer">
                <input type="radio" name="narrative" value="proyecto" class="hidden peer" />
                <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                  <i class="iconoir-hammer"></i> Proyecto / acción
                </div>
              </label>
              <label class="cursor-pointer">
                <input type="radio" name="narrative" value="detalle" class="hidden peer" />
                <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                  <i class="iconoir-search"></i> Detalle diferencial
                </div>
              </label>
              <label class="cursor-pointer">
                <input type="radio" name="narrative" value="impacto" class="hidden peer" />
                <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                  <i class="iconoir-globe"></i> Impacto en usuarios
                </div>
              </label>
              <label class="cursor-pointer">
                <input type="radio" name="narrative" value="vision" class="hidden peer" />
                <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                  <i class="iconoir-compass"></i> Visión / propósito
                </div>
              </label>
            </div>
          </div>
          
          <!-- LONGITUD + CIERRE -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Longitud -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Longitud <span class="text-slate-400 font-normal">(opcional)</span>
              </label>
              <div class="flex flex-wrap gap-2">
                <label class="cursor-pointer">
                  <input type="radio" name="length" value="" class="hidden peer" checked />
                  <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                    Auto
                  </div>
                </label>
                <label class="cursor-pointer">
                  <input type="radio" name="length" value="corto" class="hidden peer" />
                  <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                    Corto
                  </div>
                </label>
                <label class="cursor-pointer">
                  <input type="radio" name="length" value="medio" class="hidden peer" />
                  <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                    Medio
                  </div>
                </label>
                <label class="cursor-pointer">
                  <input type="radio" name="length" value="largo" class="hidden peer" />
                  <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                    Largo
                  </div>
                </label>
              </div>
            </div>
            
            <!-- Tipo de cierre -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">
                Tipo de cierre <span class="text-slate-400 font-normal">(opcional)</span>
              </label>
              <div class="flex flex-wrap gap-2">
                <label class="cursor-pointer">
                  <input type="radio" name="closing" value="" class="hidden peer" checked />
                  <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                    Auto
                  </div>
                </label>
                <label class="cursor-pointer">
                  <input type="radio" name="closing" value="informativo" class="hidden peer" />
                  <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                    <i class="iconoir-info-circle"></i> Informativo
                  </div>
                </label>
                <label class="cursor-pointer">
                  <input type="radio" name="closing" value="inspirador" class="hidden peer" />
                  <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                    <i class="iconoir-sparks"></i> Inspirador
                  </div>
                </label>
                <label class="cursor-pointer">
                  <input type="radio" name="closing" value="cta-suave" class="hidden peer" />
                  <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                    <i class="iconoir-arrow-right"></i> CTA suave
                  </div>
                </label>
                <label class="cursor-pointer">
                  <input type="radio" name="closing" value="cta-claro" class="hidden peer" />
                  <div class="px-3 py-1.5 text-sm border-2 border-slate-200 rounded-lg peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:border-violet-400 transition-all font-medium">
                    <i class="iconoir-rocket"></i> CTA claro
                  </div>
                </label>
              </div>
            </div>
          </div>
          
        </div>
      </details>
      
      <!-- Botón generar -->
      <div class="flex justify-end pt-2 border-t border-slate-200/50">
        <button type="submit" id="generate-post-btn" class="px-6 py-3 bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-600 hover:to-fuchsia-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
          <i class="iconoir-sparks"></i>
          <span>Generar publicación</span>
        </button>
      </div>
    </form>
    
    <!-- Loading -->
    <div id="post-loading" class="hidden mt-8 text-center py-12">
      <div class="inline-flex items-center gap-3 px-6 py-4 bg-violet-500/10 rounded-xl">
        <div class="w-5 h-5 border-2 border-violet-500 border-t-transparent rounded-full animate-spin"></div>
        <span class="text-violet-700 font-medium">Construyendo publicación...</span>
      </div>
    </div>
    
    <!-- Resultado -->
    <div id="post-result" class="hidden mt-8 space-y-4">
      
      <!-- Resultado principal -->
      <div class="glass-strong rounded-2xl border border-slate-200/50 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-slate-800">Publicación</h2>
          <div class="flex gap-2">
            <button id="copy-post-btn" class="px-3 py-1.5 text-sm text-slate-600 hover:text-violet-700 hover:bg-violet-50 rounded-lg transition-smooth flex items-center gap-1.5">
              <i class="iconoir-copy"></i> Copiar
            </button>
            <button id="regenerate-post-btn" class="px-3 py-1.5 text-sm text-slate-600 hover:text-violet-700 hover:bg-violet-50 rounded-lg transition-smooth flex items-center gap-1.5">
              <i class="iconoir-refresh"></i> Regenerar
            </button>
          </div>
        </div>
        <div id="post-content" class="prose prose-slate max-w-none whitespace-pre-wrap"></div>
      </div>
      
      <!-- Hashtags -->
      <div class="glass rounded-xl border border-slate-200/50 p-4">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
            <i class="iconoir-hashtag text-violet-500"></i>
            Hashtags sugeridos
          </h3>
          <button id="copy-hashtags-btn" class="text-xs text-slate-500 hover:text-violet-600 transition-colors flex items-center gap-1">
            <i class="iconoir-copy"></i> Copiar
          </button>
        </div>
        <div id="hashtags-content" class="text-sm text-violet-600"></div>
      </div>
      
      <!-- Resumen editorial -->
      <details class="glass rounded-xl border border-slate-200/50 p-4">
        <summary class="cursor-pointer text-sm font-semibold text-slate-700 flex items-center gap-2">
          <i class="iconoir-clipboard-check text-slate-400"></i>
          Resumen editorial
          <i class="iconoir-nav-arrow-down text-xs text-slate-400 ml-auto"></i>
        </summary>
        <div id="editorial-summary" class="mt-3 pt-3 border-t border-slate-200/50 text-sm text-slate-600 space-y-1"></div>
      </details>
      
      <!-- Variantes -->
      <div class="glass rounded-xl border border-slate-200/50 p-4">
        <h3 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
          <i class="iconoir-refresh-double text-violet-500"></i>
          Variantes rápidas
        </h3>
        <div class="flex flex-wrap gap-2">
          <button data-variant="cercano" class="variant-btn px-3 py-1.5 text-sm border border-slate-200 rounded-lg hover:border-violet-400 hover:bg-violet-50 transition-all">
            Más cercano
          </button>
          <button data-variant="institucional" class="variant-btn px-3 py-1.5 text-sm border border-slate-200 rounded-lg hover:border-violet-400 hover:bg-violet-50 transition-all">
            Más institucional
          </button>
          <button data-variant="corto" class="variant-btn px-3 py-1.5 text-sm border border-slate-200 rounded-lg hover:border-violet-400 hover:bg-violet-50 transition-all">
            Más corto
          </button>
          <button data-variant="directo" class="variant-btn px-3 py-1.5 text-sm border border-slate-200 rounded-lg hover:border-violet-400 hover:bg-violet-50 transition-all">
            Más directo
          </button>
          <button data-variant="emocional" class="variant-btn px-3 py-1.5 text-sm border border-slate-200 rounded-lg hover:border-violet-400 hover:bg-violet-50 transition-all">
            Más emocional
          </button>
        </div>
      </div>
      
    </div>
    
        </div><!-- /max-w-4xl -->
      </div><!-- /scrollable content -->
    </main>
  </div><!-- /main container -->

  <script src="/assets/js/gesture-social-media.js"></script>
</body>
</html>
