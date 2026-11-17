import { api, h, initNav } from './app.js';

const form = document.getElementById('recherche');
const resultats = document.getElementById('liste');

async function chargerFiltres() {
  const { data } = await api('filtres.php');
  for (const v of data.villes || []) form.ville.append(h('option', { value: v }, v));
  for (const s of data.services || []) form.service.append(h('option', { value: s }, s));
}

async function rechercher() {
  const params = new URLSearchParams({ ville: form.ville.value, service: form.service.value, q: form.q.value });
  const { ok, data } = await api('etablissements.php?' + params);
  resultats.replaceChildren();
  if (!ok) {
    resultats.append(h('div', { class: 'message erreur' }, data.error || 'Erreur de chargement.'));
    return;
  }
  if (data.etablissements.length === 0) {
    resultats.append(h('p', { class: 'muted' }, 'Aucun établissement ne correspond à votre recherche.'));
    return;
  }
  for (const e of data.etablissements) {
    resultats.append(
      h('article', { class: 'card etab' },
        h('div', {},
          h('h3', {}, e.nom),
          h('div', { class: 'muted' }, `${e.adresse} — ${e.ville}`),
          h('div', { class: 'tags' }, e.services.map((s) => h('span', { class: 'tag' }, s))),
        ),
        h('a', { class: 'btn primaire', href: 'etablissement.html?id=' + e.id }, 'Voir détails'),
      ),
    );
  }
}

form.addEventListener('submit', (ev) => { ev.preventDefault(); rechercher(); });

const patient = await initNav();
if (new URLSearchParams(location.search).get('bienvenue') && patient) {
  document.getElementById('bienvenue').hidden = false;
  document.getElementById('bienvenue-nom').textContent = patient.nom;
}
await chargerFiltres();
await rechercher();
