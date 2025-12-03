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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script>window.CSRF_TOKEN = '<?php echo $csrfToken; ?>';</script>
  <style>
    * { font-family: 'Inter', system-ui, sans-serif; }
    
    /* === BRAND GRADIENTS === */
    .gradient-brand {
      background: linear-gradient(135deg, #23AAC5 0%, #115c6c 100%);
    }
    .gradient-brand-btn {
      background: linear-gradient(90deg, #23AAC5 0%, #115c6c 100%);
    }
    
    /* === ANIMATED MESH BACKGROUND === */
    .mesh-bg {
      background: 
        radial-gradient(at 40% 20%, rgba(35, 170, 197, 0.08) 0px, transparent 50%),
        radial-gradient(at 80% 0%, rgba(17, 92, 108, 0.06) 0px, transparent 50%),
        radial-gradient(at 0% 50%, rgba(35, 170, 197, 0.05) 0px, transparent 50%),
        radial-gradient(at 80% 50%, rgba(17, 92, 108, 0.04) 0px, transparent 50%),
        radial-gradient(at 0% 100%, rgba(35, 170, 197, 0.06) 0px, transparent 50%),
        radial-gradient(at 80% 100%, rgba(17, 92, 108, 0.05) 0px, transparent 50%);
      animation: meshMove 20s ease-in-out infinite;
    }
    @keyframes meshMove {
      0%, 100% { background-position: 0% 0%, 100% 0%, 0% 50%, 100% 50%, 0% 100%, 100% 100%; }
      50% { background-position: 50% 20%, 80% 30%, 20% 60%, 90% 40%, 10% 90%, 70% 80%; }
    }
    
    /* === GLASSMORPHISM === */
    .glass {
      background: rgba(255, 255, 255, 0.7);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .glass-dark {
      background: rgba(15, 23, 42, 0.6);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    /* === 3D CARD EFFECT === */
    .card-3d {
      transform-style: preserve-3d;
      transition: transform 0.4s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.4s ease;
    }
    .card-3d:hover {
      transform: perspective(1000px) rotateX(2deg) rotateY(-2deg) translateY(-8px);
      box-shadow: 
        0 25px 50px -12px rgba(35, 170, 197, 0.15),
        0 0 0 1px rgba(35, 170, 197, 0.1);
    }
    
    /* === GLOW EFFECTS === */
    .glow-sm {
      box-shadow: 0 0 20px rgba(35, 170, 197, 0.15);
    }
    .glow-md {
      box-shadow: 0 0 40px rgba(35, 170, 197, 0.2);
    }
    .glow-lg {
      box-shadow: 0 0 60px rgba(35, 170, 197, 0.25);
    }
    .glow-pulse {
      animation: glowPulse 3s ease-in-out infinite;
    }
    @keyframes glowPulse {
      0%, 100% { box-shadow: 0 0 20px rgba(35, 170, 197, 0.2); }
      50% { box-shadow: 0 0 40px rgba(35, 170, 197, 0.35); }
    }
    
    /* === COMMAND INPUT STYLE === */
    .command-input {
      background: rgba(255, 255, 255, 0.95);
      border: 2px solid transparent;
      box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.15),
        0 0 0 1px rgba(35, 170, 197, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.5);
      transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    }
    .command-input:focus {
      border-color: #23AAC5;
      box-shadow: 
        0 25px 50px -12px rgba(35, 170, 197, 0.25),
        0 0 0 4px rgba(35, 170, 197, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.5);
      outline: none;
    }
    
    /* === FLOATING ANIMATION === */
    .float {
      animation: float 6s ease-in-out infinite;
    }
    .float-delayed {
      animation: float 6s ease-in-out infinite;
      animation-delay: -3s;
    }
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }
    
    /* === SHIMMER EFFECT === */
    .shimmer {
      background: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.4) 50%,
        rgba(255, 255, 255, 0) 100%
      );
      background-size: 200% 100%;
      animation: shimmer 2s infinite;
    }
    @keyframes shimmer {
      0% { background-position: -200% 0; }
      100% { background-position: 200% 0; }
    }
    
    /* === GRADIENT TEXT === */
    .gradient-text {
      background: linear-gradient(135deg, #23AAC5 0%, #115c6c 50%, #23AAC5 100%);
      background-size: 200% auto;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: gradientShift 4s ease infinite;
    }
    @keyframes gradientShift {
      0%, 100% { background-position: 0% center; }
      50% { background-position: 100% center; }
    }
    
    /* === ICON BOUNCE === */
    .icon-bounce {
      transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .group:hover .icon-bounce {
      transform: scale(1.15) rotate(-5deg);
    }
    
    /* === ARROW SLIDE === */
    .arrow-slide {
      transition: transform 0.3s ease, opacity 0.3s ease;
      opacity: 0.5;
    }
    .group:hover .arrow-slide {
      transform: translateX(4px);
      opacity: 1;
    }
    
    /* === STAGGER ANIMATION === */
    .stagger-in > * {
      opacity: 0;
      transform: translateY(20px);
      animation: staggerIn 0.5s ease forwards;
    }
    .stagger-in > *:nth-child(1) { animation-delay: 0.1s; }
    .stagger-in > *:nth-child(2) { animation-delay: 0.2s; }
    .stagger-in > *:nth-child(3) { animation-delay: 0.3s; }
    .stagger-in > *:nth-child(4) { animation-delay: 0.4s; }
    .stagger-in > *:nth-child(5) { animation-delay: 0.5s; }
    @keyframes staggerIn {
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* === TABS === */
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
    
    /* === CUSTOM SIZES === */
    .text-xs { font-size: 0.65rem !important; }
    .text-sm { font-size: 0.84rem !important; }
    .text-conversation { font-size: 15px; }
    
    /* === SMOOTH SCROLLBAR === */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(35, 170, 197, 0.3); border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(35, 170, 197, 0.5); }
    
    /* === KEYBOARD SHORTCUT BADGE === */
    .kbd {
      background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      padding: 2px 8px;
      font-size: 11px;
      font-weight: 500;
      color: #64748b;
      box-shadow: 0 2px 0 #cbd5e1;
    }
    
    /* === ORBS === */
    .orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(60px);
      opacity: 0.5;
      pointer-events: none;
    }
    .orb-1 {
      width: 300px;
      height: 300px;
      background: rgba(35, 170, 197, 0.3);
      top: -100px;
      right: -50px;
      animation: orbFloat 15s ease-in-out infinite;
    }
    .orb-2 {
      width: 200px;
      height: 200px;
      background: rgba(17, 92, 108, 0.25);
      bottom: -50px;
      left: -50px;
      animation: orbFloat 12s ease-in-out infinite reverse;
    }
    @keyframes orbFloat {
      0%, 100% { transform: translate(0, 0) scale(1); }
      33% { transform: translate(30px, -30px) scale(1.05); }
      66% { transform: translate(-20px, 20px) scale(0.95); }
    }
  </style>
</head>
