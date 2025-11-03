<?php
// Redirigir/servir la nueva UI ubicada en public/index.php sin cambiar el Document Root de Plesk
$publicIndex = __DIR__ . '/public/index.php';
if (is_file($publicIndex)) {
    require $publicIndex;
    exit;
}
http_response_code(500);
echo 'Falta public/index.php';
exit;
