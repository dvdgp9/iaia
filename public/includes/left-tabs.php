<?php
/**
 * Partial: Barra lateral izquierda con tabs de navegación
 * 
 * Variables esperadas:
 * - $activeTab (opcional): Tab activa ('conversations', 'voices', 'gestures'), default 'conversations'
 * - $useTabsJs (opcional): Si true, usa data-tab para manejo JS interno (index.php). Default false.
 */
$activeTab = $activeTab ?? 'conversations';
$useTabsJs = $useTabsJs ?? false;

$tabs = [
    'conversations' => [
        'icon' => 'iconoir-chat-bubble',
        'label' => 'Chat',
        'href' => '/',
        'title' => 'Conversaciones'
    ],
    'voices' => [
        'icon' => 'iconoir-voice-square',
        'label' => 'Voces',
        'href' => '/voices/',
        'title' => 'Voces especializadas'
    ],
    'gestures' => [
        'icon' => 'iconoir-magic-wand',
        'label' => 'Gestos',
        'href' => '/gestos/',
        'title' => 'Flujos automatizados'
    ]
];
?>
<!-- Barra de tabs lateral izquierda -->
<aside class="w-[70px] gradient-brand flex flex-col items-center py-6 gap-2 shrink-0">
  <?php foreach ($tabs as $tabId => $tab): ?>
    <?php 
      $isActive = ($activeTab === $tabId);
      $baseClasses = 'tab-item w-full py-4 flex flex-col items-center gap-1.5';
      $stateClasses = $isActive 
        ? 'active text-white' 
        : 'text-white/60 hover:text-white/80';
    ?>
    
    <?php if ($useTabsJs): ?>
      <!-- Modo JS: usar botones con data-tab -->
      <button data-tab="<?php echo $tabId; ?>" 
              class="<?php echo $baseClasses . ' ' . $stateClasses; ?>" 
              title="<?php echo htmlspecialchars($tab['title']); ?>">
        <i class="<?php echo $tab['icon']; ?> text-2xl"></i>
        <span class="text-[10px] font-medium"><?php echo htmlspecialchars($tab['label']); ?></span>
      </button>
    <?php elseif ($tab['href']): ?>
      <!-- Modo enlace: navegación directa -->
      <a href="<?php echo $tab['href']; ?>" 
         class="<?php echo $baseClasses . ' ' . $stateClasses; ?>" 
         title="<?php echo htmlspecialchars($tab['title']); ?>">
        <i class="<?php echo $tab['icon']; ?> text-2xl"></i>
        <span class="text-[10px] font-medium"><?php echo htmlspecialchars($tab['label']); ?></span>
      </a>
    <?php else: ?>
      <!-- Tab deshabilitado -->
      <button class="<?php echo $baseClasses; ?> text-white/40 cursor-not-allowed" 
              title="<?php echo htmlspecialchars($tab['title']); ?>" disabled>
        <i class="<?php echo $tab['icon']; ?> text-2xl"></i>
        <span class="text-[10px] font-medium"><?php echo htmlspecialchars($tab['label']); ?></span>
      </button>
    <?php endif; ?>
  <?php endforeach; ?>
  
  <!-- Spacer -->
  <div class="flex-1"></div>
  
  <!-- Tab Mi cuenta -->
  <a href="/account.php" class="tab-item w-full py-4 flex flex-col items-center gap-1.5 text-white/60 hover:text-white/80" title="Mi cuenta">
    <i class="iconoir-user text-2xl"></i>
    <span class="text-[10px] font-medium">Cuenta</span>
  </a>
</aside>
