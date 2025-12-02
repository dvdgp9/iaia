/**
 * Gesto: Escribir contenido (artículos, posts de blog, notas de prensa)
 */
(function() {
  'use strict';

  // === Referencias DOM ===
  const writeArticleForm = document.getElementById('write-article-form');
  const articleResult = document.getElementById('article-result');
  const articleContent = document.getElementById('article-content');
  const articleLoading = document.getElementById('article-loading');
  const generateArticleBtn = document.getElementById('generate-article-btn');
  const copyArticleBtn = document.getElementById('copy-article-btn');
  const regenerateArticleBtn = document.getElementById('regenerate-article-btn');

  // Campos por tipo
  const fieldsInformativo = document.getElementById('fields-informativo');
  const fieldsBlog = document.getElementById('fields-blog');
  const fieldsNotaPrensa = document.getElementById('fields-nota-prensa');

  // === Mostrar/ocultar campos según tipo de contenido ===
  const contentTypeRadios = document.querySelectorAll('input[name="content-type"]');
  contentTypeRadios.forEach(radio => {
    radio.addEventListener('change', () => {
      fieldsInformativo.classList.add('hidden');
      fieldsBlog.classList.add('hidden');
      fieldsNotaPrensa.classList.add('hidden');
      
      if (radio.value === 'informativo') fieldsInformativo.classList.remove('hidden');
      else if (radio.value === 'blog') fieldsBlog.classList.remove('hidden');
      else if (radio.value === 'nota-prensa') fieldsNotaPrensa.classList.remove('hidden');
    });
  });

  // === Helper para convertir markdown a HTML ===
  function mdToHtml(md) {
    let s = md
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
    s = s.replace(/^### (.+)$/gm, '<h3 class="text-lg font-semibold mt-4 mb-2">$1</h3>');
    s = s.replace(/^## (.+)$/gm, '<h2 class="text-xl font-semibold mt-6 mb-3">$1</h2>');
    s = s.replace(/^# (.+)$/gm, '<h1 class="text-2xl font-bold mt-6 mb-3">$1</h1>');
    s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    s = s.replace(/\*(.+?)\*/g, '<em>$1</em>');
    s = s.replace(/\n\n/g, '</p><p class="mb-4">');
    s = '<p class="mb-4">' + s + '</p>';
    return s;
  }

  // === Mapa de líneas de negocio ===
  const businessLineMap = {
    'ebone': 'Grupo Ebone',
    'cubofit': 'CUBOFIT',
    'uniges': 'UNIGES-3'
  };

  let lastPrompt = ''; // Para regenerar

  // === Submit del formulario ===
  if (writeArticleForm) {
    writeArticleForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      await generateContent();
    });
  }

  // === Generar contenido según tipo ===
  async function generateContent() {
    const contentType = document.querySelector('input[name="content-type"]:checked')?.value || 'informativo';
    const businessLine = document.querySelector('input[name="business-line"]:checked')?.value || 'ebone';
    const businessName = businessLineMap[businessLine];
    
    let prompt = '';
    
    // === ARTÍCULO INFORMATIVO ===
    if (contentType === 'informativo') {
      const topic = document.getElementById('info-topic').value.trim();
      if (!topic) { alert('Por favor, indica el tema del artículo'); return; }
      
      const category = document.getElementById('info-category').value;
      const length = document.getElementById('info-length').value;
      const details = document.getElementById('info-details').value.trim();
      
      const categoryMap = {
        'general': 'general/actualidad',
        'deportes': 'deportes y actividad física',
        'cultura': 'cultura y ocio',
        'salud': 'salud y bienestar',
        'empresa': 'noticias corporativas'
      };
      
      prompt = `Escribe un artículo informativo para ${businessName}.

TEMA: ${topic}
CATEGORÍA: ${categoryMap[category]}
EXTENSIÓN: Aproximadamente ${length} palabras

FORMATO:
- Título atractivo (con #)
- Entradilla o lead (primer párrafo que resuma la noticia)
- Desarrollo con subtítulos (##) si es necesario
- Tono objetivo e informativo
- Sin llamadas a la acción comerciales
${details ? `\nINSTRUCCIONES ADICIONALES: ${details}` : ''}

Notas importantes:
- No inventes nombres, cargos, fechas, cifras ni datos de contacto.
- Si por contexto consideras oportuno añadir un correo de contacto, utiliza siempre marketing@ebone.es.

Escribe SOLO el artículo, sin comentarios ni explicaciones.`;
    }
    
    // === POST DE BLOG ===
    else if (contentType === 'blog') {
      const topic = document.getElementById('blog-topic').value.trim();
      if (!topic) { alert('Por favor, indica el tema del post'); return; }
      
      const keywords = document.getElementById('blog-keywords').value.trim();
      const details = document.getElementById('blog-details').value.trim();
      
      prompt = `Escribe un post de blog optimizado para SEO para ${businessName}.

TEMA: ${topic}
${keywords ? `PALABRAS CLAVE: ${keywords}` : ''}

REQUISITOS SEO OBLIGATORIOS:
- Extensión: 600-1000 palabras
- Título H1 atractivo que incluya la palabra clave principal
- Meta descripción sugerida (máx 155 caracteres) al inicio entre corchetes [META: ...]
- Introducción enganchante que incluya la palabra clave en las primeras 100 palabras
- Estructura con H2 y H3 para facilitar la lectura
- Párrafos cortos (máx 3-4 líneas)
- Al menos una lista con viñetas o numerada
- Conclusión con llamada a la acción (CTA)
- Tono cercano pero profesional
${details ? `\nINSTRUCCIONES ADICIONALES: ${details}` : ''}

Notas importantes:
- No inventes nombres, cargos, fechas, cifras ni datos de contacto.
- Si decides incluir un correo de contacto, utiliza siempre marketing@ebone.es.

Escribe SOLO el post, sin comentarios ni explicaciones.`;
    }
    
    // === NOTA DE PRENSA ===
    else if (contentType === 'nota-prensa') {
      const pressType = document.querySelector('input[name="press-type"]:checked')?.value || 'lanzamiento';
      const what = document.getElementById('press-what').value.trim();
      if (!what) { alert('Por favor, indica qué ocurre (el hecho principal)'); return; }
      
      const who = document.getElementById('press-who').value.trim();
      const when = document.getElementById('press-when').value.trim();
      const where = document.getElementById('press-where').value.trim();
      const why = document.getElementById('press-why').value.trim();
      const purpose = document.getElementById('press-purpose').value.trim();
      const quoteAuthor = document.getElementById('press-quote-author').value.trim();
      const quoteText = document.getElementById('press-quote-text').value.trim();
      
      const pressTypeMap = {
        'lanzamiento': 'lanzamiento de proyecto o servicio',
        'evento': 'evento',
        'nombramiento': 'nombramiento o incorporación',
        'convenio': 'convenio o colaboración institucional',
        'premio': 'premio, éxito o reconocimiento'
      };
      
      let dataSection = `QUÉ OCURRE: ${what}`;
      if (who) dataSection += `\nQUIÉN: ${who}`;
      if (when) dataSection += `\nCUÁNDO: ${when}`;
      if (where) dataSection += `\nDÓNDE: ${where}`;
      if (why) dataSection += `\nPOR QUÉ: ${why}`;
      if (purpose) dataSection += `\nINFORMACIÓN ADICIONAL (ya confirmada, sin suposiciones): ${purpose}`;
      if (quoteText) dataSection += `\nDECLARACIÓN${quoteAuthor ? ` (${quoteAuthor})` : ''}: "${quoteText}"`;
      
      prompt = `Escribe una nota de prensa profesional para ${businessName}.

TIPO DE ANUNCIO: ${pressTypeMap[pressType]}

DATOS:
${dataSection}

FORMATO NOTA DE PRENSA:
- Titular impactante (con #)
- Subtítulo o bajada que amplíe la información
- Ubicación y fecha al inicio del cuerpo: "[Ciudad], [fecha] –"
- Primer párrafo: responder a las 5W (qué, quién, cuándo, dónde, por qué) de forma concisa
- Desarrollo: ampliar información en orden de importancia decreciente (pirámide invertida)
- Si hay declaración, incluirla entrecomillada con atribución
- Cierre: información de contexto sobre ${businessName}
- "###" al final (marca estándar de fin de nota de prensa)
- Sección "Para más información:" con placeholder de contacto

Si faltan datos, adapta la nota con la información disponible **sin inventar nunca** fechas, nombres, cargos, lugares, cifras u otros datos sensibles. Si algo no está en los datos, no lo supongas.

Escribe SOLO la nota de prensa, sin comentarios ni explicaciones.`;
    }
    
    lastPrompt = prompt;
    await sendPrompt(prompt);
  }

  // === Enviar prompt a la API ===
  async function sendPrompt(prompt) {
    // Mostrar loading
    articleResult.classList.add('hidden');
    articleLoading.classList.remove('hidden');
    generateArticleBtn.disabled = true;
    
    try {
      const res = await fetch('/api/chat.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': window.CSRF_TOKEN
        },
        body: JSON.stringify({ message: prompt }),
        credentials: 'include'
      });
      
      const data = await res.json();
      articleLoading.classList.add('hidden');
      generateArticleBtn.disabled = false;
      
      if (!res.ok) {
        alert('Error al generar el contenido: ' + (data.error?.message || 'Error desconocido'));
        return;
      }
      
      // Mostrar resultado
      articleContent.innerHTML = mdToHtml(data.message.content);
      articleResult.classList.remove('hidden');
      
      // Scroll al resultado
      articleResult.scrollIntoView({ behavior: 'smooth', block: 'start' });
      
    } catch (err) {
      articleLoading.classList.add('hidden');
      generateArticleBtn.disabled = false;
      alert('Error de conexión al generar el contenido');
    }
  }

  // === Copiar contenido ===
  if (copyArticleBtn) {
    copyArticleBtn.addEventListener('click', () => {
      const text = articleContent.innerText;
      navigator.clipboard.writeText(text).then(() => {
        const originalText = copyArticleBtn.innerHTML;
        copyArticleBtn.innerHTML = '<i class="iconoir-check"></i> Copiado';
        setTimeout(() => {
          copyArticleBtn.innerHTML = originalText;
        }, 2000);
      });
    });
  }

  // === Regenerar contenido ===
  if (regenerateArticleBtn) {
    regenerateArticleBtn.addEventListener('click', () => {
      if (lastPrompt) sendPrompt(lastPrompt);
    });
  }
})();
