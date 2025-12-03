<?php
require_once __DIR__ . '/../../src/App/bootstrap.php';
require_once __DIR__ . '/../../src/Auth/AuthService.php';

use Auth\AuthService;

$user = AuthService::requireAuth();
$activeTab = 'apps';

// Catálogo de aplicaciones Ebone (orden alfabético por nombre)
$apps = [
    [
        'id' => 'campus',
        'name' => 'Campus',
        'tagline' => 'Formación online',
        'description' => 'Plataforma Moodle 5.1 para formación interna y cursos de equipo.',
        'icon' => 'iconoir-book',
        'color' => 'from-indigo-500 to-blue-700',
        'features' => ['Cursos online para el equipo', 'Seguimiento de progreso', 'Recursos siempre disponibles', 'Integrado con servicios de Ebone'],
        'url' => 'https://campus.ebone.es',
        'url_label' => 'Abrir Campus'
    ],
    [
        'id' => 'ebonia',
        'name' => 'Ebonia',
        'tagline' => 'Asistente IA',
        'description' => 'Asistente de inteligencia artificial con voces especializadas y gestos automatizados para el equipo.',
        'icon' => 'iconoir-flash',
        'color' => 'from-[#23AAC5] to-[#1a8a9f]',
        'features' => ['Chat con contexto corporativo', 'Voces especializadas', 'Gestos automatizados', 'Documentos inteligentes'],
        'url' => '/',
        'url_label' => 'Ya estás aquí',
        'is_current' => true
    ],
    [
        'id' => 'firmas',
        'name' => 'Firmas',
        'tagline' => 'Firmas de correo',
        'description' => 'Plataforma para generar y actualizar firmas de correo corporativas.',
        'icon' => 'iconoir-mail',
        'color' => 'from-pink-500 to-rose-600',
        'features' => ['Generación guiada de firmas', 'Plantillas corporativas', 'Actualización rápida', 'Contraseña de acceso: firmaEBO'],
        'url' => 'https://firmas.ebone.es',
        'url_label' => 'Abrir Firmas'
    ],
    [
        'id' => 'happy',
        'name' => 'Happy',
        'tagline' => 'Encuestas',
        'description' => 'Encuestas y cuestionarios alojados en servidores propios. Coordinadores acceden solo a su ámbito para decisiones rápidas.',
        'icon' => 'iconoir-emoji',
        'color' => 'from-yellow-500 to-amber-600',
        'features' => ['Constructor de formularios', 'Resultados en servidor propio', 'Accesos segmentados', 'Soporte a mejora continua'],
        'url' => 'https://happy.ebone.es',
        'url_label' => 'Abrir Happy'
    ],
    [
        'id' => 'loop',
        'name' => 'Loop',
        'tagline' => 'Planificador de RRSS y Blogs',
        'description' => 'Calendario único para todas las líneas (empresa, ayuntamientos, instalaciones). Comparte solo lo necesario con cada responsable.',
        'icon' => 'iconoir-calendar',
        'color' => 'from-violet-500 to-purple-600',
        'features' => ['Calendario editorial unificado', 'Publicación automática en WordPress', 'Compartición selectiva', 'Feedback trazado por correo'],
        'url' => 'https://loop.ebone.es',
        'url_label' => 'Abrir Loop'
    ],
    [
        'id' => 'passwords',
        'name' => 'Passwords',
        'tagline' => 'Gestor seguro',
        'description' => 'Interfaz web sencilla con búsqueda. Cifrado y control de accesos. Comparte credenciales de forma segura en el equipo.',
        'icon' => 'iconoir-lock',
        'color' => 'from-slate-500 to-zinc-700',
        'features' => ['Acceso individual con visibilidad propia', 'Entradas compartidas por departamento', 'Cifrado y backups', 'Buscador rápido'],
        'url' => 'https://passwords.ebone.es/gestionar',
        'url_label' => 'Abrir Passwords'
    ],
    [
        'id' => 'prisma',
        'name' => 'Prisma',
        'tagline' => 'Cambios y mejoras',
        'description' => 'Gestión de cambios en las aplicaciones y solicitudes de mejora o reporte de fallos.',
        'icon' => 'iconoir-folder',
        'color' => 'from-sky-500 to-blue-600',
        'features' => ['Solicitar nuevas funcionalidades', 'Reportar incidencias en apps', 'Trazabilidad de cambios', 'Canal único con informática'],
        'url' => 'https://prisma.wthefox.com/solicitud.php?empresa=Ebone',
        'url_label' => 'Abrir Prisma'
    ],
    [
        'id' => 'puri',
        'name' => 'Puri',
        'tagline' => 'Control de asistencia',
        'description' => 'Monitores pasan lista desde el móvil. Coordinación y reporting centralizado para coordinadores, DT y superadmin.',
        'icon' => 'iconoir-check-circle',
        'color' => 'from-emerald-500 to-teal-600',
        'features' => ['Gestión de instalaciones y horarios', 'Asistencia consolidada y exportable', 'Informes para AA.PP.', 'Panel admin con control total'],
        'url' => 'https://puri.ebone.es',
        'url_label' => 'App monitores',
        'admin_url' => 'https://puri.ebone.es/admin',
        'admin_label' => 'Panel admin'
    ],
    [
        'id' => 'resq',
        'name' => 'RESQ',
        'tagline' => 'Seguridad acuática',
        'description' => 'Socorristas registran incidencias, controlan aforos por vaso y anotan consumos del botiquín.',
        'icon' => 'iconoir-swimming',
        'color' => 'from-red-500 to-orange-600',
        'features' => ['Incidencias y control de flujo', 'Trazabilidad de botiquines', 'Asignación de socorristas', 'Panel de control centralizado'],
        'url' => 'https://resq.ebone.es',
        'url_label' => 'App socorristas',
        'admin_url' => 'https://resq.ebone.es/admin',
        'admin_label' => 'Panel admin'
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aplicaciones · Ebonia</title>
  <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body class="bg-mesh text-slate-900 min-h-screen overflow-hidden">
  <div class="flex h-screen">
    <!-- Sidebar izquierdo (fixed) -->
    <?php include __DIR__ . '/../includes/left-tabs.php'; ?>

    <!-- Contenido principal -->
    <main class="flex-1 overflow-y-auto">
      <!-- Header -->
      <header class="sticky top-0 z-10 bg-white/70 backdrop-blur-md border-b border-slate-200/50 px-8 py-6">
        <div class="max-w-6xl mx-auto">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#23AAC5] to-[#1a8a9f] flex items-center justify-center shadow-lg shadow-[#23AAC5]/20">
              <i class="iconoir-view-grid text-xl text-white"></i>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-slate-800">Aplicaciones Ebone</h1>
              <p class="text-slate-500 text-sm">Suite de herramientas del equipo IT</p>
            </div>
          </div>
        </div>
      </header>

      <!-- Grid de aplicaciones -->
      <section class="px-8 py-8">
        <div class="max-w-6xl mx-auto">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($apps as $app): ?>
              <div class="group relative bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-200/50 overflow-hidden hover:border-slate-300 transition-all duration-300 hover:shadow-xl hover:shadow-slate-900/10 hover:-translate-y-1">
                <!-- Header con gradiente -->
                <div class="h-20 bg-gradient-to-br <?php echo $app['color']; ?> p-4 flex items-start justify-between">
                  <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <i class="<?php echo $app['icon']; ?> text-xl text-white"></i>
                  </div>
                  <?php if (!empty($app['is_current'])): ?>
                    <span class="px-2 py-1 bg-white/20 backdrop-blur-sm rounded-full text-xs text-white font-medium">
                      Actual
                    </span>
                  <?php endif; ?>
                </div>
                
                <!-- Contenido -->
                <div class="p-5">
                  <h3 class="text-lg font-semibold text-slate-800 mb-1"><?php echo htmlspecialchars($app['name']); ?></h3>
                  <p class="text-sm text-[#23AAC5] font-medium mb-2"><?php echo htmlspecialchars($app['tagline']); ?></p>
                  <p class="text-sm text-slate-500 mb-4 line-clamp-2"><?php echo htmlspecialchars($app['description']); ?></p>
                  
                  <!-- Features -->
                  <ul class="space-y-1.5 mb-5">
                    <?php foreach (array_slice($app['features'], 0, 3) as $feature): ?>
                      <li class="flex items-center gap-2 text-xs text-slate-500">
                        <i class="iconoir-check text-emerald-500"></i>
                        <span><?php echo htmlspecialchars($feature); ?></span>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                  
                  <!-- Botones -->
                  <div class="flex gap-2">
                    <?php if (empty($app['is_current'])): ?>
                      <a href="<?php echo htmlspecialchars($app['url']); ?>" 
                         target="_blank" 
                         rel="noopener noreferrer"
                         class="flex-1 px-4 py-2.5 bg-gradient-to-r <?php echo $app['color']; ?> text-white text-sm font-medium rounded-xl text-center hover:opacity-90 transition-opacity flex items-center justify-center gap-2 shadow-md">
                        <span><?php echo htmlspecialchars($app['url_label']); ?></span>
                        <i class="iconoir-arrow-up-right text-sm"></i>
                      </a>
                      <?php if (!empty($app['admin_url'])): ?>
                        <a href="<?php echo htmlspecialchars($app['admin_url']); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition-colors flex items-center gap-2">
                          <i class="iconoir-settings text-sm"></i>
                          <span>Admin</span>
                        </a>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-400 text-sm font-medium rounded-xl text-center cursor-default">
                        Ya estás aquí
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          
          <!-- Footer info -->
          <div class="mt-12 text-center pb-8">
            <p class="text-slate-500 text-sm">
              ¿Necesitas acceso a alguna aplicación? Escribe a 
              <a href="mailto:it@ebone.es" class="text-[#23AAC5] hover:underline font-medium">it@ebone.es</a>
            </p>
          </div>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
