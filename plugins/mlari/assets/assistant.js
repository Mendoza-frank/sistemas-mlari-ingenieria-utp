(() => {
  'use strict';
  const root = document.getElementById('mlari-assistant');
  if (!root) return;
  root.innerHTML = '<button class="mlari-ai-toggle" type="button" aria-expanded="false">✦ MLARI IA</button><section class="mlari-ai-panel" hidden><header><strong>✦ MLARI IA — Asistente de Redes</strong><button class="mlari-ai-close" type="button" aria-label="Cerrar">×</button></header><div class="mlari-ai-history" role="log" aria-live="polite"></div><div class="mlari-ai-form"><label for="mlari-ai-input">Pregunta sobre tus dispositivos y métricas</label><div><input id="mlari-ai-input" maxlength="1000" autocomplete="off" placeholder="Ej.: ¿Cuál es el estado de R8?"><button type="button">Enviar</button></div></div></section>';
  const toggle = root.querySelector('.mlari-ai-toggle');
  const panel = root.querySelector('.mlari-ai-panel');
  const history = root.querySelector('.mlari-ai-history');
  const form = root.querySelector('.mlari-ai-form');
  const input = root.querySelector('input');
  const submit = form.querySelector('button');
  const setOpen = open => { panel.hidden = !open; toggle.setAttribute('aria-expanded', String(open)); if (open) input.focus(); };
  toggle.addEventListener('click', () => setOpen(panel.hidden));
  root.querySelector('.mlari-ai-close').addEventListener('click', () => setOpen(false));
  const add = (role, text) => {
    const p = document.createElement('p'); p.className = 'mlari-ai-message ' + role;
    const pattern = /\[([^\]\n]{1,90})\]\((\/cacti\/graph\.php\?action=view&local_graph_id=\d+)\)/g;
    if (role === 'assistant') {
      let start = 0, match;
      while ((match = pattern.exec(text)) !== null) {
        p.append(document.createTextNode(text.slice(start, match.index)));
        const a = document.createElement('a'); a.href = match[2]; a.textContent = match[1]; a.target = '_blank'; a.rel = 'noopener'; p.append(a);
        start = pattern.lastIndex;
      }
      p.append(document.createTextNode(text.slice(start)));
    } else p.textContent = text;
    history.append(p); history.scrollTop = history.scrollHeight; return p;
  };
  fetch(root.dataset.api,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},body:new URLSearchParams({action:'history',__csrf_magic:root.dataset.csrf})})
    .then(r=>r.ok?r.json():null).then(data=>{for(const turn of data?.history||[]) add(turn.role==='user'?'user':'assistant',turn.text||'');}).catch(()=>{});
  const send = async () => {
    const message = input.value.trim();
    if (!message || submit.disabled) return;
    add('user', message); input.value = ''; submit.disabled = true;
    const pending = add('system', 'Consultando MLARI y Gemini…');
    try {
      const body = new URLSearchParams({message,__csrf_magic:root.dataset.csrf});
      const response = await fetch(root.dataset.api, {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},body});
      const data = await response.json();
      pending.remove();
      if (!response.ok || data.error) add('system', data.error || 'No se pudo completar la consulta.');
      else add('assistant', data.answer + '\n\nDatos consultados: ' + data.devices_consulted + ' · ' + data.context_time);
    } catch { pending.remove(); add('system', 'No se pudo conectar con el asistente.'); }
    finally { submit.disabled = false; input.focus(); }
  };
  submit.addEventListener('click', send);
  input.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); send(); } });
})();
