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

      const data = await response.json();

      if (!response.ok || !data.success) {
        const errorMsg = data.error?.message || data.message || 'Error al generar el podcast';
        throw new Error(errorMsg);
      }

      updateProgress('Sintetizando audio...', 'Convirtiendo texto a voz con IA');

      // Process audio
      const audioData = data.audio.data;
      const audioBlob = base64ToBlob(audioData, 'audio/wav');
      const audioUrl = URL.createObjectURL(audioBlob);
      
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
