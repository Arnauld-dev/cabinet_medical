import { useEffect, useState } from "react";
import { Users, Stethoscope, CalendarDays, Receipt } from "lucide-react";
import { useAuth } from "../context/AuthContext";
import { getListe } from "../api";
import StatCard from "../components/StatCard";
import {aujourdhui, partieDate, formaterDateHeure, formaterMontant, nomComplet,} from "../utils";

// Prépare les n derniers mois : [{ cle: "2026-10", nom: "oct.", total: 0 }, ...]
function derniersMois(n) {
  const mois = [];
  const maintenant = new Date();
  for (let i = n - 1; i >= 0; i--) {
    const d = new Date(maintenant.getFullYear(), maintenant.getMonth() - i, 1);
    const cle = d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, "0");
    mois.push({ cle, nom: d.toLocaleDateString("fr-FR", { month: "short" }), total: 0 });
  }
  return mois;
}

export default function DashboardAdmin() {
  const { user } = useAuth();
  const [patients, setPatients] = useState([]);
  const [medecins, setMedecins] = useState([]);
  const [rdv, setRdv] = useState([]);
  const [factures, setFactures] = useState([]);
  const [paiements, setPaiements] = useState([]);

  // useEffect s'exécute une fois, quand la page s'affiche
  useEffect(() => {
    getListe("/patients").then(setPatients);
    getListe("/medecins").then(setMedecins);
    getListe("/rendez-vous").then(setRdv);
    getListe("/factures").then(setFactures);
    getListe("/paiements").then(setPaiements);
  }, []);

  // ---- Chiffres des cartes ----
  const rdvDuJour = rdv.filter((r) => partieDate(r.dateHeure) === aujourdhui());
  const impayees = factures.filter((f) => f.statut !== "payée");

  // ---- Activités récentes (on mélange les dernières créations) ----
  const activites = [
    ...patients.map((p) => ({ action: "Nouveau patient créé", detail: nomComplet(p), date: p.created_at })),
    ...medecins.map((m) => ({ action: "Médecin ajouté", detail: "Dr " + nomComplet(m), date: m.created_at })),
    ...rdv.map((r) => ({ action: "Rendez-vous créé", detail: formaterDateHeure(r.dateHeure), date: r.created_at })),
    ...factures.map((f) => ({ action: "Facture créée", detail: f.numeroFacture, date: f.created_at })),
    ...paiements.map((p) => ({ action: "Paiement reçu", detail: formaterMontant(p.montant), date: p.created_at })),
  ]
    .filter((a) => a.date)
    .sort((a, b) => String(b.date).localeCompare(String(a.date)))
    .slice(0, 8);

  // ---- Recettes mensuelles (somme des paiements par mois) ----
  const mois = derniersMois(6);
  paiements.forEach((p) => {
    const cle = partieDate(p.datePaiement).slice(0, 7);
    const ligne = mois.find((m) => m.cle === cle);
    if (ligne) ligne.total += Number(p.montant);
  });
  const maximum = Math.max(1, ...mois.map((m) => m.total));

  return (
    <>
      <h1>Tableau de bord - Administrateur</h1>
      <p className="sous-titre">Bienvenue, {user.name} - Administrateur</p>

      <div className="grille-stats">
        <StatCard titre="Total patients" valeur={patients.length} Icone={Users} />
        <StatCard titre="Total médecins" valeur={medecins.length} Icone={Stethoscope} couleur="vert" />
        <StatCard titre="Rendez-vous du jour" valeur={rdvDuJour.length} Icone={CalendarDays} />
        <StatCard titre="Factures impayées" valeur={impayees.length} Icone={Receipt} couleur="rouge" />
      </div>

      <div className="carte">
        <h3>Activités récentes</h3>
        {activites.length === 0 ? (
          <p className="gris">Aucune activité pour le moment.</p>
        ) : (
          <table>
            <thead>
              <tr>
                <th>Action</th>
                <th>Détail</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              {activites.map((a, index) => (
                <tr key={index}>
                  <td>{a.action}</td>
                  <td>{a.detail}</td>
                  <td>{formaterDateHeure(a.date)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      <div className="carte">
        <h3>Recettes mensuelles</h3>
        <div className="graphique">
          {mois.map((m) => (
            <div className="barre-zone" key={m.cle}>
              <span className="barre-valeur">{m.total > 0 ? m.total.toLocaleString("fr-FR") : ""}</span>
              <div className="barre" style={{ height: (m.total / maximum) * 100 + "%" }} />
              <span className="barre-nom">{m.nom}</span>
            </div>
          ))}
        </div>
        <p className="gris petit">Total des paiements reçus, en {"FBu"}.</p>
      </div>
    </>
  );
}
