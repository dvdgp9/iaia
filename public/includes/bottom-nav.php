<?php
/**
 * Bottom Navigation Bar - Solo visible en móvil (<lg)
 * 
 * Variables esperadas:
 * - $activeTab: Tab activa ('conversations', 'voices', 'gestures', 'apps', 'account')
 */
$activeTab = $activeTab ?? 'conversations';

$tabs = [
    'conversations' => [
        'icon' => 'iconoir-chat-bubble',
        'iconActive' => 'iconoir-chat-bubble-solid',
        'label' => 'Chat',
        'href' => '/'
    ],
    'voices' => [
        'icon' => 'iconoir-voice-square',
        'iconActive' => 'iconoir-voice-square',
        'label' => 'Voces',
        'href' => '/voices/'
    ],
    'gestures' => [
        'icon' => 'iconoir-magic-wand',
        'iconActive' => 'iconoir-magic-wand',
        'label' => 'Gestos',
        'href' => '/gestos/'
    ],
    'apps' => [
        'icon' => 'iconoir-view-grid',
        'iconActive' => 'iconoir-view-grid',
        'label' => 'Apps',
        'href' => '/aplicaciones/'
    ],
    'account' => [
        'icon' => 'iconoir-user',
        'iconActive' => 'iconoir-user',
        'label' => 'Cuenta',
        'href' => '/account.php'
    ]
];
?>
<!-- Bottom Navigation - Solo móvil -->
<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-slate-200 shadow-lg safe-area-bottom">
  <div class="flex items-center justify-around h-16">
    <?php foreach ($tabs as $tabId => $tab): ?>
      <?php 
        $isActive = ($activeTab === $tabId);
        $colorClass = $isActive ? 'text-[#23AAC5]' : 'text-slate-400';
        $iconClass = $isActive ? ($tab['iconActive'] ?? $tab['icon']) : $tab['icon'];
      ?>
      <a href="<?php echo $tab['href']; ?>" 
         class="flex flex-col items-center justify-center flex-1 h-full py-2 <?php echo $colorClass; ?> transition-colors tap-highlight-none active:bg-slate-50">
        <i class="<?php echo $iconClass; ?> text-xl mb-0.5"></i>
        <span class="text-[10px] font-medium"><?php echo htmlspecialchars($tab['label']); ?></span>
        <?php if ($isActive): ?>
          <div class="absolute bottom-1 w-1 h-1 rounded-full bg-[#23AAC5]"></div>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
</nav>

<style>
  /* Safe area para dispositivos con notch */
  .safe-area-bottom {
    padding-bottom: env(safe-area-inset-bottom, 0);
  }
  
  /* Eliminar highlight en tap */
  .tap-highlight-none {
    -webkit-tap-highlight-color: transparent;
  }
  
  /* Espacio para bottom nav en el contenido principal */
  .has-bottom-nav {
    padding-bottom: 4rem; /* h-16 = 4rem */
  }
  
  @media (min-width: 1024px) {
    .has-bottom-nav {
      padding-bottom: 0;
    }
  }
</style>
