# Configuración Nginx para Gesto Podcast

## Problema
El gesto de podcast tarda 2-4 minutos en ejecutarse:
- Extracción de contenido: 5-10s
- Generación de guion (LLM): 30-60s
- Generación de audio (TTS): 60-180s

Por defecto, Nginx tiene un timeout de 60 segundos, causando error **504 Gateway Timeout**.

## Solución

### 1. Configurar Nginx

Edita tu archivo de configuración de Nginx (probablemente en `/etc/nginx/sites-available/ebonia` o similar):

```nginx
# Añadir dentro del bloque server {}
location ~ ^/api/gestures/podcast\.php$ {
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;  # Ajustar a tu versión de PHP
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    
    # Timeouts para generación de podcast (hasta 5 minutos)
    fastcgi_read_timeout 300s;
    fastcgi_send_timeout 300s;
    proxy_read_timeout 300s;
    proxy_send_timeout 300s;
}
```

### 2. Configurar PHP-FPM

Edita `/etc/php/8.2/fpm/pool.d/www.conf` (ajustar versión):

```ini
; Aumentar timeout para procesos largos
request_terminate_timeout = 300
```

### 3. Reiniciar servicios

```bash
sudo systemctl reload nginx
sudo systemctl restart php8.2-fpm
```

## Verificación

Prueba generando un podcast con un artículo de ~200-300 palabras. Debería tardar ~2 minutos y completarse sin timeout.

## Alternativa: Procesamiento Asíncrono

Si los timeouts persisten o quieres mejor UX:
1. El endpoint crea un "job" y devuelve inmediatamente un ID
2. El frontend hace polling cada 5-10s para verificar el estado
3. Cuando está listo, descarga el audio

Esta solución requiere una tabla `podcast_jobs` y un worker/cron que procese en background.
