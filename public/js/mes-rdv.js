import { api, h, initNav, formatDateHeure, showMessage } from './app.js';

const aVenir = document.getElementById('a-venir');
const historique = document.getElementById('historique');
const msg = document.getElementById('msg');

function carte(r, annulable) {
  return h('article', { class: 'card rdv' },
    h('div', {},
      h('h3', {}, `${r.service} — ${r.etablissement}`),
      h('div', {}, formatDateHeure(r.debut)),
      h('div', { class: 'muted' }, `${r.adresse}, ${r.ville}`),
      h('div', { class: 'muted' }, `Référence : ${r.reference} — Motif : ${r.motif}`),
    ),
    h('div', {},
      h('span', { class: 'statut ' + r.statut }, r.statut === 'confirme' ? 'Confirmé' : 'Annulé'),
      annulable ? h('div', {}, h('button', {
        class: 'btn danger petit',
        type: 'button',
        onclick: () => annuler(r),
      }, 'Annuler')) : null,
    ),
  );
}

async function annuler(r) {
  if (!window.confirm(`Annuler le rendez-vous du ${formatDateHeure(r.debut)} ?`)) return;
  const res = await api('annuler.php', { method: 'POST', body: { rdv_id: r.id } });
  if (res.ok) { showMessage(msg, 'Rendez-vous annulé.', 'succes'); await charger(); }
  else showMessage(msg, res.data.error || 'Annulation impossible.');
}

async function charger() {
  const { ok, status, data } = await api('mes_rdv.php');
  if (status === 401) { location.href = 'connexion.html?next=mes-rdv.html'; return; }
  if (!ok) { showMessage(msg, data.error); return; }
  const futurs = data.rendez_vous.filter((r) => r.a_venir);
  const passes = data.rendez_vous.filter((r) => !futurs.includes(r)).reverse();
  aVenir.replaceChildren(...(futurs.length
    ? futurs.map((r) => carte(r, true))
    : [h('p', { class: 'muted' }, 'Aucun rendez-vous à venir. '), h('a', { class: 'btn', href: 'index.html' }, 'Prendre un rendez-vous')]));
  historique.replaceChildren(...(passes.length
    ? passes.map((r) => carte(r, false))
    : [h('p', { class: 'muted' }, 'Aucun historique.')]));
}

await initNav();
await charger();
