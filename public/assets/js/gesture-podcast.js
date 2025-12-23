/**
 * Gesture: Podcast desde artículo
 * Convierte artículos en podcasts con dos voces usando Gemini TTS
 */
document.addEventListener('DOMContentLoaded', () => {
  // === DOM Elements ===
  const tabBtns = document.querySelectorAll('.tab-btn');
  const tabContents = document.querySelectorAll('.tab-content');
  const articleUrl = document.getElementById('article-url');
  const articleText = document.getElementById('article-text');
  const articlePdf = document.getElementById('article-pdf');
  const pdfFilename = document.getElementById('pdf-filename');
  const generateBtn = document.getElementById('generate-btn');
  
  const inputSection = document.getElementById('input-section');
  const progressSection = document.getElementById('progress-section');
  const resultSection = document.getElementById('result-section');
  const errorSection = document.getElementById('error-section');
  
  const progressText = document.getElementById('progress-text');
  const progressDetail = document.getElementById('progress-detail');
  
  const audioPlayer = document.getElementById('audio-player');
  const podcastTitle = document.getElementById('podcast-title');
  const podcastSummary = document.getElementById('podcast-summary');
  const podcastDuration = document.getElementById('podcast-duration');
  const podcastScript = document.getElementById('podcast-script');
  const downloadBtn = document.getElementById('download-btn');
  const newPodcastBtn = document.getElementById('new-podcast-btn');
  const newPodcastSidebarBtn = document.getElementById('new-podcast-sidebar-btn');
  const retryBtn = document.getElementById('retry-btn');
  const errorMessage = document.getElementById('error-message');
  const historyList = document.getElementById('history-list');

  let currentTab = 'url';
  let pdfBase64 = null;
  let lastAudioBlob = null;
  let lastTitle = '';

  // === Tab switching ===
  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const tab = btn.dataset.tab;
      currentTab = tab;
      
      // Update tab buttons
      tabBtns.forEach(b => {
        b.classList.remove('bg-violet-100', 'text-violet-700');
        b.classList.add('bg-slate-100', 'text-slate-600');
      });
      btn.classList.remove('bg-slate-100', 'text-slate-600');
      btn.classList.add('bg-violet-100', 'text-violet-700');
      
      // Show/hide content
      tabContents.forEach(content => content.classList.add('hidden'));
      document.getElementById(`tab-${tab}`).classList.remove('hidden');
    });
  });

  // === PDF file handling ===
  articlePdf.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    if (file.type !== 'application/pdf') {
      alert('Por favor, selecciona un archivo PDF');
      return;
    }
    
    const reader = new FileReader();
    reader.onload = (event) => {
      const base64 = event.target.result.split(',')[1];
      pdfBase64 = base64;
      pdfFilename.textContent = `Archivo: ${file.name}`;
      pdfFilename.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
  });

  // === Generate podcast ===
  generateBtn.addEventListener('click', generatePodcast);

  async function generatePodcast() {
    // Get input based on current tab
    let sourceType = currentTab;
    let payload = { source_type: sourceType, action: 'full' };
    
    switch (sourceType) {
      case 'url':
        const url = articleUrl.value.trim();
        if (!url) {
          alert('Por favor, introduce una URL');
          return;
        }
        payload.url = url;
        break;
        
      case 'text':
        const text = articleText.value.trim();
        if (!text) {
          alert('Por favor, introduce el texto del artículo');
          return;
        }
        payload.text = text;
        break;
        
      case 'pdf':
        if (!pdfBase64) {
          alert('Por favor, selecciona un archivo PDF');
          return;
        }
        payload.pdf_base64 = pdfBase64;
        break;
    }

    // Show progress
    showProgress();
    updateProgress('Extrayendo contenido del artículo...', 'Analizando la fuente');

    try {
      // Call API
      updateProgress('Generando guion del podcast...', 'Creando diálogo entre Ana y Carlos');
      
      const response = await fetch('/api/gestures/podcast.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify(payload)
      });

      let data;
      try {
        data = await response.json();
      } catch (e) {
        throw new Error('El servidor devolvió una respuesta vacía o no-JSON');
      }

      if (!response.ok || !data.success) {
        const errorMsg = data.error?.message || data.message || 'Error al generar el podcast';
        throw new Error(errorMsg);
      }

      updateProgress('Sintetizando audio...', 'Convirtiendo texto a voz con IA');

      // Process audio (usar URL para no cargar base64 gigante en memoria)
      const audioUrl = data.audio.url;
      if (!audioUrl) {
        throw new Error('No se recibió URL del audio');
      }

      // Mantener para descarga (fetch y blob)
      const blobResp = await fetch(audioUrl, { credentials: 'include' });
      const audioBlob = await blobResp.blob();
      lastAudioBlob = audioBlob;
      lastTitle = data.title || 'Podcast';

      // Update UI
      audioPlayer.src = audioUrl;
      podcastTitle.textContent = data.title || 'Podcast generado';
      podcastSummary.textContent = data.summary || '';
      podcastScript.textContent = formatScript(data.script);
      
      // Estimate duration
      const durationMinutes = Math.ceil((data.audio.duration_estimate || 0) / 60);
      podcastDuration.textContent = durationMinutes > 0 ? `~${durationMinutes} min` : '';

      showResult();

    } catch (error) {
      console.error('Error:', error);
      showError(error.message);
    }
  }

  // === Download ===
  downloadBtn.addEventListener('click', () => {
    if (!lastAudioBlob) return;
    
    const url = URL.createObjectURL(lastAudioBlob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `podcast-${slugify(lastTitle)}.wav`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  });

  // === New podcast ===
  newPodcastBtn.addEventListener('click', resetUI);
  if (newPodcastSidebarBtn) newPodcastSidebarBtn.addEventListener('click', resetUI);
  retryBtn.addEventListener('click', resetUI);

  // === Historial ===
  const GESTURE_TYPE = window.GESTURE_TYPE || 'podcast-from-article';
  const CSRF_TOKEN = window.CSRF_TOKEN || '';
  loadHistory();

  async function loadHistory() {
    if (!historyList) return;
    historyList.innerHTML = `
      <div class="p-4 text-center text-slate-400 text-sm">
        <i class="iconoir-refresh animate-spin"></i>
        Cargando...
      </div>`;
    try {
      const res = await fetch(`/api/gestures/history.php?type=${GESTURE_TYPE}`, {
        credentials: 'include'
      });
      let data;
      try {
        data = await res.json();
      } catch (e) {
        const text = await res.text();
        historyList.innerHTML = `<div class="p-4 text-center text-red-500 text-sm">Error al leer historial</div><pre class="text-[10px] text-slate-400 p-3 overflow-auto">${escapeHtml(text || '')}</pre>`;
        return;
      }
      if (!res.ok || data.error) {
        const msg = data.error?.message || 'Error al cargar';
        historyList.innerHTML = `<div class="p-4 text-center text-red-500 text-sm">${escapeHtml(msg)}</div>`;
        return;
      }
      renderHistory(data.items || []);
    } catch (err) {
      historyList.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Error de conexión</div>';
    }
  }

  function renderHistory(items) {
    if (!historyList) return;
    if (items.length === 0) {
      historyList.innerHTML = `
        <div class="p-6 text-center">
          <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
            <i class="iconoir-podcast text-xl text-slate-400"></i>
          </div>
          <p class="text-sm text-slate-500">Aún no hay podcasts generados</p>
        </div>`;
      return;
    }

    historyList.innerHTML = items.map(item => {
      const timeAgo = formatTimeAgo(new Date(item.created_at));
      const title = escapeHtml(item.title || 'Podcast');
      return `
        <div class="history-item w-full p-3 hover:bg-slate-50 border-b border-slate-100 transition-colors group flex items-start gap-2" data-id="${item.id}">
          <i class="iconoir-podcast text-rose-500 mt-0.5"></i>
          <div class="flex-1 min-w-0 cursor-pointer history-item-main">
            <p class="text-sm font-medium text-slate-700 truncate group-hover:text-rose-600">${title}</p>
            <span class="text-[10px] text-slate-400">${timeAgo}</span>
          </div>
          <button class="history-item-delete opacity-0 group-hover:opacity-100 transition-opacity text-slate-300 hover:text-red-500 p-1 rounded" title="Eliminar">
            <i class="iconoir-trash"></i>
          </button>
        </div>`;
    }).join('');

    historyList.querySelectorAll('.history-item-main').forEach(el => {
      const id = el.parentElement.dataset.id;
      el.addEventListener('click', () => loadExecution(id));
    });
    historyList.querySelectorAll('.history-item-delete').forEach(btn => {
      const id = btn.parentElement.dataset.id;
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        deleteExecution(id);
      });
    });
  }

  async function loadExecution(id) {
    try {
      const res = await fetch(`/api/gestures/get.php?id=${id}`, { credentials: 'include' });
      const data = await res.json();
      if (!res.ok) {
        alert(data.error?.message || 'No se pudo cargar el historial');
        return;
      }
      const exec = data.execution;
      const input = exec.input_data || {};

      // Mostrar resultado guardado (script y summary)
      podcastTitle.textContent = exec.title || 'Podcast generado';
      podcastSummary.textContent = input.summary || '';
      podcastScript.textContent = formatScript(exec.output_content || '');
      audioPlayer.src = ''; // No tenemos audio histórico guardado
      podcastDuration.textContent = '';
      resultSection.classList.remove('hidden');
      inputSection.classList.add('hidden');
      progressSection.classList.add('hidden');
      errorSection.classList.add('hidden');

    } catch (err) {
      alert('Error al cargar el historial');
    }
  }

  async function deleteExecution(id) {
    try {
      const res = await fetch('/api/gestures/delete.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN
        },
        credentials: 'include',
        body: JSON.stringify({ id: Number(id) })
      });
      const data = await res.json();
      if (!res.ok) {
        alert(data.error?.message || 'No se pudo eliminar');
        return;
      }
      loadHistory();
    } catch (err) {
      alert('Error de conexión al eliminar');
    }
  }

  // === UI helpers ===
  function showProgress() {
    inputSection.classList.add('opacity-50', 'pointer-events-none');
    progressSection.classList.remove('hidden');
    resultSection.classList.add('hidden');
    errorSection.classList.add('hidden');
    generateBtn.disabled = true;
  }

  function updateProgress(text, detail) {
    progressText.textContent = text;
    progressDetail.textContent = detail;
  }

  function showResult() {
    inputSection.classList.add('hidden');
    progressSection.classList.add('hidden');
    resultSection.classList.remove('hidden');
    errorSection.classList.add('hidden');
  }

  function showError(message) {
    inputSection.classList.remove('opacity-50', 'pointer-events-none');
    progressSection.classList.add('hidden');
    resultSection.classList.add('hidden');
    errorSection.classList.remove('hidden');
    errorMessage.textContent = message;
    generateBtn.disabled = false;
  }

  function resetUI() {
    inputSection.classList.remove('hidden', 'opacity-50', 'pointer-events-none');
    progressSection.classList.add('hidden');
    resultSection.classList.add('hidden');
    errorSection.classList.add('hidden');
    generateBtn.disabled = false;
    
    // Clear inputs
    articleUrl.value = '';
    articleText.value = '';
    articlePdf.value = '';
    pdfBase64 = null;
    pdfFilename.classList.add('hidden');
    
    // Reset audio
    audioPlayer.src = '';
    lastAudioBlob = null;
    lastTitle = '';

    loadHistory();
  }

  // === Utility functions ===
  function base64ToBlob(base64, mimeType) {
    const byteCharacters = atob(base64);
    const byteNumbers = new Array(byteCharacters.length);
    for (let i = 0; i < byteCharacters.length; i++) {
      byteNumbers[i] = byteCharacters.charCodeAt(i);
    }
    const byteArray = new Uint8Array(byteNumbers);
    return new Blob([byteArray], { type: mimeType });
  }

  function formatScript(script) {
    if (!script) return '';
    // Add line breaks between speakers for readability
    return script.replace(/\n(Ana:|Carlos:)/g, '\n\n$1');
  }

  function slugify(text) {
    return text
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)/g, '')
      .substring(0, 50);
  }
});
