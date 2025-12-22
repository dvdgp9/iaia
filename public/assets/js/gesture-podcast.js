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
  const retryBtn = document.getElementById('retry-btn');
  const errorMessage = document.getElementById('error-message');

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

  async function safeJson(response) {
    const contentType = response.headers.get('content-type') || '';
    if (contentType.includes('application/json')) {
      return await response.json();
    }
    const text = await response.text();
    // Cuando hay 504, algunos proxies devuelven HTML
    throw new Error(text.slice(0, 200) || 'Respuesta no JSON');
  }

  function splitScriptIntoChunks(script, maxChars) {
    if (!script) return [];
    const lines = script.split('\n').map(l => l.trim()).filter(Boolean);
    const chunks = [];
    let current = '';

    for (const line of lines) {
      // Intentar respetar límites de tamaño
      const next = current ? (current + '\n' + line) : line;
      if (next.length > maxChars && current) {
        chunks.push(current);
        current = line;
      } else {
        current = next;
      }
    }

    if (current) chunks.push(current);
    return chunks;
  }

  function base64ToUint8Array(base64) {
    const binary = atob(base64);
    const len = binary.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) bytes[i] = binary.charCodeAt(i);
    return bytes;
  }

  function concatUint8Arrays(arrays) {
    const total = arrays.reduce((sum, a) => sum + a.length, 0);
    const out = new Uint8Array(total);
    let offset = 0;
    for (const a of arrays) {
      out.set(a, offset);
      offset += a.length;
    }
    return out;
  }

  function pcmToWavBlob(pcmUint8, sampleRate = 24000, channels = 1, bitDepth = 16) {
    const blockAlign = channels * (bitDepth / 8);
    const byteRate = sampleRate * blockAlign;
    const dataSize = pcmUint8.length;
    const buffer = new ArrayBuffer(44 + dataSize);
    const view = new DataView(buffer);

    // RIFF header
    writeString(view, 0, 'RIFF');
    view.setUint32(4, 36 + dataSize, true);
    writeString(view, 8, 'WAVE');

    // fmt chunk
    writeString(view, 12, 'fmt ');
    view.setUint32(16, 16, true); // PCM
    view.setUint16(20, 1, true); // audio format = PCM
    view.setUint16(22, channels, true);
    view.setUint32(24, sampleRate, true);
    view.setUint32(28, byteRate, true);
    view.setUint16(32, blockAlign, true);
    view.setUint16(34, bitDepth, true);

    // data chunk
    writeString(view, 36, 'data');
    view.setUint32(40, dataSize, true);

    // PCM data
    new Uint8Array(buffer, 44).set(pcmUint8);
    return new Blob([buffer], { type: 'audio/wav' });
  }

  function writeString(view, offset, str) {
    for (let i = 0; i < str.length; i++) {
      view.setUint8(offset + i, str.charCodeAt(i));
    }
  }

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
      // === FASE 1: generar guion (rápido) ===
      updateProgress('Generando guion del podcast...', 'Creando diálogo entre Ana y Carlos');

      const scriptPayload = { ...payload, action: 'script' };
      const scriptRes = await fetch('/api/gestures/podcast.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(scriptPayload)
      });

      const scriptData = await safeJson(scriptRes);
      if (!scriptRes.ok || !scriptData?.success) {
        const errorMsg = scriptData?.error?.message || scriptData?.message || 'Error al generar el guion';
        throw new Error(errorMsg);
      }

      const title = scriptData.title || 'Podcast generado';
      const summary = scriptData.summary || '';
      const script = scriptData.script || '';
      const speaker1 = scriptData.speaker1 || 'Ana';
      const speaker2 = scriptData.speaker2 || 'Carlos';

      // === FASE 2: generar audio por chunks (evita timeout) ===
      updateProgress('Sintetizando audio...', 'Generando audio por partes (puede tardar 2-4 min)');

      const chunks = splitScriptIntoChunks(script, 1200);
      if (!chunks.length) {
        throw new Error('Guion vacío: no se puede generar audio');
      }

      let pcmParts = [];
      let audioMeta = null;

      for (let i = 0; i < chunks.length; i++) {
        updateProgress('Sintetizando audio...', `Parte ${i + 1} de ${chunks.length}`);

        const chunkPayload = {
          action: 'audio_chunk',
          source_type: payload.source_type,
          audio_chunk: chunks[i],
          speaker1,
          speaker2,
          voice1: 'Aoede',
          voice2: 'Orus'
        };

        const audioRes = await fetch('/api/gestures/podcast.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
          body: JSON.stringify(chunkPayload)
        });

        const audioData = await safeJson(audioRes);
        if (!audioRes.ok || !audioData?.success) {
          const errorMsg = audioData?.error?.message || audioData?.message || `Error generando audio (parte ${i + 1})`;
          throw new Error(errorMsg);
        }

        const pcmBase64 = audioData.audio?.pcm_base64;
        if (!pcmBase64) {
          throw new Error(`Respuesta sin audio (parte ${i + 1})`);
        }

        if (!audioMeta) {
          audioMeta = {
            sampleRate: audioData.audio?.sample_rate || 24000,
            channels: audioData.audio?.channels || 1,
            bitDepth: audioData.audio?.bit_depth || 16
          };
        }

        pcmParts.push(base64ToUint8Array(pcmBase64));
      }

      const pcmAll = concatUint8Arrays(pcmParts);
      const wavBlob = pcmToWavBlob(pcmAll, audioMeta.sampleRate, audioMeta.channels, audioMeta.bitDepth);
      const wavUrl = URL.createObjectURL(wavBlob);

      lastAudioBlob = wavBlob;
      lastTitle = title;

      // Update UI
      audioPlayer.src = wavUrl;
      podcastTitle.textContent = title;
      podcastSummary.textContent = summary;
      podcastScript.textContent = formatScript(script);

      // Duración estimada (si viene)
      const durationMinutes = Math.ceil((scriptData.estimated_duration || 0) / 60);
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
  retryBtn.addEventListener('click', resetUI);

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
