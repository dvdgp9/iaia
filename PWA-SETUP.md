# Configuración PWA - Ebonia

## ✅ Archivos creados

- `public/manifest.json` - Metadata de la PWA
- `public/sw.js` - Service Worker (network-first, caché mínimo)
- `public/includes/head.php` - Actualizado con meta tags PWA

## 📱 Iconos necesarios

Debes crear y subir los siguientes iconos en la carpeta `public/assets/icons/`:

### Iconos estándar (fondo cuadrado con logo)
- `icon-72x72.png` (72×72px)
- `icon-96x96.png` (96×96px)
- `icon-128x128.png` (128×128px)
- `icon-144x144.png` (144×144px)
- `icon-152x152.png` (152×152px) - Para Apple Touch Icon
- `icon-192x192.png` (192×192px) - Mínimo requerido Android
- `icon-384x384.png` (384×384px)
- `icon-512x512.png` (512×512px) - Mínimo requerido Android

### Iconos maskable (logo centrado con safe area)
- `icon-192x192-maskable.png` (192×192px)
- `icon-512x512-maskable.png` (512×512px)

**Nota maskable**: Deja un margen de seguridad del 20% (40% del tamaño total como área segura central). El logo debe ocupar solo el 40% central del canvas para que Android no lo recorte.

## 📸 Screenshots (opcionales)

Para mejorar la presentación en el instalador, puedes añadir en `public/assets/screenshots/`:

- `screenshot-mobile-1.png` (390×844px aprox) - Captura móvil del chat o empty state
- `screenshot-desktop-1.png` (1280×800px aprox) - Captura desktop

Si no subes screenshots, elimina la sección "screenshots" del `manifest.json`.

## 🎨 Recomendaciones de diseño

### Para iconos estándar:
- Fondo: Color brand (#23AAC5) o blanco
- Logo: Isotipo de Ebonia centrado
- Bordes redondeados opcionales (el sistema los aplicará)

### Para iconos maskable:
- Canvas completo con fondo de color
- Logo en el centro ocupando máximo el 40% del tamaño
- Sin texto (puede ser recortado)

## 🧪 Cómo probar

1. **Chrome Desktop**: 
   - DevTools → Application → Manifest
   - Verifica que no haya errores
   - Application → Service Workers (debe aparecer registrado)

2. **Chrome Android**:
   - Abre https://tudominio.com
   - Menú → "Instalar app" o "Añadir a pantalla de inicio"

3. **Safari iOS**:
   - Abre en Safari
   - Botón compartir → "Añadir a pantalla de inicio"

## 📝 Notas técnicas

- **Caché**: Mínimo (solo `/`, `/manifest.json`, logo). La app prioriza red siempre.
- **Estrategia**: Network-first para mantener datos actualizados.
- **Offline**: La app muestra error 503 si no hay conexión (esperado, ya que es 100% online).
- **Actualización SW**: Cambiar `CACHE_VERSION` en `sw.js` si necesitas forzar actualización.

## 🚀 Despliegue

1. Sube los iconos a `public/assets/icons/`
2. Commit y push
3. Verifica en Chrome DevTools → Application
4. Prueba instalación en móvil

## ⚙️ Configuración actual

- **Nombre**: Ebonia
- **Color tema**: #23AAC5 (brand)
- **Orientación**: Portrait
- **Display**: Standalone (sin barra navegador)
- **Start URL**: `/` (home/chat)
