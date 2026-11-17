import { api, h, initNav, formatJour, showMessage } from './app.js';

const id = new URLSearchParams(location.search).get('id');
const infos = document.getElementById('infos');
const form = document.getElementById('reservation');
const msg = document.getElementById('msg');
const slots = document.getElementById('slots');
let etab = null;

const patient = await initNav();
const res = await api('etablissement.php?id=' + encodeURIComponent(id));
if (!res.ok) {
  infos.replaceChildren(h('div', { class: 'message erreur' }, res.data.error || 'Établissement introuvable.'));
  form.hidden = true;
} else {
  etab = res.data.etablissement;
  document.title = etab.nom + ' — RDV-MEDICAL CI';
  infos.replaceChildren(
    h('h2', {}, etab.nom),
    h('p', {}, `${etab.adresse} — ${etab.commune ? etab.commune + ', ' : ''}${etab.ville}`),
    h('p', {}, 'Téléphone : ', etab.telephone || 'non communiqué'),
    h('p', { class: 'muted' }, etab.infos || ''),
  );
  for (const s of etab.services) form.service.append(h('option', { value: s.id }, s.nom));
}

async function chargerDates() {
  slots.replaceChildren();
  form.date.replaceChildren(h('option', { value: '' }, 'Choisir une date'));
  if (!form.service.value) return;
  const { data } = await api('creneaux.php?service_id=' + form.service.value);
  for (const d of data.dates || []) form.date.append(h('option', { value: d }, formatJour(d)));
  if ((data.dates || []).length === 0) showMessage(msg, 'Aucun créneau disponible pour ce service.');
  else showMessage(msg, '');
}

async function chargerCreneaux() {
  slots.replaceChildren();
  if (!form.date.value) return;
  const { data } = await api(`creneaux.php?service_id=${form.service.value}&date=${form.date.value}`);
  for (const c of data.creneaux || []) {
    slots.append(
      h('span', {},
        h('input', { type: 'radio', name: 'creneau', id: 'c' + c.id, value: c.id, required: '' }),
        h('label', { for: 'c' + c.id }, c.debut.slice(11, 16)),
      ),
    );
  }
}

form.service.addEventListener('change', chargerDates);
form.date.addEventListener('change', chargerCreneaux);

form.addEventListener('submit', async (ev) => {
  ev.preventDefault();
  const choisi = form.querySelector('input[name="creneau"]:checked');
  if (!choisi) { showMessage(msg, 'Choisissez un créneau.'); return; }
  if (!patient) {
    location.href = 'connexion.html?next=' + encodeURIComponent('etablissement.html?id=' + id);
    return;
  }
  const r = await api('reserver.php', { method: 'POST', body: { creneau_id: Number(choisi.value), motif: form.motif.value } });
  if (r.ok) {
    location.href = 'confirmation.html?ref=' + encodeURIComponent(r.data.rendez_vous.reference);
  } else {
    showMessage(msg, r.data.error || 'Erreur lors de la réservation.');
    if (r.status === 409) chargerCreneaux();
  }
});

if (etab) {
  if (!patient) {
    document.getElementById('invite-connexion').hidden = false;
  }
  await chargerDates();
}
