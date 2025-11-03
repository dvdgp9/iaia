<?php
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ebonia — Placeholder</title>
  <style>
    :root { color-scheme: light dark; }
    body { margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; display:grid; place-items:center; min-height:100dvh; }
    .card { text-align:center; padding:2rem 2.5rem; border:1px solid #ccc5; border-radius:12px; backdrop-filter:saturate(140%) blur(8px); }
    h1 { font-size:1.75rem; margin:0 0 .5rem; }
    p { margin:.25rem 0; opacity:.8; }
    .muted { font-size:.9rem; opacity:.65; }
    code { background: #00000010; padding: .15rem .35rem; border-radius: 6px; }
  </style>
</head>
<body>
  <main class="card">
    <h1>Ebonia</h1>
    <p>Plataforma inicial en PHP/HTML/CSS/JS</p>
    <p class="muted">Placeholder listo para primer commit</p>
    <p class="muted">Servidor: <?php echo phpversion(); ?> · Fecha: <?php echo date('Y-m-d H:i:s'); ?></p>
  </main>
  <script>
    (function(){
      const el = document.querySelector('h1');
      if (el) el.title = 'Ebonia';
    })();
  </script>
</body>
</html>
