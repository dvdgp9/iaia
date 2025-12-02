<?php
require_once __DIR__ . '/../src/App/bootstrap.php';

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
  <title>Ebonia — IA Corporativa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/iconoir-icons/iconoir@main/css/iconoir.css">
  <script>window.CSRF_TOKEN = '<?php echo $csrfToken; ?>';</script>
  <style>
    .gradient-brand {
      background: linear-gradient(135deg, #23AAC5 0%, #115c6c 100%);
    }
    .gradient-brand-btn {
      background: linear-gradient(90deg, #23AAC5 0%, #115c6c 100%);
    }
    .tab-item {
      position: relative;
      transition: all 0.2s ease;
    }
    .tab-item:hover {
      background: rgba(35, 170, 197, 0.1);
    }
    .tab-item.active {
      background: rgba(35, 170, 197, 0.15);
    }
    .tab-item.active::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 3px;
      height: 32px;
      background: #23AAC5;
      border-radius: 0 2px 2px 0;
    }
    /* Custom font sizes */
    .text-xs {
      font-size: 0.65rem !important;
    }
    .text-sm {
      font-size: 0.84rem !important;
    }
    .text-conversation {
      font-size: 15px;
    }
  </style>
</head>
<body class="bg-gray-100 text-slate-900 overflow-hidden">
  <div class="min-h-screen flex h-screen">
    <!-- Barra de tabs lateral izquierda -->
    <aside class="w-[70px] gradient-brand flex flex-col items-center py-6 gap-2">
      <!-- Tab Conversaciones -->
      <button data-tab="conversations" class="tab-item active w-full py-4 flex flex-col items-center gap-1.5 text-white/80 hover:text-white" title="Conversaciones">
        <i class="iconoir-chat-bubble text-2xl"></i>
        <span class="text-[10px] font-medium">Chat</span>
      </button>
      
      <!-- Tab Voces -->
      <button data-tab="voices" class="tab-item w-full py-4 flex flex-col items-center gap-1.5 text-white/60 hover:text-white/80" title="Voces (próximamente)">
        <i class="iconoir-voice-square text-2xl"></i>
        <span class="text-[10px] font-medium">Voces</span>
      </button>
      
      <!-- Tab Gestos -->
      <button data-tab="gestures" class="tab-item w-full py-4 flex flex-col items-center gap-1.5 text-white/60 hover:text-white/80" title="Gestos (próximamente)">
        <i class="iconoir-magic-wand text-2xl"></i>
        <span class="text-[10px] font-medium">Gestos</span>
      </button>
      
      <!-- Spacer -->
      <div class="flex-1"></div>
      
      <!-- Tab Mi cuenta -->
      <a href="/account.php" class="tab-item w-full py-4 flex flex-col items-center gap-1.5 text-white/60 hover:text-white/80" title="Mi cuenta">
        <i class="iconoir-user text-2xl"></i>
        <span class="text-[10px] font-medium">Cuenta</span>
      </a>
    </aside>

    <!-- Sidebar conversaciones -->
    <aside id="conversations-sidebar" class="w-80 bg-white border-r border-slate-200 flex flex-col shadow-sm">
      <div class="p-5 border-b border-slate-200">
        <div class="flex items-center gap-3 mb-6">
          <div class="h-10 w-10 rounded-xl gradient-brand flex items-center justify-center text-white font-bold text-lg shadow-md">E</div>
          <div>
            <strong class="text-xl font-semibold text-slate-800">Ebonia</strong>
            <div class="text-xs text-slate-500">IA Corporativa</div>
          </div>
        </div>
        <button id="new-conv-btn" class="w-full py-2.5 px-4 rounded-lg gradient-brand-btn text-white font-medium shadow-md hover:shadow-lg hover:opacity-90 transition-all duration-200 flex items-center justify-center gap-2">
          <span class="text-lg">+</span> Nueva conversación
        </button>
      </div>
      <div class="flex-1 overflow-y-auto p-3">
        <!-- Sección Carpetas -->
        <div class="mb-4">
          <div class="flex items-center justify-between mb-2 px-2">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Carpetas</div>
            <button id="new-folder-btn" class="p-1 text-slate-400 hover:text-[#23AAC5] hover:bg-[#23AAC5]/10 rounded transition-colors" title="Nueva carpeta">
              <i class="iconoir-folder-plus text-sm"></i>
            </button>
          </div>
          <ul id="folder-list" class="space-y-1">
            <!-- Opción "Todas" siempre visible -->
            <li>
              <button data-folder-id="-1" class="folder-item w-full text-left p-2 rounded-lg transition-all duration-200 flex items-center gap-2 hover:bg-slate-50 group">
                <i class="iconoir-folder text-[#23AAC5]"></i>
                <span class="flex-1 text-sm text-slate-700">Todas</span>
                <span class="text-xs text-slate-400" id="all-count">0</span>
              </button>
            </li>
            <!-- Opción "Sin carpeta" -->
            <li>
              <button data-folder-id="0" class="folder-item w-full text-left p-2 rounded-lg transition-all duration-200 flex items-center gap-2 hover:bg-slate-50 group">
                <i class="iconoir-folder text-[#23AAC5]"></i>
                <span class="flex-1 text-sm text-slate-700">Sin carpeta</span>
                <span class="text-xs text-slate-400" id="root-count">0</span>
              </button>
            </li>
            <!-- Carpetas dinámicas se insertarán aquí -->
          </ul>
        </div>
        
        <!-- Sección Conversaciones -->
        <div>
          <div class="flex items-center justify-between mb-2 px-2">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Conversaciones</div>
            <select id="sort-select" class="text-xs border border-slate-200 rounded px-2 py-1 bg-white focus:outline-none focus:border-[#23AAC5]">
              <option value="updated_at">Recientes</option>
              <option value="favorite">Favoritos</option>
              <option value="created_at">Creación</option>
              <option value="title">Alfabético</option>
            </select>
          </div>
          <ul id="conv-list" class="space-y-1">
            <li class="text-slate-400 text-sm px-3 py-2">(vacío)</li>
          </ul>
        </div>
      </div>
    </aside>
    
    <!-- Sidebar gestos -->
    <aside id="gestures-sidebar" class="hidden w-80 bg-white border-r border-slate-200 flex flex-col shadow-sm">
      <div class="p-5 border-b border-slate-200">
        <div class="flex items-center gap-3 mb-4">
          <div class="h-10 w-10 rounded-xl gradient-brand flex items-center justify-center text-white shadow-md">
            <i class="iconoir-magic-wand text-xl"></i>
          </div>
          <div>
            <strong class="text-xl font-semibold text-slate-800">Gestos</strong>
            <div class="text-xs text-slate-500">Acciones rápidas</div>
          </div>
        </div>
        <p class="text-sm text-slate-600">Selecciona un gesto para empezar una tarea específica.</p>
      </div>
      <div class="flex-1 overflow-y-auto p-4">
        <div class="grid grid-cols-1 gap-3" id="gestures-list">
          <!-- Gesto: Escribir artículos -->
          <button data-gesture="write-article" class="gesture-card group p-4 bg-gradient-to-br from-[#23AAC5]/5 to-[#115c6c]/5 hover:from-[#23AAC5]/10 hover:to-[#115c6c]/10 border-2 border-[#23AAC5]/40 hover:border-[#23AAC5] rounded-xl transition-all duration-200 text-left">
            <div class="flex items-start gap-3">
              <div class="w-10 h-10 rounded-lg bg-[#23AAC5] flex items-center justify-center text-white shadow-sm group-hover:scale-110 transition-transform">
                <i class="iconoir-page-edit text-lg"></i>
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-slate-800 group-hover:text-[#115c6c] transition-colors">Escribir artículos</h3>
                <p class="text-xs text-slate-600 mt-0.5 line-clamp-2">Genera artículos de blog, noticias o contenido editorial con el estilo que elijas.</p>
              </div>
              <i class="iconoir-arrow-right text-slate-400 group-hover:text-[#23AAC5] group-hover:translate-x-1 transition-all"></i>
            </div>
          </button>
          
          <!-- Gesto: Próximamente 1 -->
          <div class="gesture-card p-4 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl opacity-60">
            <div class="flex items-start gap-3">
              <div class="w-10 h-10 rounded-lg bg-slate-300 flex items-center justify-center text-white">
                <i class="iconoir-plus text-lg"></i>
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-slate-500">Próximamente</h3>
                <p class="text-xs text-slate-400 mt-0.5">Nuevos gestos en desarrollo...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <main class="flex-1 flex flex-col bg-white">
      <header class="h-[60px] px-6 border-b border-slate-200 bg-white/95 backdrop-blur-sm flex items-center justify-between shadow-sm sticky top-0 z-10">
        <!-- Título conversación -->
        <div class="flex items-center gap-3 min-w-0">
          <!-- Título conversación activa -->
          <div id="conv-title" class="hidden flex items-center gap-2">
            <i class="iconoir-chat-bubble text-[#23AAC5]"></i>
            <span class="text-sm font-medium text-slate-700 truncate max-w-md"></span>
          </div>
        </div>
        
        <!-- Acciones derecha -->
        <div class="flex items-center gap-3">
          <!-- Búsqueda (preparado futuro) -->
          <button class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-lg transition-colors" title="Buscar (próximamente)">
            <i class="iconoir-search text-xl"></i>
          </button>
          
          <!-- FAQ / Dudas rápidas -->
          <button id="faq-btn" class="p-2 text-slate-400 hover:text-[#23AAC5] hover:bg-[#23AAC5]/10 rounded-lg transition-colors" title="Dudas rápidas">
            <i class="iconoir-help-circle text-xl"></i>
          </button>
          
          <!-- Avatar + Dropdown -->
          <div class="relative" id="profile-dropdown-container">
            <button id="profile-btn" class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded-lg transition-colors">
              <div class="h-8 w-8 rounded-full gradient-brand flex items-center justify-center text-white text-sm font-semibold" id="user-avatar">?</div>
              <i class="iconoir-nav-arrow-down text-slate-400 text-sm"></i>
            </button>
            
            <!-- Dropdown menu -->
            <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-50">
              <div class="px-4 py-3 border-b border-slate-100">
                <div id="session-user" class="font-semibold text-slate-800 text-sm">Cargando...</div>
                <div id="session-meta" class="text-xs text-slate-500 mt-0.5"></div>
              </div>
              <a href="/account.php" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2">
                <i class="iconoir-user"></i>
                <span>Mi cuenta</span>
              </a>
              <a href="/admin/users.php" id="admin-link" class="hidden w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2 border-t border-slate-100">
                <i class="iconoir-settings"></i>
                <span>Gestión de usuarios</span>
              </a>
              <button id="logout-btn" class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 transition-colors flex items-center gap-2 border-t border-slate-100">
                <i class="iconoir-log-out"></i>
                <span>Cerrar sesión</span>
              </button>
            </div>
          </div>
        </div>
      </header>
      
      <!-- Workspace de Gestos (oculto por defecto) -->
      <section id="gestures-workspace" class="hidden flex-1 overflow-auto bg-gradient-to-b from-slate-50/50 to-white">
        <!-- Estado inicial: selecciona un gesto -->
        <div id="gesture-welcome" class="h-full flex items-center justify-center p-6">
          <div class="text-center max-w-md">
            <div class="w-20 h-20 rounded-2xl gradient-brand flex items-center justify-center mx-auto mb-6 shadow-lg">
              <i class="iconoir-magic-wand text-4xl text-white"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Gestos de Ebonia</h2>
            <p class="text-slate-600 mb-6">Los gestos son acciones predefinidas que te ayudan a realizar tareas específicas de forma rápida y consistente.</p>
            <p class="text-sm text-slate-500">← Selecciona un gesto del panel lateral para empezar</p>
          </div>
        </div>
        
        <!-- Workspace del gesto: Escribir artículos -->
        <div id="gesture-write-article" class="hidden h-full flex flex-col">
          <div class="flex-1 overflow-auto p-6">
            <div class="max-w-4xl mx-auto">
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
              <form id="write-article-form" class="space-y-6">
                
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
              <div id="article-result" class="hidden mt-8">
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
                <div id="article-content" class="prose prose-slate max-w-none p-6 bg-white border-2 border-slate-200 rounded-xl"></div>
              </div>
              
              <!-- Loading -->
              <div id="article-loading" class="hidden mt-8 text-center py-12">
                <div class="inline-flex items-center gap-3 px-6 py-4 bg-[#23AAC5]/10 rounded-xl">
                  <div class="w-5 h-5 border-2 border-[#23AAC5] border-t-transparent rounded-full animate-spin"></div>
                  <span class="text-[#115c6c] font-medium">Generando contenido...</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      
      <section class="flex-1 overflow-auto bg-gradient-to-b from-slate-50/50 to-white relative" id="messages-container">
        <div id="context-warning" class="hidden mx-6 mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-start gap-3">
          <i class="iconoir-info-circle text-amber-600 text-lg mt-0.5"></i>
          <div class="flex-1 text-sm">
            <div class="font-medium text-amber-900">Conversación muy larga</div>
            <div class="text-amber-700 mt-0.5">Para optimizar el rendimiento, solo se envían los mensajes más recientes al asistente. El historial completo permanece guardado.</div>
          </div>
        </div>
        <div id="empty-state" class="absolute inset-0 overflow-auto p-6">
          <div class="max-w-6xl mx-auto py-8">
            
            <!-- Hero Input Section -->
            <div class="text-center mb-8">
              <h2 class="text-2xl font-bold text-slate-900 mb-2">¿Qué quieres hacer hoy?</h2>
              <p class="text-sm text-slate-600 mb-6">Escribe una pregunta, elige una voz o inicia una acción rápida</p>
              
              <!-- Input principal -->
              <form id="chat-form-empty" class="max-w-2xl mx-auto mb-4">
                <!-- Preview de archivo adjunto en estado vacío -->
                <div id="file-preview-empty" class="hidden mb-3 p-3 bg-slate-50 border border-slate-200 rounded-lg flex items-center gap-3">
                  <div class="flex-1 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#23AAC5]/10 to-[#115c6c]/10 flex items-center justify-center flex-shrink-0">
                      <i id="file-icon-empty" class="iconoir-page text-xl text-[#23AAC5]"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div id="file-name-empty" class="text-sm font-medium text-slate-800 truncate"></div>
                      <div id="file-size-empty" class="text-xs text-slate-500"></div>
                    </div>
                  </div>
                  <button type="button" id="remove-file-empty" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <i class="iconoir-xmark"></i>
                  </button>
                </div>
                
                <div class="relative flex gap-2">
                  <input type="file" id="file-input-empty" class="hidden" accept=".pdf,.png,.jpg,.jpeg,.gif,.webp" />
                  <button type="button" id="attach-btn-empty" class="p-4 text-slate-400 hover:text-[#23AAC5] hover:bg-[#23AAC5]/5 rounded-2xl transition-all border-2 border-slate-300 hover:border-[#23AAC5]" title="Adjuntar archivo">
                    <i class="iconoir-attachment text-xl"></i>
                  </button>
                  <div class="flex-1 relative">
                    <input id="chat-input-empty" class="w-full border-2 border-slate-300 rounded-2xl px-5 py-4 pr-14 focus:outline-none focus:border-[#23AAC5] focus:ring-4 focus:ring-[#23AAC5]/20 transition-all shadow-lg bg-white" placeholder="Escribe tu pregunta o solicitud..." />
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 p-2.5 gradient-brand-btn text-white rounded-xl hover:shadow-lg hover:opacity-90 transition-all">
                      <svg class="w-5 h-5 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </button>
                  </div>
                </div>
              </form>
            </div>

            <!-- Divisor con "o" -->
            <div class="flex items-center gap-4 max-w-2xl mx-auto mb-8">
              <div class="flex-1 h-px bg-slate-200"></div>
              <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">O elige una opción</span>
              <div class="flex-1 h-px bg-slate-200"></div>
            </div>

            <!-- Grid de opciones: Voces y Gestos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-5xl mx-auto">
              
              <!-- Sección Voces -->
              <div class="bg-white rounded-2xl border-2 border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2.5 mb-4">
                  <div class="w-10 h-10 rounded-xl gradient-brand flex items-center justify-center shadow-sm">
                    <i class="iconoir-voice-square text-xl text-white"></i>
                  </div>
                  <div>
                    <h3 class="font-bold text-slate-900">Hablar con una voz</h3>
                    <p class="text-xs text-slate-500">Personalidades únicas para distintas tareas</p>
                  </div>
                </div>
                
                <div class="space-y-2">
                  <button class="voice-option w-full p-3 bg-slate-50 hover:bg-[#23AAC5]/5 border border-slate-200 hover:border-[#23AAC5] rounded-xl transition-all text-left group" data-voice="cubo">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">C</div>
                      <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-800 text-sm group-hover:text-[#23AAC5] transition-colors">Cubo</div>
                        <div class="text-xs text-slate-500">Analítico y estructurado</div>
                      </div>
                      <i class="iconoir-arrow-right text-slate-400 group-hover:text-[#23AAC5] transition-colors"></i>
                    </div>
                  </button>

                  <button class="voice-option w-full p-3 bg-slate-50 hover:bg-[#23AAC5]/5 border border-slate-200 hover:border-[#23AAC5] rounded-xl transition-all text-left group" data-voice="lex">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">L</div>
                      <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-800 text-sm group-hover:text-[#23AAC5] transition-colors">Lex</div>
                        <div class="text-xs text-slate-500">Creativo y dinámico</div>
                      </div>
                      <i class="iconoir-arrow-right text-slate-400 group-hover:text-[#23AAC5] transition-colors"></i>
                    </div>
                  </button>

                  <button class="voice-option w-full p-3 bg-slate-50 hover:bg-[#23AAC5]/5 border border-slate-200 hover:border-[#23AAC5] rounded-xl transition-all text-left group" data-voice="uniges">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">U</div>
                      <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-800 text-sm group-hover:text-[#23AAC5] transition-colors">Uniges</div>
                        <div class="text-xs text-slate-500">Profesional y técnico</div>
                      </div>
                      <i class="iconoir-arrow-right text-slate-400 group-hover:text-[#23AAC5] transition-colors"></i>
                    </div>
                  </button>

                  <button id="view-all-voices" class="w-full p-3 bg-white hover:bg-[#23AAC5]/5 border-2 border-dashed border-slate-300 hover:border-[#23AAC5] rounded-xl transition-all text-center group">
                    <div class="flex items-center justify-center gap-2 text-sm font-medium text-slate-600 group-hover:text-[#23AAC5] transition-colors">
                      <span>Ver todas las voces</span>
                      <i class="iconoir-arrow-right"></i>
                    </div>
                  </button>
                </div>
              </div>

              <!-- Sección Gestos -->
              <div class="bg-white rounded-2xl border-2 border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2.5 mb-4">
                  <div class="w-10 h-10 rounded-xl gradient-brand flex items-center justify-center shadow-sm">
                    <i class="iconoir-magic-wand text-xl text-white"></i>
                  </div>
                  <div>
                    <h3 class="font-bold text-slate-900">Acciones rápidas</h3>
                    <p class="text-xs text-slate-500">Plantillas optimizadas para tareas específicas</p>
                  </div>
                </div>
                
                <div class="space-y-2">
                  <button class="gesture-option w-full p-3 bg-slate-50 hover:bg-[#23AAC5]/5 border border-slate-200 hover:border-[#23AAC5] rounded-xl transition-all text-left group" data-gesture="write-document">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-[#23AAC5]/20 to-[#115c6c]/20 flex items-center justify-center flex-shrink-0">
                        <i class="iconoir-page text-lg text-[#23AAC5]"></i>
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-800 text-sm group-hover:text-[#23AAC5] transition-colors">Escribir documento</div>
                        <div class="text-xs text-slate-500">Artículos, informes, ensayos</div>
                      </div>
                      <i class="iconoir-arrow-right text-slate-400 group-hover:text-[#23AAC5] transition-colors"></i>
                    </div>
                  </button>

                  <button class="gesture-option w-full p-3 bg-slate-50 hover:bg-[#23AAC5]/5 border border-slate-200 hover:border-[#23AAC5] rounded-xl transition-all text-left group" data-gesture="translate">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-[#23AAC5]/20 to-[#115c6c]/20 flex items-center justify-center flex-shrink-0">
                        <i class="iconoir-translate text-lg text-[#23AAC5]"></i>
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-800 text-sm group-hover:text-[#23AAC5] transition-colors">Traducir texto</div>
                        <div class="text-xs text-slate-500">Múltiples idiomas disponibles</div>
                      </div>
                      <i class="iconoir-arrow-right text-slate-400 group-hover:text-[#23AAC5] transition-colors"></i>
                    </div>
                  </button>

                  <button class="gesture-option w-full p-3 bg-slate-50 hover:bg-[#23AAC5]/5 border border-slate-200 hover:border-[#23AAC5] rounded-xl transition-all text-left group" data-gesture="summarize">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-[#23AAC5]/20 to-[#115c6c]/20 flex items-center justify-center flex-shrink-0">
                        <i class="iconoir-compress text-lg text-[#23AAC5]"></i>
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-800 text-sm group-hover:text-[#23AAC5] transition-colors">Resumir contenido</div>
                        <div class="text-xs text-slate-500">Extrae lo más importante</div>
                      </div>
                      <i class="iconoir-arrow-right text-slate-400 group-hover:text-[#23AAC5] transition-colors"></i>
                    </div>
                  </button>

                  <button id="view-all-gestures" class="w-full p-3 bg-white hover:bg-[#23AAC5]/5 border-2 border-dashed border-slate-300 hover:border-[#23AAC5] rounded-xl transition-all text-center group">
                    <div class="flex items-center justify-center gap-2 text-sm font-medium text-slate-600 group-hover:text-[#23AAC5] transition-colors">
                      <span>Ver todos los gestos</span>
                      <i class="iconoir-arrow-right"></i>
                    </div>
                  </button>
                </div>
              </div>

            </div>
          </div>
        </div>
        <div id="messages" class="hidden p-8 space-y-2"></div>
        <div id="typing-indicator" class="hidden px-8 pb-4">
          <div class="flex gap-3 items-start max-w-3xl">
            <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 text-sm font-semibold flex-shrink-0">E</div>
            <div class="bg-white border border-slate-200 px-5 py-3.5 rounded-2xl rounded-tl-sm shadow-sm">
              <div class="flex gap-1.5">
                <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <footer id="chat-footer" class="hidden p-6 bg-white border-t border-slate-200 shadow-lg">
        <form id="chat-form" class="max-w-4xl mx-auto">
          <!-- Preview de archivo adjunto -->
          <div id="file-preview" class="hidden mb-3 p-3 bg-slate-50 border border-slate-200 rounded-lg flex items-center gap-3">
            <div class="flex-1 flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#23AAC5]/10 to-[#115c6c]/10 flex items-center justify-center flex-shrink-0">
                <i id="file-icon" class="iconoir-page text-xl text-[#23AAC5]"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div id="file-name" class="text-sm font-medium text-slate-800 truncate"></div>
                <div id="file-size" class="text-xs text-slate-500"></div>
              </div>
            </div>
            <button type="button" id="remove-file" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
              <i class="iconoir-xmark"></i>
            </button>
          </div>
          
          <div class="flex gap-3">
            <input type="file" id="file-input" class="hidden" accept=".pdf,.png,.jpg,.jpeg,.gif,.webp" />
            <button type="button" id="attach-btn" class="p-3 text-slate-400 hover:text-[#23AAC5] hover:bg-[#23AAC5]/5 rounded-xl transition-all border-2 border-slate-200 hover:border-[#23AAC5]" title="Adjuntar archivo">
              <i class="iconoir-attachment text-xl"></i>
            </button>
            <input id="chat-input" class="flex-1 border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-all" placeholder="Escribe un mensaje..." />
            <button type="submit" class="px-6 py-3 gradient-brand-btn text-white rounded-xl font-medium shadow-md hover:shadow-lg hover:opacity-90 transition-all duration-200 flex items-center gap-2">
              <span>Enviar</span>
              <svg class="w-5 h-5 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
            </button>
          </div>
        </form>
      </footer>
    </main>
  </div>
  
  <!-- Modal Mover a Carpeta -->
  <div id="move-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[80vh] flex flex-col">
      <!-- Header -->
      <div class="p-6 border-b border-slate-200 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl gradient-brand flex items-center justify-center">
            <i class="iconoir-folder-settings text-xl text-white"></i>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-slate-900">Mover conversación</h3>
            <p class="text-xs text-slate-500" id="move-conv-title">Selecciona la carpeta de destino</p>
          </div>
        </div>
        <button id="close-move-modal" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
          <i class="iconoir-xmark text-xl"></i>
        </button>
      </div>
      
      <!-- Body - Lista de carpetas -->
      <div class="flex-1 overflow-y-auto p-6">
        <div class="space-y-2" id="folder-options">
          <!-- Opción "Sin carpeta" -->
          <button data-target-folder="0" class="folder-option w-full p-4 bg-slate-50 hover:bg-[#23AAC5]/5 border-2 border-slate-200 hover:border-[#23AAC5] rounded-xl transition-all text-left group">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-slate-200 flex items-center justify-center flex-shrink-0 group-hover:bg-[#23AAC5]/10">
                <i class="iconoir-folder-minus text-xl text-slate-500 group-hover:text-[#23AAC5]"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-semibold text-slate-800 group-hover:text-[#23AAC5] transition-colors">Sin carpeta</div>
                <div class="text-xs text-slate-500">Mover a la raíz</div>
              </div>
              <i class="iconoir-nav-arrow-right text-slate-300 group-hover:text-[#23AAC5] transition-colors"></i>
            </div>
          </button>
          
          <!-- Carpetas dinámicas se insertarán aquí -->
        </div>
        
        <div id="empty-folders" class="hidden text-center py-8 text-slate-400 text-sm">
          <i class="iconoir-folder text-4xl mb-2"></i>
          <p>No tienes carpetas creadas</p>
        </div>
      </div>
      
      <!-- Footer -->
      <div class="p-6 border-t border-slate-200 flex gap-3">
        <button id="cancel-move" class="flex-1 px-4 py-2.5 border-2 border-slate-200 text-slate-700 rounded-lg font-medium hover:bg-slate-50 transition-colors">
          Cancelar
        </button>
      </div>
    </div>
  </div>
  
  <script type="module">
    const messagesEl = document.getElementById('messages');
    const messagesContainer = document.getElementById('messages-container');
    const emptyState = document.getElementById('empty-state');
    const chatFooter = document.getElementById('chat-footer');
    const inputEl = document.getElementById('chat-input');
    const inputEmptyEl = document.getElementById('chat-input-empty');
    const formEl = document.getElementById('chat-form');
    const formEmptyEl = document.getElementById('chat-form-empty');
    const logoutBtn = document.getElementById('logout-btn');
    const sessionUser = document.getElementById('session-user');
    const sessionMeta = document.getElementById('session-meta');
    const userAvatar = document.getElementById('user-avatar');
    const profileBtn = document.getElementById('profile-btn');
    const profileDropdown = document.getElementById('profile-dropdown');
    const convTitleEl = document.getElementById('conv-title');
    const convListEl = document.getElementById('conv-list');
    const newConvBtn = document.getElementById('new-conv-btn');
    const sortSelect = document.getElementById('sort-select');
    const typingIndicator = document.getElementById('typing-indicator');
    const folderListEl = document.getElementById('folder-list');
    const newFolderBtn = document.getElementById('new-folder-btn');
    const moveModal = document.getElementById('move-modal');
    const closeMoveModal = document.getElementById('close-move-modal');
    const cancelMoveBtn = document.getElementById('cancel-move');
    const folderOptionsEl = document.getElementById('folder-options');
    const emptyFoldersEl = document.getElementById('empty-folders');
    const fileInput = document.getElementById('file-input');
    const attachBtn = document.getElementById('attach-btn');
    const filePreview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const fileIcon = document.getElementById('file-icon');
    const removeFileBtn = document.getElementById('remove-file');
    
    const fileInputEmpty = document.getElementById('file-input-empty');
    const attachBtnEmpty = document.getElementById('attach-btn-empty');
    const filePreviewEmpty = document.getElementById('file-preview-empty');
    const fileNameEmpty = document.getElementById('file-name-empty');
    const fileSizeEmpty = document.getElementById('file-size-empty');
    const fileIconEmpty = document.getElementById('file-icon-empty');
    const removeFileBtnEmpty = document.getElementById('remove-file-empty');

    let csrf = null;
    let currentConversationId = null;
    let emptyConversationId = null; // id de conversación sin mensajes aún
    let currentUser = null;
    let currentConvTitle = null;
    let currentFile = null; // archivo adjunto actual
    let currentFileEmpty = null; // archivo adjunto en estado vacío
    let currentFolderId = -1; // -1 = todas, 0 = sin carpeta, >0 = carpeta específica
    let allFolders = []; // cache de carpetas
    let conversationToMove = null; // conversación que se está moviendo

    function showChatMode(){
      emptyState.classList.add('hidden');
      messagesEl.classList.remove('hidden');
      chatFooter.classList.remove('hidden');
    }

    function showEmptyMode(){
      emptyState.classList.remove('hidden');
      messagesEl.classList.add('hidden');
      chatFooter.classList.add('hidden');
      messagesEl.innerHTML = '';
      document.getElementById('context-warning').classList.add('hidden');
      convTitleEl.classList.add('hidden');
      inputEmptyEl?.focus();
    }

    function escapeHtml(str){
      return str.replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
    }

    function mdToHtml(md){
      // escape first
      let s = escapeHtml(md);
      // headings
      s = s.replace(/^###\s+(.+)$/gm, '<h3 class="font-semibold text-base mb-1">$1<\/h3>');
      s = s.replace(/^##\s+(.+)$/gm, '<h2 class="font-semibold text-lg mb-1">$1<\/h2>');
      s = s.replace(/^#\s+(.+)$/gm, '<h1 class="font-semibold text-xl mb-1">$1<\/h1>');
      // bold and italics (basic)
      s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1<\/strong>');
      s = s.replace(/\*(.+?)\*/g, '<em>$1<\/em>');
      // inline code
      s = s.replace(/`([^`]+)`/g, '<code class="px-1 py-0.5 bg-slate-100 rounded">$1<\/code>');
      // line breaks
      s = s.replace(/\n/g, '<br>');
      return s;
    }

    function append(role, content){
      if(messagesEl.children.length === 0) showChatMode();
      
      const wrap = document.createElement('div');
      wrap.className = 'mb-6 flex flex-col ' + (role === 'user' ? 'items-end' : 'items-start');
      
      // Avatar + burbuja container
      const msgContainer = document.createElement('div');
      msgContainer.className = 'flex gap-3 max-w-3xl ' + (role === 'user' ? 'flex-row-reverse' : 'flex-row');
      
      // Avatar
      const avatar = document.createElement('div');
      avatar.className = role === 'user'
        ? 'w-9 h-9 rounded-full gradient-brand flex items-center justify-center text-white text-sm font-semibold flex-shrink-0 shadow-sm'
        : 'w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 text-sm font-semibold flex-shrink-0';
      avatar.textContent = role === 'user' 
        ? (currentUser ? currentUser.first_name[0] + currentUser.last_name[0] : '?')
        : 'E';
      
      // Burbuja
      const bubble = document.createElement('div');
      bubble.className = role === 'user' 
        ? 'gradient-brand text-white px-5 py-3.5 rounded-2xl rounded-tr-sm shadow-md text-conversation' 
        : 'bg-white border border-slate-200 text-slate-800 px-5 py-3.5 rounded-2xl rounded-tl-sm shadow-sm text-conversation';
      bubble.style.wordBreak = 'break-word';
      
      if (role === 'assistant') {
        bubble.innerHTML = mdToHtml(content);
      } else {
        bubble.textContent = content;
      }
      
      msgContainer.appendChild(avatar);
      msgContainer.appendChild(bubble);
      
      // Timestamp
      const timestamp = document.createElement('div');
      timestamp.className = 'text-xs text-slate-400 mt-1 px-3';
      const now = new Date();
      timestamp.textContent = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
      
      wrap.appendChild(msgContainer);
      wrap.appendChild(timestamp);
      messagesEl.appendChild(wrap);
      messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    async function api(path, opts={}){
      const res = await fetch(path, {
        method: opts.method || 'GET',
        headers: {
          'Content-Type': 'application/json',
          ...(csrf ? { 'X-CSRF-Token': csrf } : {})
        },
        body: opts.body ? JSON.stringify(opts.body) : undefined,
        credentials: 'include'
      });
      const data = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(data?.error?.message || res.statusText);
      return data;
    }

    // Restaurar sesión al cargar (si existe cookie de sesión)
    (async function initSession(){
      try {
        const res = await fetch('/api/auth/me.php', { credentials: 'include' });
        if (res.status === 401) {
          window.location.href = '/login.php';
          return;
        }
        const data = await res.json();
        csrf = data.csrf_token || null;
        currentUser = data.user;
        
        // Actualizar UI de perfil
        const fullName = `${data.user.first_name} ${data.user.last_name}`;
        sessionUser.textContent = fullName;
        sessionMeta.textContent = data.user.email;
        
        // Avatar con iniciales
        const initials = `${data.user.first_name[0]}${data.user.last_name[0]}`.toUpperCase();
        userAvatar.textContent = initials;
        
        // Mostrar enlace admin si es superadmin
        if (data.user.roles && data.user.roles.includes('admin')) {
          document.getElementById('admin-link').classList.remove('hidden');
        }
        
        await loadFolders();
        await loadConversations();
      } catch (_) {
        window.location.href = '/login.php';
      }
    })();

    // Toggle dropdown perfil
    profileBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      profileDropdown.classList.toggle('hidden');
    });
    
    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', (e) => {
      if (!profileDropdown.classList.contains('hidden') && 
          !e.target.closest('#profile-dropdown-container')) {
        profileDropdown.classList.add('hidden');
      }
    });
    
    sortSelect.addEventListener('change', () => loadConversations());
    
    // Crear nueva carpeta
    newFolderBtn.addEventListener('click', async () => {
      const name = prompt('Nombre de la carpeta:');
      if (!name || name.trim() === '') return;
      try {
        await api('/api/folders/create.php', { method: 'POST', body: { name: name.trim() } });
        await loadFolders();
        await loadConversations();
      } catch (err) {
        alert('Error al crear carpeta: ' + err.message);
      }
    });
    
    // Cerrar modal
    closeMoveModal.addEventListener('click', () => {
      moveModal.classList.add('hidden');
      conversationToMove = null;
    });
    
    cancelMoveBtn.addEventListener('click', () => {
      moveModal.classList.add('hidden');
      conversationToMove = null;
    });
    
    // Cerrar modal al hacer clic fuera
    moveModal.addEventListener('click', (e) => {
      if (e.target === moveModal) {
        moveModal.classList.add('hidden');
        conversationToMove = null;
      }
    });

    logoutBtn.addEventListener('click', async (e)=>{
      e.stopPropagation();
      try {
        await api('/api/auth/logout.php', { method: 'POST' });
        window.location.href = '/login.php';
      } catch(e){
        alert('Logout error: ' + e.message);
      }
    });

    async function loadFolders(){
      const data = await api('/api/folders/list.php');
      allFolders = data.folders || [];
      
      // Contar conversaciones totales y sin carpeta
      const allConvs = await api('/api/conversations/list.php?folder_id=-1');
      const rootConvs = await api('/api/conversations/list.php?folder_id=0');
      document.getElementById('all-count').textContent = (allConvs.items || []).length;
      document.getElementById('root-count').textContent = (rootConvs.items || []).length;
      
      // Renderizar carpetas dinámicas
      const existingDynamic = folderListEl.querySelectorAll('.dynamic-folder');
      existingDynamic.forEach(el => el.remove());
      
      for (const folder of allFolders) {
        const li = document.createElement('li');
        li.className = 'dynamic-folder group';
        
        const btn = document.createElement('button');
        btn.dataset.folderId = folder.id;
        btn.className = 'folder-item w-full text-left p-2 rounded-lg transition-all duration-200 flex items-center gap-2 hover:bg-slate-50';
        if (currentFolderId === folder.id) {
          btn.classList.add('bg-gradient-to-r', 'from-[#23AAC5]/10', 'to-[#115c6c]/10', 'shadow-sm');
        }
        
        const iconEl = document.createElement('i');
        iconEl.className = 'iconoir-folder text-[#23AAC5] flex-shrink-0';
        
        const nameEl = document.createElement('span');
        nameEl.className = 'flex-1 text-sm text-slate-700 truncate min-w-0';
        nameEl.textContent = folder.name;
        
        const countEl = document.createElement('span');
        countEl.className = 'text-xs text-slate-400 flex-shrink-0';
        countEl.textContent = folder.conversation_count;
        
        btn.appendChild(iconEl);
        btn.appendChild(nameEl);
        
        btn.addEventListener('click', () => {
          currentFolderId = folder.id;
          loadFolders();
          loadConversations();
        });
        
        // Acciones de carpeta (renombrar, eliminar) - siempre presentes pero invisibles
        const actions = document.createElement('div');
        actions.className = 'flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0';
        
        const renameBtn = document.createElement('button');
        renameBtn.className = 'p-1 text-slate-400 hover:text-[#23AAC5] hover:bg-[#23AAC5]/10 rounded transition-colors';
        renameBtn.innerHTML = '<i class="iconoir-edit-pencil text-xs"></i>';
        renameBtn.title = 'Renombrar';
        renameBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          const newName = prompt('Nuevo nombre:', folder.name);
          if (!newName || newName.trim() === '') return;
          try {
            await api('/api/folders/rename.php', { method: 'POST', body: { id: folder.id, name: newName.trim() } });
            await loadFolders();
          } catch (err) {
            alert('Error al renombrar: ' + err.message);
          }
        });
        
        const delBtn = document.createElement('button');
        delBtn.className = 'p-1 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors';
        delBtn.innerHTML = '<i class="iconoir-trash text-xs"></i>';
        delBtn.title = 'Eliminar';
        delBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          const msg = folder.conversation_count > 0 
            ? `¿Eliminar "${folder.name}"? Las ${folder.conversation_count} conversaciones quedarán sin carpeta.`
            : `¿Eliminar "${folder.name}"?`;
          if (!confirm(msg)) return;
          try {
            await api('/api/folders/delete.php', { method: 'POST', body: { id: folder.id } });
            if (currentFolderId === folder.id) {
              currentFolderId = -1;
            }
            await loadFolders();
            await loadConversations();
          } catch (err) {
            alert('Error al eliminar: ' + err.message);
          }
        });
        
        actions.appendChild(renameBtn);
        actions.appendChild(delBtn);
        btn.appendChild(actions);
        btn.appendChild(countEl);
        
        li.appendChild(btn);
        folderListEl.appendChild(li);
      }
      
      // Actualizar estado activo de "Todas" y "Sin carpeta"
      const allFolderItems = document.querySelectorAll('.folder-item');
      allFolderItems.forEach(item => {
        const folderId = parseInt(item.dataset.folderId);
        item.classList.remove('bg-gradient-to-r', 'from-[#23AAC5]/10', 'to-[#115c6c]/10', 'shadow-sm');
        if (folderId === currentFolderId) {
          item.classList.add('bg-gradient-to-r', 'from-[#23AAC5]/10', 'to-[#115c6c]/10', 'shadow-sm');
        }
        
        // Añadir event listeners solo para "Todas" (-1) y "Sin carpeta" (0)
        if (folderId === -1 || folderId === 0) {
          item.addEventListener('click', () => {
            currentFolderId = folderId;
            loadFolders();
            loadConversations();
          });
        }
      });
    }
    
    function openMoveModal(conversation) {
      conversationToMove = conversation;
      document.getElementById('move-conv-title').textContent = `"${conversation.title}"`;
      
      // Renderizar opciones de carpetas
      const dynamicOptions = folderOptionsEl.querySelectorAll('.dynamic-folder-option');
      dynamicOptions.forEach(el => el.remove());
      
      if (allFolders.length === 0) {
        emptyFoldersEl.classList.remove('hidden');
      } else {
        emptyFoldersEl.classList.add('hidden');
        
        allFolders.forEach(folder => {
          const btn = document.createElement('button');
          btn.dataset.targetFolder = folder.id;
          btn.className = 'folder-option dynamic-folder-option w-full p-4 bg-slate-50 hover:bg-[#23AAC5]/5 border-2 border-slate-200 hover:border-[#23AAC5] rounded-xl transition-all text-left group';
          
          // Marcar si es la carpeta actual
          if (conversation.folder_id && conversation.folder_id == folder.id) {
            btn.classList.add('border-[#23AAC5]', 'bg-[#23AAC5]/5');
          }
          
          btn.innerHTML = `
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#23AAC5]/20 to-[#115c6c]/20 flex items-center justify-center flex-shrink-0">
                <i class="iconoir-folder text-xl text-[#23AAC5]"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-semibold text-slate-800 group-hover:text-[#23AAC5] transition-colors">${folder.name}</div>
                <div class="text-xs text-slate-500">${folder.conversation_count} conversación${folder.conversation_count !== 1 ? 'es' : ''}</div>
              </div>
              <i class="iconoir-nav-arrow-right text-slate-300 group-hover:text-[#23AAC5] transition-colors"></i>
            </div>
          `;
          
          btn.addEventListener('click', () => handleMoveConversation(folder.id));
          folderOptionsEl.appendChild(btn);
        });
      }
      
      // Añadir listener al botón "Sin carpeta"
      const rootBtn = folderOptionsEl.querySelector('[data-target-folder="0"]');
      if (rootBtn) {
        // Remover listeners anteriores clonando
        const newRootBtn = rootBtn.cloneNode(true);
        rootBtn.parentNode.replaceChild(newRootBtn, rootBtn);
        
        // Marcar si está en raíz
        if (!conversation.folder_id || conversation.folder_id === 0) {
          newRootBtn.classList.add('border-[#23AAC5]', 'bg-[#23AAC5]/5');
        }
        
        newRootBtn.addEventListener('click', () => handleMoveConversation(null));
      }
      
      moveModal.classList.remove('hidden');
    }
    
    async function handleMoveConversation(targetFolderId) {
      if (!conversationToMove) return;
      
      try {
        await api('/api/conversations/move_to_folder.php', { 
          method: 'POST', 
          body: { 
            conversation_id: conversationToMove.id, 
            folder_id: targetFolderId 
          } 
        });
        
        moveModal.classList.add('hidden');
        conversationToMove = null;
        
        await loadFolders();
        await loadConversations();
      } catch (err) {
        alert('Error al mover: ' + err.message);
      }
    }

    async function loadConversations(){
      const sort = sortSelect.value || 'updated_at';
      const folderParam = currentFolderId !== null ? `&folder_id=${currentFolderId}` : '';
      const data = await api(`/api/conversations/list.php?sort=${encodeURIComponent(sort)}${folderParam}`);
      const items = data.items || [];
      if(items.length === 0){
        convListEl.innerHTML = '<li class="text-slate-400 text-sm px-3 py-2">(vacío)</li>';
        return;
      }
      convListEl.innerHTML = '';
      for(const c of items){
        const li = document.createElement('li');
        const isActive = currentConversationId === c.id;
        li.className = 'group rounded-lg transition-all duration-200 ' + (isActive ? 'bg-gradient-to-r from-[#23AAC5]/10 to-[#115c6c]/10 shadow-sm' : 'hover:bg-slate-50');
        const container = document.createElement('div');
        container.className = 'flex items-center gap-2 p-2';
        const btn = document.createElement('button');
        btn.className = 'text-left flex-1 min-w-0 flex items-center gap-2';
        
        // Icono de estrella favorito
        const starBtn = document.createElement('button');
        starBtn.className = 'flex-shrink-0 transition-colors';
        starBtn.innerHTML = c.is_favorite 
          ? '<i class="iconoir-star-solid text-amber-500"></i>'
          : '<i class="iconoir-star text-slate-300 group-hover:text-slate-400"></i>';
        starBtn.title = c.is_favorite ? 'Quitar de favoritos' : 'Añadir a favoritos';
        starBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          try {
            await api('/api/conversations/toggle_favorite.php', { method: 'POST', body: { id: c.id } });
            await loadConversations();
          } catch (err) {
            alert('Error al cambiar favorito: ' + err.message);
          }
        });
        
        const textContainer = document.createElement('div');
        textContainer.className = 'flex-1 min-w-0';
        const titleEl = document.createElement('div');
        titleEl.className = 'font-medium text-sm truncate ' + (isActive ? 'text-[#115c6c]' : 'text-slate-700 group-hover:text-slate-900');
        titleEl.textContent = c.title || `Conversación ${c.id}`;
        const timeEl = document.createElement('div');
        timeEl.className = 'text-xs text-slate-400';
        timeEl.textContent = new Date(c.updated_at).toLocaleDateString('es-ES', {month: 'short', day: 'numeric'});
        textContainer.appendChild(titleEl);
        textContainer.appendChild(timeEl);
        
        btn.appendChild(starBtn);
        btn.appendChild(textContainer);
        btn.addEventListener('click', async () => {
          currentConversationId = c.id;
          updateConvTitle(c.title);
          await loadConversations();
          messagesEl.innerHTML = '';
          await loadMessages(c.id);
        });
        const actions = document.createElement('div');
        actions.className = 'flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity';
        const renameBtn = document.createElement('button');
        renameBtn.className = 'p-1.5 text-slate-400 hover:text-[#23AAC5] hover:bg-[#23AAC5]/10 rounded transition-colors';
        renameBtn.innerHTML = '<i class="iconoir-edit-pencil"></i>';
        renameBtn.title = 'Renombrar';
        renameBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          const title = prompt('Nuevo título', c.title || '');
          if (!title) return;
          try {
            await api('/api/conversations/rename.php', { method: 'POST', body: { id: c.id, title } });
            // Actualizar título en header si es la conversación activa
            if (currentConversationId === c.id) {
              updateConvTitle(title);
            }
            await loadConversations();
          } catch (err) {
            alert('Error al renombrar: ' + err.message);
          }
        });
        const moveBtn = document.createElement('button');
        moveBtn.className = 'p-1.5 text-slate-400 hover:text-[#23AAC5] hover:bg-[#23AAC5]/10 rounded transition-colors';
        moveBtn.innerHTML = '<i class="iconoir-folder-settings"></i>';
        moveBtn.title = 'Mover a carpeta';
        moveBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          openMoveModal(c);
        });
        
        const delBtn = document.createElement('button');
        delBtn.className = 'p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors';
        delBtn.innerHTML = '<i class="iconoir-trash"></i>';
        delBtn.title = 'Borrar';
        delBtn.addEventListener('click', async (e) => {
          e.stopPropagation();
          if (!confirm('¿Borrar conversación?')) return;
          try {
            await api('/api/conversations/delete.php', { method: 'POST', body: { id: c.id } });
            if (currentConversationId === c.id) {
              currentConversationId = null;
              messagesEl.innerHTML = '';
              updateConvTitle(null);
              showEmptyMode();
            }
            await loadFolders();
            await loadConversations();
          } catch (err) {
            alert('Error al borrar: ' + err.message);
          }
        });
        actions.appendChild(renameBtn);
        actions.appendChild(moveBtn);
        actions.appendChild(delBtn);
        container.appendChild(btn);
        container.appendChild(actions);
        li.appendChild(container);
        convListEl.appendChild(li);
      }
    }

    async function loadMessages(conversationId){
      const data = await api(`/api/messages/list.php?conversation_id=${encodeURIComponent(conversationId)}`);
      messagesEl.innerHTML = '';
      document.getElementById('context-warning').classList.add('hidden');
      const items = data.items || [];
      if(items.length > 0){
        showChatMode();
        for(const m of items){
          append(m.role, m.content);
        }
        emptyConversationId = null;
      } else {
        showEmptyMode();
        emptyConversationId = conversationId;
      }
    }
    
    function updateConvTitle(title) {
      if (title && title !== 'Nueva conversación') {
        currentConvTitle = title;
        const span = convTitleEl.querySelector('span');
        if (span) span.textContent = title;
        convTitleEl.classList.remove('hidden');
      } else {
        currentConvTitle = null;
        convTitleEl.classList.add('hidden');
      }
    }

    newConvBtn.addEventListener('click', async ()=>{
      try{
        // Si ya hay una conversación vacía sin mensajes, reutilizarla
        if (emptyConversationId) {
          currentConversationId = emptyConversationId;
          updateConvTitle(null);
          await loadConversations();
          showEmptyMode();
          return;
        }
        const res = await api('/api/conversations/create.php', { method: 'POST', body: {} });
        currentConversationId = res.id;
        emptyConversationId = res.id;
        updateConvTitle(null);
        await loadConversations();
        showEmptyMode();
      }catch(e){
        alert('Error al crear conversación: ' + e.message);
      }
    });

    async function handleSubmit(text, file = null){
      if(!text && !file) return;
      
      // Mostrar mensaje del usuario con archivo si existe
      let userMessage = text || '';
      if (file) {
        userMessage += file ? ` 📎 ${file.name}` : '';
      }
      append('user', userMessage);
      
      // Mostrar indicador de escritura
      typingIndicator.classList.remove('hidden');
      messagesContainer.scrollTop = messagesContainer.scrollHeight;
      
      try {
        const body = {
          conversation_id: currentConversationId,
          message: text || (file ? '¿Qué puedes decirme sobre este archivo?' : '')
        };

        // Si hay archivo, convertir a base64
        if (file) {
          const base64 = await fileToBase64(file);
          body.file = {
            mime_type: file.type,
            data: base64,
            name: file.name
          };
        }

        const data = await api('/api/chat.php', { method: 'POST', body });
        
        // Ocultar indicador de escritura
        typingIndicator.classList.add('hidden');
        
        if (!currentConversationId && data.conversation && data.conversation.id) {
          currentConversationId = data.conversation.id;
          await loadConversations();
        }
        // Actualizar título tras auto-title
        if (data.conversation && data.conversation.id === currentConversationId) {
          const convData = await api(`/api/conversations/list.php`);
          const conv = convData.items?.find(c => c.id === currentConversationId);
          if (conv) updateConvTitle(conv.title);
        }
        // Al enviar el primer mensaje, ya no es conversación vacía
        if (emptyConversationId === currentConversationId) emptyConversationId = null;
        // Mostrar/ocultar aviso de truncamiento
        const warning = document.getElementById('context-warning');
        if (data.context_truncated) {
          warning.classList.remove('hidden');
        } else {
          warning.classList.add('hidden');
        }
        append('assistant', data.message.content);
      } catch(e){
        typingIndicator.classList.add('hidden');
        append('assistant', 'Error: ' + e.message);
      }
    }

    function fileToBase64(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => {
          // Quitar el prefijo "data:mime/type;base64,"
          const base64 = reader.result.split(',')[1];
          resolve(base64);
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
      });
    }

    // Manejar adjuntar archivo
    attachBtn.addEventListener('click', () => {
      fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;

      // Validar tamaño (10MB máximo)
      const maxSize = 10 * 1024 * 1024;
      if (file.size > maxSize) {
        alert('El archivo es demasiado grande. Máximo 10MB.');
        fileInput.value = '';
        return;
      }

      // Validar tipo
      const validTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/gif', 'image/webp'];
      if (!validTypes.includes(file.type)) {
        alert('Tipo de archivo no soportado. Solo PDF e imágenes.');
        fileInput.value = '';
        return;
      }

      currentFile = file;
      showFilePreview(file);
    });

    removeFileBtn.addEventListener('click', () => {
      currentFile = null;
      fileInput.value = '';
      filePreview.classList.add('hidden');
    });

    function showFilePreview(file) {
      fileName.textContent = file.name;
      fileSize.textContent = formatFileSize(file.size);
      
      // Cambiar icono según tipo
      if (file.type === 'application/pdf') {
        fileIcon.className = 'iconoir-page text-xl text-red-500';
      } else if (file.type.startsWith('image/')) {
        fileIcon.className = 'iconoir-media-image text-xl text-[#23AAC5]';
      }
      
      filePreview.classList.remove('hidden');
    }

    function formatFileSize(bytes) {
      if (bytes < 1024) return bytes + ' B';
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
      return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    formEl.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const text = inputEl.value.trim();
      
      if (!text && !currentFile) return;
      
      inputEl.value = '';
      await handleSubmit(text, currentFile);
      
      // Limpiar archivo después de enviar
      if (currentFile) {
        currentFile = null;
        fileInput.value = '';
        filePreview.classList.add('hidden');
      }
    });

    formEmptyEl.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const text = inputEmptyEl.value.trim();
      
      if (!text && !currentFileEmpty) return;
      
      inputEmptyEl.value = '';
      await handleSubmit(text, currentFileEmpty);
      
      // Limpiar archivo después de enviar
      if (currentFileEmpty) {
        currentFileEmpty = null;
        fileInputEmpty.value = '';
        filePreviewEmpty.classList.add('hidden');
      }
    });

    // Manejar adjuntar archivo en estado vacío
    attachBtnEmpty.addEventListener('click', () => {
      fileInputEmpty.click();
    });

    fileInputEmpty.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;

      // Validar tamaño (10MB máximo)
      const maxSize = 10 * 1024 * 1024;
      if (file.size > maxSize) {
        alert('El archivo es demasiado grande. Máximo 10MB.');
        fileInputEmpty.value = '';
        return;
      }

      // Validar tipo
      const validTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/gif', 'image/webp'];
      if (!validTypes.includes(file.type)) {
        alert('Tipo de archivo no soportado. Solo PDF e imágenes.');
        fileInputEmpty.value = '';
        return;
      }

      currentFileEmpty = file;
      showFilePreviewEmpty(file);
    });

    removeFileBtnEmpty.addEventListener('click', () => {
      currentFileEmpty = null;
      fileInputEmpty.value = '';
      filePreviewEmpty.classList.add('hidden');
    });

    function showFilePreviewEmpty(file) {
      fileNameEmpty.textContent = file.name;
      fileSizeEmpty.textContent = formatFileSize(file.size);
      
      // Cambiar icono según tipo
      if (file.type === 'application/pdf') {
        fileIconEmpty.className = 'iconoir-page text-xl text-red-500';
      } else if (file.type.startsWith('image/')) {
        fileIconEmpty.className = 'iconoir-media-image text-xl text-[#23AAC5]';
      }
      
      filePreviewEmpty.classList.remove('hidden');
    }

    // Manejar clics en voces
    document.querySelectorAll('.voice-option').forEach(btn => {
      btn.addEventListener('click', () => {
        const voice = btn.getAttribute('data-voice');
        const voiceName = btn.querySelector('.font-semibold').textContent;
        
        // Mostrar mensaje temporal (próximamente)
        const tempMsg = document.createElement('div');
        tempMsg.className = 'fixed top-20 left-1/2 -translate-x-1/2 bg-[#23AAC5] text-white px-6 py-3 rounded-xl shadow-lg z-50 flex items-center gap-2';
        tempMsg.innerHTML = `<i class="iconoir-voice-square"></i><span>Conversación con <strong>${voiceName}</strong> disponible próximamente</span>`;
        document.body.appendChild(tempMsg);
        
        setTimeout(() => {
          tempMsg.style.opacity = '0';
          tempMsg.style.transition = 'opacity 0.3s';
          setTimeout(() => tempMsg.remove(), 300);
        }, 2000);
      });
    });

    // Manejar clics en gestos
    document.querySelectorAll('.gesture-option').forEach(btn => {
      btn.addEventListener('click', () => {
        const gesture = btn.getAttribute('data-gesture');
        const gestureName = btn.querySelector('.font-semibold').textContent;
        
        // Mostrar mensaje temporal (próximamente)
        const tempMsg = document.createElement('div');
        tempMsg.className = 'fixed top-20 left-1/2 -translate-x-1/2 bg-[#23AAC5] text-white px-6 py-3 rounded-xl shadow-lg z-50 flex items-center gap-2';
        tempMsg.innerHTML = `<i class="iconoir-magic-wand"></i><span>Acción <strong>${gestureName}</strong> disponible próximamente</span>`;
        document.body.appendChild(tempMsg);
        
        setTimeout(() => {
          tempMsg.style.opacity = '0';
          tempMsg.style.transition = 'opacity 0.3s';
          setTimeout(() => tempMsg.remove(), 300);
        }, 2000);
      });
    });

    async function highlightActive(){
      const items = convListEl.querySelectorAll('li');
      items.forEach(li => li.classList.remove('bg-gray-100'));
      // volver a poner la clase sobre el seleccionado en el próximo render de lista
    }

    // Manejo de tabs laterales
    const tabButtons = document.querySelectorAll('[data-tab]');
    const conversationsSidebar = document.getElementById('conversations-sidebar');
    
    tabButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const tab = btn.getAttribute('data-tab');
        
        // Actualizar estado activo de tabs
        tabButtons.forEach(b => {
          b.classList.remove('active', 'text-white/80');
          b.classList.add('text-white/60');
        });
        btn.classList.add('active', 'text-white/80');
        btn.classList.remove('text-white/60');
        
        // Mostrar/ocultar sidebars y workspaces según tab
        const gesturesSidebar = document.getElementById('gestures-sidebar');
        const gesturesWorkspace = document.getElementById('gestures-workspace');
        const messagesContainer = document.getElementById('messages-container');
        const chatForm = document.getElementById('chat-form');
        
        if (tab === 'conversations') {
          conversationsSidebar.classList.remove('hidden');
          gesturesSidebar.classList.add('hidden');
          gesturesWorkspace.classList.add('hidden');
          messagesContainer.classList.remove('hidden');
          if (chatForm) chatForm.classList.remove('hidden');
        } else if (tab === 'gestures') {
          conversationsSidebar.classList.add('hidden');
          gesturesSidebar.classList.remove('hidden');
          gesturesWorkspace.classList.remove('hidden');
          messagesContainer.classList.add('hidden');
          if (chatForm) chatForm.classList.add('hidden');
        } else if (tab === 'voices') {
          conversationsSidebar.classList.add('hidden');
          gesturesSidebar.classList.add('hidden');
          gesturesWorkspace.classList.add('hidden');
          messagesContainer.classList.remove('hidden');
          // Mostrar mensaje "próximamente" para Voces
          const main = document.querySelector('main');
          if (main && !main.querySelector('.coming-soon')) {
            const comingSoon = document.createElement('div');
            comingSoon.className = 'coming-soon absolute inset-0 flex items-center justify-center bg-white/95 z-50';
            comingSoon.innerHTML = `
              <div class="text-center">
                <i class="iconoir-hourglass text-6xl text-[#23AAC5] mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Voces</h2>
                <p class="text-gray-600">Función disponible próximamente</p>
              </div>
            `;
            main.style.position = 'relative';
            main.appendChild(comingSoon);
            
            setTimeout(() => {
              comingSoon.remove();
              document.querySelector('[data-tab="conversations"]').click();
            }, 2000);
          }
        }
      });
    });

    // Botones "Ver todas" que cambian a las tabs correspondientes
    const viewAllVoicesBtn = document.getElementById('view-all-voices');
    const viewAllGesturesBtn = document.getElementById('view-all-gestures');

    if (viewAllVoicesBtn) {
      viewAllVoicesBtn.addEventListener('click', () => {
        const voicesTab = document.querySelector('[data-tab="voices"]');
        if (voicesTab) voicesTab.click();
      });
    }

    if (viewAllGesturesBtn) {
      viewAllGesturesBtn.addEventListener('click', () => {
        const gesturesTab = document.querySelector('[data-tab="gestures"]');
        if (gesturesTab) gesturesTab.click();
      });
    }
  </script>
  
  <!-- Modal FAQ / Dudas Rápidas -->
  <div id="faq-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col">
      <!-- Header -->
      <div class="p-5 border-b border-slate-200 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl gradient-brand flex items-center justify-center">
            <i class="iconoir-help-circle text-xl text-white"></i>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-slate-900">Dudas rápidas</h3>
            <p class="text-xs text-slate-500">Pregunta sobre el Grupo Ebone</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button id="faq-clear-btn" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" title="Nueva conversación">
            <i class="iconoir-refresh text-lg"></i>
          </button>
          <button id="faq-close-btn" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <i class="iconoir-xmark text-xl"></i>
          </button>
        </div>
      </div>
      
      <!-- Mensajes -->
      <div id="faq-messages" class="flex-1 overflow-y-auto p-5 space-y-4">
        <!-- Estado inicial con sugerencias -->
        <div id="faq-suggestions" class="space-y-3">
          <p class="text-sm text-slate-600 text-center mb-4">¿Qué quieres saber? Aquí tienes algunas ideas:</p>
          <div class="grid grid-cols-1 gap-2">
            <button class="faq-suggestion p-3 text-left bg-slate-50 hover:bg-[#23AAC5]/5 border border-slate-200 hover:border-[#23AAC5] rounded-xl transition-all text-sm text-slate-700 hover:text-[#23AAC5]">
              ¿Qué es CUBOFIT y cómo funciona?
            </button>
            <button class="faq-suggestion p-3 text-left bg-slate-50 hover:bg-[#23AAC5]/5 border border-slate-200 hover:border-[#23AAC5] rounded-xl transition-all text-sm text-slate-700 hover:text-[#23AAC5]">
              ¿Cuántos empleados tiene el Grupo Ebone?
            </button>
            <button class="faq-suggestion p-3 text-left bg-slate-50 hover:bg-[#23AAC5]/5 border border-slate-200 hover:border-[#23AAC5] rounded-xl transition-all text-sm text-slate-700 hover:text-[#23AAC5]">
              ¿Qué servicios ofrece UNIGES-3?
            </button>
            <button class="faq-suggestion p-3 text-left bg-slate-50 hover:bg-[#23AAC5]/5 border border-slate-200 hover:border-[#23AAC5] rounded-xl transition-all text-sm text-slate-700 hover:text-[#23AAC5]">
              ¿Dónde están las sedes del grupo?
            </button>
          </div>
        </div>
      </div>
      
      <!-- Typing indicator -->
      <div id="faq-typing" class="hidden px-5 pb-2">
        <div class="flex items-center gap-2 text-slate-500 text-sm">
          <div class="flex gap-1">
            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
          </div>
          <span>Pensando...</span>
        </div>
      </div>
      
      <!-- Input -->
      <div class="p-4 border-t border-slate-200 flex-shrink-0">
        <form id="faq-form" class="flex gap-3">
          <input 
            id="faq-input" 
            type="text" 
            class="flex-1 border-2 border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-[#23AAC5] focus:ring-2 focus:ring-[#23AAC5]/20 transition-all text-sm" 
            placeholder="Escribe tu pregunta..."
            autocomplete="off"
          />
          <button type="submit" class="px-5 py-3 gradient-brand-btn text-white rounded-xl font-medium shadow-md hover:shadow-lg hover:opacity-90 transition-all">
            <i class="iconoir-send text-lg"></i>
          </button>
        </form>
      </div>
    </div>
  </div>
  
  <script>
    // FAQ Modal Logic
    (function() {
      const faqBtn = document.getElementById('faq-btn');
      const faqModal = document.getElementById('faq-modal');
      const faqCloseBtn = document.getElementById('faq-close-btn');
      const faqClearBtn = document.getElementById('faq-clear-btn');
      const faqForm = document.getElementById('faq-form');
      const faqInput = document.getElementById('faq-input');
      const faqMessages = document.getElementById('faq-messages');
      const faqSuggestions = document.getElementById('faq-suggestions');
      const faqTyping = document.getElementById('faq-typing');
      
      let faqHistory = []; // Historial en memoria
      
      // Helpers locales
      function escapeHtml(str) {
        return str.replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
      }
      
      function mdToHtml(md) {
        let s = escapeHtml(md);
        s = s.replace(/^###\s+(.+)$/gm, '<h3 class="font-semibold text-base mb-1">$1</h3>');
        s = s.replace(/^##\s+(.+)$/gm, '<h2 class="font-semibold text-lg mb-1">$1</h2>');
        s = s.replace(/^#\s+(.+)$/gm, '<h1 class="font-semibold text-xl mb-1">$1</h1>');
        s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        s = s.replace(/\*(.+?)\*/g, '<em>$1</em>');
        s = s.replace(/`([^`]+)`/g, '<code class="px-1 py-0.5 bg-slate-200 rounded text-xs">$1</code>');
        s = s.replace(/\n/g, '<br>');
        return s;
      }
      
      // Abrir modal
      faqBtn.addEventListener('click', () => {
        faqModal.classList.remove('hidden');
        faqInput.focus();
      });
      
      // Cerrar modal
      faqCloseBtn.addEventListener('click', () => {
        faqModal.classList.add('hidden');
      });
      
      // Cerrar con click fuera
      faqModal.addEventListener('click', (e) => {
        if (e.target === faqModal) {
          faqModal.classList.add('hidden');
        }
      });
      
      // Cerrar con Escape
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !faqModal.classList.contains('hidden')) {
          faqModal.classList.add('hidden');
        }
      });
      
      // Limpiar conversación
      faqClearBtn.addEventListener('click', () => {
        faqHistory = [];
        faqMessages.innerHTML = faqSuggestions.outerHTML;
        faqSuggestions.classList.remove('hidden');
        bindSuggestions();
      });
      
      // Sugerencias
      function bindSuggestions() {
        document.querySelectorAll('.faq-suggestion').forEach(btn => {
          btn.addEventListener('click', () => {
            faqInput.value = btn.textContent.trim();
            faqForm.dispatchEvent(new Event('submit'));
          });
        });
      }
      bindSuggestions();
      
      // Enviar mensaje
      faqForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = faqInput.value.trim();
        if (!message) return;
        
        // Ocultar sugerencias
        const suggestions = faqMessages.querySelector('#faq-suggestions');
        if (suggestions) suggestions.classList.add('hidden');
        
        // Añadir mensaje usuario
        appendFaqMessage('user', message);
        faqInput.value = '';
        faqHistory.push({ role: 'user', content: message });
        
        // Mostrar typing
        faqTyping.classList.remove('hidden');
        faqMessages.scrollTop = faqMessages.scrollHeight;
        
        try {
          const res = await fetch('/api/faq.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-Token': window.CSRF_TOKEN
            },
            body: JSON.stringify({
              message: message,
              history: faqHistory.slice(0, -1) // Enviar historial sin el mensaje actual
            }),
            credentials: 'include'
          });
          
          const data = await res.json();
          faqTyping.classList.add('hidden');
          
          if (!res.ok) {
            appendFaqMessage('assistant', 'Lo siento, ha ocurrido un error. Por favor, inténtalo de nuevo.');
            return;
          }
          
          appendFaqMessage('assistant', data.reply);
          faqHistory.push({ role: 'assistant', content: data.reply });
          
        } catch (err) {
          faqTyping.classList.add('hidden');
          appendFaqMessage('assistant', 'Error de conexión. Por favor, inténtalo de nuevo.');
        }
      });
      
      function appendFaqMessage(role, content) {
        const div = document.createElement('div');
        div.className = 'flex gap-3 ' + (role === 'user' ? 'justify-end' : 'justify-start');
        
        // Obtener iniciales del usuario del avatar existente en el DOM
        const userInitials = document.getElementById('user-avatar')?.textContent?.trim() || '?';
        
        const avatar = role === 'user' 
          ? `<div class="w-8 h-8 rounded-full gradient-brand flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">${userInitials}</div>`
          : `<div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 text-xs font-semibold flex-shrink-0">E</div>`;
        
        const bubbleClass = role === 'user'
          ? 'gradient-brand text-white'
          : 'bg-slate-100 text-slate-800';
        
        const contentHtml = role === 'assistant' ? mdToHtml(content) : escapeHtml(content);
        
        div.innerHTML = role === 'user'
          ? `<div class="${bubbleClass} px-4 py-2.5 rounded-2xl rounded-tr-sm max-w-[80%] text-sm">${contentHtml}</div>${avatar}`
          : `${avatar}<div class="${bubbleClass} px-4 py-2.5 rounded-2xl rounded-tl-sm max-w-[80%] text-sm">${contentHtml}</div>`;
        
        faqMessages.appendChild(div);
        faqMessages.scrollTop = faqMessages.scrollHeight;
      }
    })();
  </script>
  
  <script>
    // Gestures Logic
    (function() {
      const gestureCards = document.querySelectorAll('[data-gesture]');
      const gestureWelcome = document.getElementById('gesture-welcome');
      const gestureWorkspaces = {
        'write-article': document.getElementById('gesture-write-article')
      };
      
      // Seleccionar gesto
      gestureCards.forEach(card => {
        card.addEventListener('click', () => {
          const gestureId = card.getAttribute('data-gesture');
          
          // Ocultar welcome y todos los workspaces
          gestureWelcome.classList.add('hidden');
          Object.values(gestureWorkspaces).forEach(ws => {
            if (ws) ws.classList.add('hidden');
          });
          
          // Mostrar workspace del gesto seleccionado
          if (gestureWorkspaces[gestureId]) {
            gestureWorkspaces[gestureId].classList.remove('hidden');
          }
          
          // Actualizar estado activo de las cards
          gestureCards.forEach(c => c.classList.remove('ring-2', 'ring-[#23AAC5]'));
          card.classList.add('ring-2', 'ring-[#23AAC5]');
        });
      });
      
      // === Gesto: Escribir contenido ===
      const writeArticleForm = document.getElementById('write-article-form');
      const articleResult = document.getElementById('article-result');
      const articleContent = document.getElementById('article-content');
      const articleLoading = document.getElementById('article-loading');
      const generateArticleBtn = document.getElementById('generate-article-btn');
      const copyArticleBtn = document.getElementById('copy-article-btn');
      const regenerateArticleBtn = document.getElementById('regenerate-article-btn');
      
      // Campos por tipo
      const fieldsInformativo = document.getElementById('fields-informativo');
      const fieldsBlog = document.getElementById('fields-blog');
      const fieldsNotaPrensa = document.getElementById('fields-nota-prensa');
      
      // Mostrar/ocultar campos según tipo de contenido
      const contentTypeRadios = document.querySelectorAll('input[name="content-type"]');
      contentTypeRadios.forEach(radio => {
        radio.addEventListener('change', () => {
          fieldsInformativo.classList.add('hidden');
          fieldsBlog.classList.add('hidden');
          fieldsNotaPrensa.classList.add('hidden');
          
          if (radio.value === 'informativo') fieldsInformativo.classList.remove('hidden');
          else if (radio.value === 'blog') fieldsBlog.classList.remove('hidden');
          else if (radio.value === 'nota-prensa') fieldsNotaPrensa.classList.remove('hidden');
        });
      });
      
      // Helper para convertir markdown a HTML
      function mdToHtml(md) {
        let s = md
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;');
        s = s.replace(/^### (.+)$/gm, '<h3 class="text-lg font-semibold mt-4 mb-2">$1</h3>');
        s = s.replace(/^## (.+)$/gm, '<h2 class="text-xl font-semibold mt-6 mb-3">$1</h2>');
        s = s.replace(/^# (.+)$/gm, '<h1 class="text-2xl font-bold mt-6 mb-3">$1</h1>');
        s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        s = s.replace(/\*(.+?)\*/g, '<em>$1</em>');
        s = s.replace(/\n\n/g, '</p><p class="mb-4">');
        s = '<p class="mb-4">' + s + '</p>';
        return s;
      }
      
      // Mapa de líneas de negocio
      const businessLineMap = {
        'ebone': 'Grupo Ebone',
        'cubofit': 'CUBOFIT',
        'uniges': 'UNIGES-3'
      };
      
      let lastPrompt = ''; // Para regenerar
      
      if (writeArticleForm) {
        writeArticleForm.addEventListener('submit', async (e) => {
          e.preventDefault();
          await generateContent();
        });
      }
      
      async function generateContent() {
        const contentType = document.querySelector('input[name="content-type"]:checked')?.value || 'informativo';
        const businessLine = document.querySelector('input[name="business-line"]:checked')?.value || 'ebone';
        const businessName = businessLineMap[businessLine];
        
        let prompt = '';
        
        // === ARTÍCULO INFORMATIVO ===
        if (contentType === 'informativo') {
          const topic = document.getElementById('info-topic').value.trim();
          if (!topic) { alert('Por favor, indica el tema del artículo'); return; }
          
          const category = document.getElementById('info-category').value;
          const length = document.getElementById('info-length').value;
          const details = document.getElementById('info-details').value.trim();
          
          const categoryMap = {
            'general': 'general/actualidad',
            'deportes': 'deportes y actividad física',
            'cultura': 'cultura y ocio',
            'salud': 'salud y bienestar',
            'empresa': 'noticias corporativas'
          };
          
          prompt = `Escribe un artículo informativo para ${businessName}.

TEMA: ${topic}
CATEGORÍA: ${categoryMap[category]}
EXTENSIÓN: Aproximadamente ${length} palabras

FORMATO:
- Título atractivo (con #)
- Entradilla o lead (primer párrafo que resuma la noticia)
- Desarrollo con subtítulos (##) si es necesario
- Tono objetivo e informativo
- Sin llamadas a la acción comerciales
${details ? `\nINSTRUCCIONES ADICIONALES: ${details}` : ''}

Notas importantes:
- No inventes nombres, cargos, fechas, cifras ni datos de contacto.
- Si por contexto consideras oportuno añadir un correo de contacto, utiliza siempre marketing@ebone.es.

Escribe SOLO el artículo, sin comentarios ni explicaciones.`;
        }
        
        // === POST DE BLOG ===
        else if (contentType === 'blog') {
          const topic = document.getElementById('blog-topic').value.trim();
          if (!topic) { alert('Por favor, indica el tema del post'); return; }
          
          const keywords = document.getElementById('blog-keywords').value.trim();
          const details = document.getElementById('blog-details').value.trim();
          
          prompt = `Escribe un post de blog optimizado para SEO para ${businessName}.

TEMA: ${topic}
${keywords ? `PALABRAS CLAVE: ${keywords}` : ''}

REQUISITOS SEO OBLIGATORIOS:
- Extensión: 600-1000 palabras
- Título H1 atractivo que incluya la palabra clave principal
- Meta descripción sugerida (máx 155 caracteres) al inicio entre corchetes [META: ...]
- Introducción enganchante que incluya la palabra clave en las primeras 100 palabras
- Estructura con H2 y H3 para facilitar la lectura
- Párrafos cortos (máx 3-4 líneas)
- Al menos una lista con viñetas o numerada
- Conclusión con llamada a la acción (CTA)
- Tono cercano pero profesional
${details ? `\nINSTRUCCIONES ADICIONALES: ${details}` : ''}

Notas importantes:
- No inventes nombres, cargos, fechas, cifras ni datos de contacto.
- Si decides incluir un correo de contacto, utiliza siempre marketing@ebone.es.

Escribe SOLO el post, sin comentarios ni explicaciones.`;
        }
        
        // === NOTA DE PRENSA ===
        else if (contentType === 'nota-prensa') {
          const pressType = document.querySelector('input[name="press-type"]:checked')?.value || 'lanzamiento';
          const what = document.getElementById('press-what').value.trim();
          if (!what) { alert('Por favor, indica qué ocurre (el hecho principal)'); return; }
          
          const who = document.getElementById('press-who').value.trim();
          const when = document.getElementById('press-when').value.trim();
          const where = document.getElementById('press-where').value.trim();
          const why = document.getElementById('press-why').value.trim();
          const purpose = document.getElementById('press-purpose').value.trim();
          const quoteAuthor = document.getElementById('press-quote-author').value.trim();
          const quoteText = document.getElementById('press-quote-text').value.trim();
          
          const pressTypeMap = {
            'lanzamiento': 'lanzamiento de proyecto o servicio',
            'evento': 'evento',
            'nombramiento': 'nombramiento o incorporación',
            'convenio': 'convenio o colaboración institucional',
            'premio': 'premio, éxito o reconocimiento'
          };
          
          let dataSection = `QUÉ OCURRE: ${what}`;
          if (who) dataSection += `\nQUIÉN: ${who}`;
          if (when) dataSection += `\nCUÁNDO: ${when}`;
          if (where) dataSection += `\nDÓNDE: ${where}`;
          if (why) dataSection += `\nPOR QUÉ: ${why}`;
          if (purpose) dataSection += `\nINFORMACIÓN ADICIONAL (ya confirmada, sin suposiciones): ${purpose}`;
          if (quoteText) dataSection += `\nDECLARACIÓN${quoteAuthor ? ` (${quoteAuthor})` : ''}: "${quoteText}"`;
          
          prompt = `Escribe una nota de prensa profesional para ${businessName}.

TIPO DE ANUNCIO: ${pressTypeMap[pressType]}

DATOS:
${dataSection}

FORMATO NOTA DE PRENSA:
- Titular impactante (con #)
- Subtítulo o bajada que amplíe la información
- Ubicación y fecha al inicio del cuerpo: "[Ciudad], [fecha] –"
- Primer párrafo: responder a las 5W (qué, quién, cuándo, dónde, por qué) de forma concisa
- Desarrollo: ampliar información en orden de importancia decreciente (pirámide invertida)
- Si hay declaración, incluirla entrecomillada con atribución
- Cierre: información de contexto sobre ${businessName}
- "###" al final (marca estándar de fin de nota de prensa)
- Sección "Para más información:" con placeholder de contacto

Si faltan datos, adapta la nota con la información disponible **sin inventar nunca** fechas, nombres, cargos, lugares, cifras u otros datos sensibles. Si algo no está en los datos, no lo supongas.

Escribe SOLO la nota de prensa, sin comentarios ni explicaciones.`;
        }
        
        lastPrompt = prompt;
        await sendPrompt(prompt);
      }
      
      async function sendPrompt(prompt) {
        // Mostrar loading
        articleResult.classList.add('hidden');
        articleLoading.classList.remove('hidden');
        generateArticleBtn.disabled = true;
        
        try {
          const res = await fetch('/api/chat.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-Token': window.CSRF_TOKEN
            },
            body: JSON.stringify({ message: prompt }),
            credentials: 'include'
          });
          
          const data = await res.json();
          articleLoading.classList.add('hidden');
          generateArticleBtn.disabled = false;
          
          if (!res.ok) {
            alert('Error al generar el contenido: ' + (data.error?.message || 'Error desconocido'));
            return;
          }
          
          // Mostrar resultado
          articleContent.innerHTML = mdToHtml(data.message.content);
          articleResult.classList.remove('hidden');
          
          // Scroll al resultado
          articleResult.scrollIntoView({ behavior: 'smooth', block: 'start' });
          
        } catch (err) {
          articleLoading.classList.add('hidden');
          generateArticleBtn.disabled = false;
          alert('Error de conexión al generar el contenido');
        }
      }
      
      // Copiar contenido
      if (copyArticleBtn) {
        copyArticleBtn.addEventListener('click', () => {
          const text = articleContent.innerText;
          navigator.clipboard.writeText(text).then(() => {
            const originalText = copyArticleBtn.innerHTML;
            copyArticleBtn.innerHTML = '<i class="iconoir-check"></i> Copiado';
            setTimeout(() => {
              copyArticleBtn.innerHTML = originalText;
            }, 2000);
          });
        });
      }
      
      // Regenerar contenido
      if (regenerateArticleBtn) {
        regenerateArticleBtn.addEventListener('click', () => {
          if (lastPrompt) sendPrompt(lastPrompt);
        });
      }
    })();
  </script>
</body>
</html>
