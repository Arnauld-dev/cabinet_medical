import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Users, CalendarDays, Receipt, ChevronLeft, ChevronRight } from "lucide-react";
import { useAuth } from "../context/AuthContext";
import { getListe } from "../api";
import StatCard from "../components/StatCard";
import {
  aujourdhui, dateEnTexte, partieDate, partieHeure, formaterDate,
  nomComplet, initiales,
} from "../utils";

const NOMS_JOURS = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];

// Retourne les 7 jours (du lundi au dimanche) de la semaine d'une date
function joursDeLaSemaine(date) {
  const decalage = (date.getDay() + 6) % 7; // lundi = 0
  const jours = [];
  for (let i = 0; i < 7; i++) {
    jours.push(new Date(date.getFullYear(), date.getMonth(), date.getDate() - decalage + i));
  }
  return jours;
}

export default function DashboardSecretaire() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [patients, setPatients] = useState([]);
  const [medecins, setMedecins] = useState([]);
  const [rdv, setRdv] = useState([]);
  const [factures, setFactures] = useState([]);
  const [jourChoisi, setJourChoisi] = useState(new Date());

  useEffect(() => {
    getListe("/patients").then(setPatients);
    getListe("/medecins").then(setMedecins);
    getListe("/rendez-vous").then(setRdv);
    getListe("/factures").then(setFactures);
  }, []);

  const patientParId = {};
  patients.forEach((p) => (patientParId[p.idPatient] = p));
  const medecinParId = {};
  medecins.forEach((m) => (medecinParId[m.idMedecin] = m));

  // ---- Chiffres des cartes ----
  const rdvDuJour = rdv.filter((r) => partieDate(r.dateHeure) === aujourdhui());
  const impayees = factures.filter((f) => f.statut !== "payée");

  // ---- Calendrier ----
  const semaine = joursDeLaSemaine(jourChoisi);
  const texteChoisi = dateEnTexte(jourChoisi);
  const rdvDuJourChoisi = rdv
    .filter((r) => partieDate(r.dateHeure) === texteChoisi)
    .sort((a, b) => partieHeure(a.dateHeure).localeCompare(partieHeure(b.dateHeure)));

  function changerSemaine(nombre) {
    setJourChoisi(
      new Date(jourChoisi.getFullYear(), jourChoisi.getMonth(), jourChoisi.getDate() + nombre * 7)
    );
  }

  // ---- Derniers patients inscrits ----
  const derniersPatients = [...patients]
    .sort((a, b) => String(b.created_at).localeCompare(String(a.created_at)))
    .slice(0, 5);

  return (
    <>
      <h1>Tableau de bord - Secrétaire</h1>
      <p className="sous-titre">Bienvenue, {user.name} - Secrétaire</p>

      <div className="grille-stats">
        <StatCard titre="Total patients" valeur={patients.length} Icone={Users} />
        <StatCard titre="Rendez-vous du jour" valeur={rdvDuJour.length} Icone={CalendarDays} couleur="vert" />
        <StatCard titre="Factures impayées" valeur={impayees.length} Icone={Receipt} couleur="rouge" />
      </div>

      <div className="carte">
        <div className="ligne-titre">
          <h3>Gestion des rendez-vous</h3>
          <button className="btn" onClick={() => navigate("/rendez-vous", { state: { ajouter: true } })}>
            Créer un RDV
          </button>
        </div>

        <div className="calendrier-nav">
          <button className="btn btn-petit btn-clair" onClick={() => changerSemaine(-1)}>
            <ChevronLeft size={14} />
          </button>
          <strong>{jourChoisi.toLocaleDateString("fr-FR", { month: "long", year: "numeric" })}</strong>
          <button className="btn btn-petit btn-clair" onClick={() => changerSemaine(1)}>
            <ChevronRight size={14} />
          </button>
        </div>

        <div className="semaine">
          {semaine.map((jour, index) => (
            <button
              key={index}
              className={dateEnTexte(jour) === texteChoisi ? "jour choisi" : "jour"}
              onClick={() => setJourChoisi(jour)}
            >
              <small>{NOMS_JOURS[index]}</small>
              <strong>{jour.getDate()}</strong>
            </button>
          ))}
        </div>

        {rdvDuJourChoisi.length === 0 && (
          <p className="gris">Aucun rendez-vous ce jour-là.</p>
        )}
        {rdvDuJourChoisi.map((r) => (
          <div className="rdv-ligne" key={r.idRendeVous}>
            <div className="rdv-heure">
              <strong>{partieHeure(r.dateHeure)}</strong>
            </div>
            <div className="rdv-info">
              <strong>{nomComplet(patientParId[r.idPatient])}</strong>
              <small>Dr {nomComplet(medecinParId[r.idMedecin])}</small>
            </div>
            <button
              className="btn btn-petit btn-clair droite"
              onClick={() => navigate("/rendez-vous", { state: { modifierId: r.idRendeVous } })}
            >
              Modifier
            </button>
          </div>
        ))}
      </div>

      <div className="carte">
        <h3>Derniers patients inscrits</h3>
        {derniersPatients.length === 0 && <p className="gris">Aucun patient.</p>}
        {derniersPatients.map((p) => (
          <div className="rdv-ligne" key={p.idPatient}>
            <span className="avatar">{initiales(nomComplet(p))}</span>
            <div className="rdv-info">
              <strong>{nomComplet(p)}</strong>
              <small>Inscrit le {formaterDate(p.created_at)}</small>
            </div>
          </div>
        ))}
      </div>
    </>
  );
}
