<?php
/**
 * Partial: <head> común para todas las páginas
 * 
 * Variables esperadas:
 * - $pageTitle (opcional): Título de la página, default "Ebonia — IA Corporativa"
 * - $csrfToken: Token CSRF de la sesión
 */
$pageTitle = $pageTitle ?? 'Ebonia — IA Corporativa';
?>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/iconoir-icons/iconoir@main/css/iconoir.css">
  <script>window.CSRF_TOKEN = '<?php echo $csrfToken; ?>';</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { font-family: 'Inter', system-ui, sans-serif; }
    
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
    
    /* === NEW: Premium UI Styles === */
    
    /* Glassmorphism input */
    .glass-input {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.6);
      box-shadow: 
        0 0 0 1px rgba(0, 0, 0, 0.03),
        0 2px 4px rgba(0, 0, 0, 0.02),
        0 12px 24px rgba(0, 0, 0, 0.06);
    }
    .glass-input:focus-within {
      border-color: #23AAC5;
      box-shadow: 
        0 0 0 1px rgba(35, 170, 197, 0.1),
        0 0 0 4px rgba(35, 170, 197, 0.1),
        0 12px 24px rgba(0, 0, 0, 0.08);
    }
    
    /* Animated gradient background */
    .hero-gradient {
      background: 
        radial-gradient(ellipse 80% 50% at 50% -20%, rgba(35, 170, 197, 0.15), transparent),
        radial-gradient(ellipse 60% 40% at 80% 50%, rgba(17, 92, 108, 0.08), transparent),
        radial-gradient(ellipse 50% 30% at 20% 80%, rgba(35, 170, 197, 0.06), transparent);
    }
    
    /* Card hover effects */
    .action-card {
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid rgba(0, 0, 0, 0.06);
    }
    .action-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 32px -8px rgba(0, 0, 0, 0.12);
      border-color: rgba(35, 170, 197, 0.3);
    }
    .action-card:active {
      transform: translateY(0);
    }
    
    /* Subtle pulse animation for CTA */
    @keyframes subtle-pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.8; }
    }
    .pulse-subtle {
      animation: subtle-pulse 3s ease-in-out infinite;
    }
    
    /* Floating animation */
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-6px); }
    }
    .float {
      animation: float 4s ease-in-out infinite;
    }
    
    /* Keyboard shortcut badge */
    .kbd {
      font-size: 10px;
      padding: 2px 6px;
      background: rgba(0, 0, 0, 0.06);
      border-radius: 4px;
      font-weight: 500;
      color: rgba(0, 0, 0, 0.4);
      border: 1px solid rgba(0, 0, 0, 0.08);
    }
    
    /* Smooth scrollbar */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    ::-webkit-scrollbar-track {
      background: transparent;
    }
    ::-webkit-scrollbar-thumb {
      background: rgba(0, 0, 0, 0.15);
      border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: rgba(0, 0, 0, 0.25);
    }
    
    /* Text gradient */
    .text-gradient {
      background: linear-gradient(135deg, #23AAC5 0%, #115c6c 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
  </style>
</head>
