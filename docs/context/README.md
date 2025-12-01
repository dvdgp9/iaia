# Contexto Corporativo de Ebonia

Esta carpeta contiene los archivos de conocimiento base que Ebonia utiliza en todas las conversaciones.

## Cómo funciona

1. **ContextBuilder** lee todos los archivos `.md` de esta carpeta
2. Los concatena en orden alfabético (priorizando `system_prompt.md` al inicio)
3. Este texto combinado se envía como **system instruction** a los modelos LLM
4. Todos los proveedores (Gemini, ChatGPT, etc.) reciben el mismo contexto base

## Archivos actuales

- **system_prompt.md**: Instrucciones base del sistema, rol de Ebonia, directrices de conversación
- **grupo_ebone_overview.md**: Información general del Grupo Ebone (PENDIENTE - subir documento)

## Cómo añadir nuevo contexto

1. Crea un archivo `.md` en esta carpeta con el contenido que quieras
2. Usa markdown estándar
3. El contenido se añadirá automáticamente al contexto de Ebonia
4. No requiere reiniciar el servidor (se lee en cada request)

## Ejemplo de estructura futura

```
docs/context/
├── system_prompt.md           # Instrucciones generales
├── grupo_ebone_overview.md    # Visión general
├── linea_cubo.md             # Detalle línea Cubo
├── linea_lex.md              # Detalle línea Lex  
├── linea_uniges.md           # Detalle línea Uniges
└── guidelines_tone.md        # Guías de tono y estilo
```

## Límites

- Gemini 2.5 Flash soporta hasta 1M tokens (~4MB de texto)
- Mantén cada archivo enfocado y conciso
- El contexto total debería ser < 50-100 páginas para rendimiento óptimo

## Nota importante

Estos archivos **NO se guardan en git** si contienen información sensible.  
Añade patrones a `.gitignore` si es necesario.
