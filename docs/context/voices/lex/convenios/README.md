# Convenios Laborales para RAG

Coloca aquí los PDFs de los convenios laborales. El script de ingesta los procesará automáticamente.

## Formato esperado

- Archivos PDF con texto extraíble (no escaneados como imagen)
- Nombrar de forma descriptiva: `convenio_hosteleria_2024.pdf`, `convenio_limpieza_2023.pdf`, etc.

## Proceso de ingesta

Una vez colocados los PDFs, ejecutar:

```bash
php scripts/rag/ingest_lex.php
```

Esto:
1. Extrae texto de cada PDF
2. Divide en chunks de ~500 tokens
3. Genera embeddings con OpenAI
4. Indexa en Qdrant para búsqueda semántica
