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
  <style>
    :root {
      --brand-primary: #23AAC5;
      --brand-dark: #115c6c;
    }
    
    /* Animated mesh gradient background */
    .bg-mesh {
      background: linear-gradient(135deg, #f0f9ff 0%, #e8f7fa 25%, #fff 50%, #f0fdf4 75%, #fefce8 100%);
      background-size: 400% 400%;
      animation: meshMove 20s ease infinite;
    }
    @keyframes meshMove {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }
    
    /* Glassmorphism */
    .glass {
      background: rgba(255,255,255,0.7);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
    }
    .glass-strong {
      background: rgba(255,255,255,0.85);
      backdrop-filter: blur(30px);
      -webkit-backdrop-filter: blur(30px);
    }
    
    .gradient-brand {
      background: linear-gradient(135deg, #23AAC5 0%, #115c6c 100%);
    }
    .gradient-brand-btn {
      background: linear-gradient(90deg, #23AAC5 0%, #115c6c 100%);
    }
    
    /* Glow effects */
    .glow-soft { box-shadow: 0 20px 50px -15px rgba(35,170,197,0.2); }
    
    /* Smooth transitions */
    .transition-smooth { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    
    /* Card hover effects */
    .card-hover {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .card-hover:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 40px -15px rgba(35,170,197,0.25);
    }
    
    /* Floating animation */
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-8px); }
    }
    .animate-float { animation: float 6s ease-in-out infinite; }
    
    /* Pulse glow */
    @keyframes pulseGlow {
      0%, 100% { box-shadow: 0 0 20px rgba(35,170,197,0.2); }
      50% { box-shadow: 0 0 30px rgba(35,170,197,0.4); }
    }
    .animate-pulse-glow { animation: pulseGlow 2s ease-in-out infinite; }
    
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
    
    /* Input focus */
    .input-focus:focus {
      outline: none;
      border-color: var(--brand-primary);
      box-shadow: 0 0 0 4px rgba(35,170,197,0.15);
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
