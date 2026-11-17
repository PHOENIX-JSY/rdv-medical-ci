import { api, initNav, safeNext, showMessage } from './app.js';

const form = document.getElementById('auth');
const msg = document.getElementById('msg');
const inscription = form.dataset.mode === 'inscription';

await initNav();

form.addEventListener('submit', async (ev) => {
  ev.preventDefault();
  const body = { telephone: form.telephone.value, mot_de_passe: form.mot_de_passe.value };
  if (inscription) body.nom = form.nom.value;
  const r = await api(inscription ? 'register.php' : 'login.php', { method: 'POST', body });
  if (r.ok) {
    const next = new URLSearchParams(location.search).get('next');
    location.href = next ? safeNext() : inscription ? 'index.html?bienvenue=1' : 'index.html';
  } else {
    showMessage(msg, r.data.error || 'Une erreur est survenue.');
  }
});
