// Fonctions communes à toutes les pages (aucune dépendance externe)

export async function api(path, { method = 'GET', body } = {}) {
  const opts = { method, headers: {}, credentials: 'same-origin' };
  if (body !== undefined) {
    opts.headers['Content-Type'] = 'application/json';
    opts.body = JSON.stringify(body);
  }
  try {
    const res = await fetch('api/' + path, opts);
    let data = {};
    try { data = await res.json(); } catch { /* réponse non JSON */ }
    return { ok: res.ok, status: res.status, data };
  } catch {
    return { ok: false, status: 0, data: { error: 'Serveur injoignable.' } };
  }
}

/** Crée un élément DOM ; le texte est inséré via textContent (pas de XSS). */
export function h(tag, attrs = {}, ...children) {
  const el = document.createElement(tag);
  for (const [k, v] of Object.entries(attrs)) {
    if (k === 'class') el.className = v;
    else if (k.startsWith('on')) el.addEventListener(k.slice(2), v);
    else el.setAttribute(k, v);
  }
  for (const c of children.flat()) {
    if (c !== null && c !== undefined) el.append(c instanceof Node ? c : document.createTextNode(String(c)));
  }
  return el;
}

export function formatJour(dateStr) {
  const d = new Date(dateStr.slice(0, 10) + 'T12:00:00');
  return d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}

export function formatDateHeure(debut) {
  return `${formatJour(debut)} à ${debut.slice(11, 16)}`;
}

export function showMessage(container, text, type = 'erreur') {
  container.replaceChildren();
  if (text) container.append(h('div', { class: 'message ' + type, role: type === 'erreur' ? 'alert' : 'status' }, text));
}

/** N'autorise que des redirections vers une page du site. */
export function safeNext(fallback = 'index.html') {
  const next = new URLSearchParams(location.search).get('next') || '';
  return /^[a-z0-9_-]+\.html(\?[\w=&%.-]*)?$/i.test(next) ? next : fallback;
}

/** Affiche le menu de navigation selon l'état de connexion. */
export async function initNav() {
  const nav = document.getElementById('nav');
  const { data } = await api('me.php');
  const patient = data.patient;
  nav.replaceChildren(
    h('a', { href: 'index.html' }, 'Accueil'),
    h('a', { href: 'index.html#resultats' }, 'Hôpitaux'),
    h('a', { href: 'mes-rdv.html' }, 'Mes rendez-vous'),
    ...(patient
      ? [
        h('span', { class: 'muted' }, patient.nom),
        h('button', {
          class: 'lien',
          type: 'button',
          onclick: async () => { await api('logout.php', { method: 'POST', body: {} }); location.href = 'index.html'; },
        }, 'Déconnexion'),
      ]
      : [h('a', { href: 'connexion.html' }, 'Connexion'), h('a', { href: 'inscription.html' }, 'Inscription')]),
  );
  return patient;
}
