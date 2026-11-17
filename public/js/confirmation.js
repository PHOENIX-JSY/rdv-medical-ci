import { api, h, initNav, formatDateHeure } from './app.js';

const zone = document.getElementById('recap');
const ref = new URLSearchParams(location.search).get('ref') || '';

await initNav();
const { ok, status, data } = await api('mes_rdv.php?reference=' + encodeURIComponent(ref));
if (status === 401) {
  location.href = 'connexion.html?next=' + encodeURIComponent('confirmation.html?ref=' + ref);
} else if (!ok || data.rendez_vous.length === 0) {
  zone.replaceChildren(h('div', { class: 'message erreur' }, 'Rendez-vous introuvable.'));
} else {
  const r = data.rendez_vous[0];
  zone.replaceChildren(
    h('div', { class: 'message succes' }, 'Votre rendez-vous est confirmé.'),
    h('p', { class: 'muted' }, 'Référence de réservation'),
    h('div', { class: 'ref' }, r.reference),
    h('p', {}, h('strong', {}, `${r.service} — ${r.etablissement}`)),
    h('p', {}, formatDateHeure(r.debut)),
    h('p', { class: 'muted' }, `${r.adresse}, ${r.ville} — Tél. ${r.telephone || 'non communiqué'}`),
    h('p', { class: 'muted' }, 'À prévoir : une pièce d\'identité et votre référence. Présentez-vous 15 minutes avant l\'heure.'),
    h('a', { class: 'btn primaire', href: 'mes-rdv.html' }, 'Voir mes rendez-vous'),
  );
}
