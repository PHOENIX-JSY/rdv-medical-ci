#!/usr/bin/env bash
# Test de bout en bout de l'API : php -S + curl + jq. Utilise une base temporaire.
set -u
cd "$(dirname "$0")/.."
export DB_PATH="/tmp/rdv-test-$$.sqlite"
PORT=8099
BASE="http://localhost:$PORT/api"
php database/init.php --reset >/dev/null
php -S "localhost:$PORT" -t public >/dev/null 2>&1 &
SRV=$!
trap 'kill $SRV 2>/dev/null; rm -f "$DB_PATH" /tmp/jar-a-$$ /tmp/jar-b-$$' EXIT
sleep 1

PASS=0; FAIL=0
check() { # description attendu obtenu
  if [ "$2" == "$3" ]; then PASS=$((PASS+1)); echo "  ok   $1"; else FAIL=$((FAIL+1)); echo "  FAIL $1 (attendu: $2, obtenu: $3)"; fi
}
post() { # jar chemin json -> "status|corps"
  curl -s -o /tmp/body-$$ -w '%{http_code}' -b "$1" -c "$1" -H 'Content-Type: application/json' -d "$3" "$BASE/$2"
}
get() { curl -s -b "$1" -c "$1" "$BASE/$2"; }

A=/tmp/jar-a-$$; B=/tmp/jar-b-$$

echo "Authentification"
check "inscription patient A" 201 "$(post $A register.php '{"nom":"Awa Koné","telephone":"+22507000001","mot_de_passe":"motdepasse1"}')"
check "inscription doublon refusée" 409 "$(post $B register.php '{"nom":"X Y","telephone":"+22507000001","mot_de_passe":"motdepasse1"}')"
check "mot de passe trop court" 422 "$(post $B register.php '{"nom":"X Y","telephone":"+22507000002","mot_de_passe":"court"}')"
check "mauvais mot de passe" 401 "$(post $B login.php '{"telephone":"+22507000001","mot_de_passe":"faux"}')"
check "inscription patient B" 201 "$(post $B register.php '{"nom":"Yao Kouadio","telephone":"+22507000002","mot_de_passe":"motdepasse2"}')"
check "POST sans JSON refusé" 415 "$(curl -s -o /dev/null -w '%{http_code}' -d 'a=1' "$BASE/login.php")"
check "session A active" "Awa Koné" "$(get $A me.php | jq -r .patient.nom)"

echo "Recherche"
check "5 établissements" 5 "$(get $A etablissements.php | jq '.etablissements | length')"
check "filtre ville Bouaké" 1 "$(get $A 'etablissements.php?ville=Bouak%C3%A9' | jq '.etablissements | length')"
check "filtre service Dentaire" 1 "$(get $A 'etablissements.php?service=Dentaire' | jq '.etablissements | length')"
check "fiche établissement" "Centre Hospitalier Les Lagunes" "$(get $A 'etablissement.php?id=1' | jq -r .etablissement.nom)"
check "établissement inconnu" "Établissement introuvable." "$(get $A 'etablissement.php?id=999' | jq -r .error)"

echo "Réservation"
DATE=$(get $A 'creneaux.php?service_id=1' | jq -r '.dates[0]')
CR=$(get $A "creneaux.php?service_id=1&date=$DATE" | jq -r '.creneaux[0].id')
check "créneaux disponibles" true "$([ -n "$CR" ] && [ "$CR" != null ] && echo true || echo false)"
check "réservation sans connexion" 401 "$(curl -s -o /dev/null -w '%{http_code}' -H 'Content-Type: application/json' -d "{\"creneau_id\":$CR,\"motif\":\"x\"}" "$BASE/reserver.php")"
check "motif requis" 422 "$(post $A reserver.php "{\"creneau_id\":$CR,\"motif\":\"\"}")"
check "réservation A" 201 "$(post $A reserver.php "{\"creneau_id\":$CR,\"motif\":\"Contrôle\"}")"
REF=$(jq -r .rendez_vous.reference /tmp/body-$$)
RID=$(jq -r .rendez_vous.id /tmp/body-$$)
check "double réservation refusée" 409 "$(post $B reserver.php "{\"creneau_id\":$CR,\"motif\":\"Autre\"}")"
check "créneau retiré de la liste" 0 "$(get $A "creneaux.php?service_id=1&date=$DATE" | jq "[.creneaux[] | select(.id == $CR)] | length")"

echo "Espace patient"
check "A voit son rendez-vous" "$REF" "$(get $A mes_rdv.php | jq -r '.rendez_vous[0].reference')"
check "B ne voit pas le rendez-vous de A" 0 "$(get $B mes_rdv.php | jq '.rendez_vous | length')"
check "B ne peut pas annuler le RDV de A" 404 "$(post $B annuler.php "{\"rdv_id\":$RID}")"
check "A annule" 200 "$(post $A annuler.php "{\"rdv_id\":$RID}")"
check "statut annulé" annule "$(get $A mes_rdv.php | jq -r '.rendez_vous[0].statut')"
check "créneau de nouveau libre" 201 "$(post $B reserver.php "{\"creneau_id\":$CR,\"motif\":\"Après annulation\"}")"

echo "Déconnexion"
check "logout" 200 "$(post $A logout.php '{}')"
check "mes_rdv après logout" 401 "$(curl -s -o /dev/null -w '%{http_code}' -b $A "$BASE/mes_rdv.php")"

echo; echo "Résultat : $PASS réussis, $FAIL échecs"
[ "$FAIL" -eq 0 ]
