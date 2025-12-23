/**
 * Gesture: Podcast desde artículo
 * Convierte artículos en podcasts con dos voces usando Gemini TTS
 */
(function() {
  'use strict';

  const GESTURE_TYPE = 'podcast-from-article';

  // === DOM Elements ===
  const podcastForm = document.getElementById('podcast-form');
  const tabBtns = document.querySelectorAll('.tab-btn');
  const tabContents = document.querySelectorAll('.tab-content');
  const articleUrl = document.getElementById('article-url');
  const articleText = document.getElementById('article-text');
  const articlePdf = document.getElementById('article-pdf');
  const pdfFilename = document.getElementById('pdf-filename');
  const generateBtn = document.getElementById('generate-btn');
  
  const progressPanel = document.getElementById('progress-panel');
  const errorPanel = document.getElementById('error-panel');
  const resultPlaceholder = document.getElementById('result-placeholder');
  const podcastResult = document.getElementById('podcast-result');
  
  const progressText = document.getElementById('progress-text');
  const progressDetail = document.getElementById('progress-detail');
  const errorMessage = document.getElementById('error-message');
  
  const audioPlayer = document.getElementById('audio-player');
  const podcastTitle = document.getElementById('podcast-title');
  const podcastSummary = document.getElementById('podcast-summary');
  const podcastDuration = document.getElementById('podcast-duration');
  const podcastScript = document.getElementById('podcast-script');
  const downloadBtn = document.getElementById('download-btn');
  
  const historyList = document.getElementById('history-list');
  const newPodcastBtn = document.getElementById('new-podcast-btn');

  let currentTab = 'url';
  let pdfBase64 = null;
  let lastAudioBlob = null;
  let lastAudioUrl = '';
  let lastTitle = '';

  // === Tab switching ===
  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const tab = btn.dataset.tab;
      currentTab = tab;
      
      tabBtns.forEach(b => {
        b.classList.remove('bg-orange-100', 'text-orange-700', 'active');
        b.classList.add('bg-slate-100', 'text-slate-600');
      });
      btn.classList.remove('bg-slate-100', 'text-slate-600');
      btn.classList.add('bg-orange-100', 'text-orange-700', 'active');
      
      tabContents.forEach(content => content.classList.add('hidden'));
      document.getElementById(`tab-${tab}`).classList.remove('hidden');
    });
  });

  // === PDF file handling ===
  if (articlePdf) {
    articlePdf.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;
      
      if (file.type !== 'application/pdf') {
        alert('Por favor, selecciona un archivo PDF');
        return;
      }
      
      const reader = new FileReader();
      reader.onload = (event) => {
        pdfBase64 = event.target.result.split(',')[1];
        pdfFilename.textContent = `📄 ${file.name}`;
        pdfFilename.classList.remove('hidden');
      };
      reader.readAsDataURL(file);
    });
  }

  // === Form submit ===
  if (podcastForm) {
    podcastForm.addEventListener('submit', (e) => {
      e.preventDefault();
      generatePodcast();
    });
  }

  // === Generate podcast ===
  async function generatePodcast() {
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

    showProgress();
    updateProgress('Extrayendo contenido...', 'Analizando la fuente');

    try {
      updateProgress('Generando guion...', 'Creando diálogo entre Ana y Carlos (1-2 min)');
      
      const response = await fetch('/api/gestures/podcast.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
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
        throw new Error(data.error?.message || data.message || 'Error al generar el podcast');
      }

      updateProgress('Sintetizando audio...', 'Convirtiendo texto a voz con IA');

      const audioUrl = data.audio.url;
      if (!audioUrl) {
        throw new Error('No se recibió URL del audio');
      }

      // Fetch blob para descarga
      const blobResp = await fetch(audioUrl, { credentials: 'include' });
      lastAudioBlob = await blobResp.blob();
      lastAudioUrl = audioUrl;
      lastTitle = data.title || 'Podcast';

      // Update UI
      audioPlayer.src = audioUrl;
      podcastTitle.textContent = data.title || 'Podcast generado';
      podcastSummary.textContent = data.summary || '';
      podcastScript.textContent = formatScript(data.script);
      
      const durationMinutes = Math.ceil((data.audio.duration_estimate || 0) / 60);
      podcastDuration.textContent = durationMinutes > 0 ? `~${durationMinutes} min` : '';

      showResult();
      loadHistory(); // Refresh history

    } catch (error) {
      console.error('Error:', error);
      showError(error.message);
    }
  }

  // === Download ===
  if (downloadBtn) {
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
  }

  // === New podcast button ===
  if (newPodcastBtn) {
    newPodcastBtn.addEventListener('click', resetUI);
  }

  // === UI helpers ===
  function showProgress() {
    progressPanel.classList.remove('hidden');
    errorPanel.classList.add('hidden');
    generateBtn.disabled = true;
    generateBtn.innerHTML = '<i class="iconoir-refresh animate-spin"></i> Generando...';
  }

  function updateProgress(text, detail) {
    progressText.textContent = text;
    progressDetail.textContent = detail;
  }

  function showResult() {
    progressPanel.classList.add('hidden');
    errorPanel.classList.add('hidden');
    resultPlaceholder.classList.add('hidden');
    podcastResult.classList.remove('hidden');
    generateBtn.disabled = false;
    generateBtn.innerHTML = '<i class="iconoir-sparks"></i> <span>Generar Podcast</span>';
  }

  function showError(message) {
    progressPanel.classList.add('hidden');
    errorPanel.classList.remove('hidden');
    errorMessage.textContent = message;
    generateBtn.disabled = false;
    generateBtn.innerHTML = '<i class="iconoir-sparks"></i> <span>Generar Podcast</span>';
  }

  function resetUI() {
    progressPanel.classList.add('hidden');
    errorPanel.classList.add('hidden');
    resultPlaceholder.classList.remove('hidden');
    podcastResult.classList.add('hidden');
    generateBtn.disabled = false;
    generateBtn.innerHTML = '<i class="iconoir-sparks"></i> <span>Generar Podcast</span>';
    
    articleUrl.value = '';
    articleText.value = '';
    if (articlePdf) articlePdf.value = '';
    pdfBase64 = null;
    pdfFilename.classList.add('hidden');
    
    audioPlayer.src = '';
    lastAudioBlob = null;
    lastAudioUrl = '';
    lastTitle = '';
  }

  // === HISTORY ===
  loadHistory();

  async function loadHistory() {
    try {
      const res = await fetch(`/api/gestures/history.php?type=${GESTURE_TYPE}`, {
        credentials: 'include'
      });
      const data = await res.json();

      if (!res.ok) {
        historyList.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Error al cargar</div>';
        return;
      }

      renderHistory(data.items || []);
    } catch (err) {
      historyList.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Error de conexión</div>';
    }
  }

  function renderHistory(items) {
    if (items.length === 0) {
      historyList.innerHTML = `
        <div class="p-6 text-center">
          <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
            <i class="iconoir-podcast text-xl text-orange-400"></i>
          </div>
          <p class="text-sm text-slate-500">Aún no has creado podcasts</p>
          <p class="text-xs text-slate-400 mt-1">Usa el formulario para empezar</p>
        </div>
      `;
      return;
    }

    historyList.innerHTML = items.map(item => {
      const timeAgo = formatTimeAgo(new Date(item.created_at));
      const inputData = item.input_data || {};
      const sourceIcon = inputData.source_type === 'url' ? 'iconoir-link' : 
                         inputData.source_type === 'pdf' ? 'iconoir-page' : 'iconoir-text';

      return `
        <div class="history-item w-full p-3 hover:bg-slate-50 border-b border-slate-100 transition-colors group flex items-start gap-2" data-id="${item.id}">
          <i class="${sourceIcon} text-orange-500 mt-0.5"></i>
          <div class="flex-1 min-w-0 cursor-pointer history-item-main">
            <p class="text-sm font-medium text-slate-700 truncate group-hover:text-orange-600">${escapeHtml(item.title)}</p>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-[10px] text-slate-400">${timeAgo}</span>
            </div>
          </div>
          <button class="history-item-delete opacity-0 group-hover:opacity-100 transition-opacity text-slate-300 hover:text-red-500 p-1 rounded" title="Eliminar">
            <i class="iconoir-trash"></i>
          </button>
        </div>
      `;
    }).join('');

    // Event listeners
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
      const res = await fetch(`/api/gestures/get.php?id=${id}`, {
        credentials: 'include'
      });
      const data = await res.json();

      if (!res.ok || !data.execution) {
        alert('Error al cargar el podcast');
        return;
      }

      const exec = data.execution;
      const outputData = exec.output_data || {};

      // Mostrar resultado
      podcastTitle.textContent = exec.title || 'Podcast';
      podcastSummary.textContent = outputData.summary || '';
      podcastScript.textContent = formatScript(outputData.script || '');
      
      // Audio
      if (outputData.audio_url) {
        audioPlayer.src = outputData.audio_url;
        lastAudioUrl = outputData.audio_url;
        lastTitle = exec.title || 'Podcast';
        
        // Fetch blob para descarga
        try {
          const blobResp = await fetch(outputData.audio_url, { credentials: 'include' });
          lastAudioBlob = await blobResp.blob();
        } catch (e) {
          lastAudioBlob = null;
        }
      }

      const durationMinutes = Math.ceil((outputData.duration_estimate || 0) / 60);
      podcastDuration.textContent = durationMinutes > 0 ? `~${durationMinutes} min` : '';

      showResult();
    } catch (err) {
      alert('Error al cargar el podcast');
    }
  }

  async function deleteExecution(id) {
    if (!confirm('¿Eliminar este podcast del historial?')) return;

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
      const res = await fetch('/api/gestures/delete.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken
        },
        credentials: 'include',
        body: JSON.stringify({ id })
      });

      if (res.ok) {
        loadHistory();
      }
    } catch (err) {
      alert('Error al eliminar');
    }
  }

  // === Utility functions ===
  function formatScript(script) {
    if (!script) return '';
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

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function formatTimeAgo(date) {
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Ahora';
    if (diffMins < 60) return `Hace ${diffMins} min`;
    if (diffHours < 24) return `Hace ${diffHours}h`;
    if (diffDays < 7) return `Hace ${diffDays}d`;
    return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
  }
})();
