import { useEffect, useState } from "react";
import { CalendarDays, ClipboardCheck, Users, ClipboardList } from "lucide-react";
import { useAuth } from "../context/AuthContext";
import { getListe } from "../api";
import StatCard from "../components/StatCard";
import {
  aujourdhui, partieDate, partieHeure, formaterDate, nomComplet,
} from "../utils";

export default function DashboardMedecin() {
  const { user } = useAuth();
  const [patients, setPatients] = useState([]);
  const [rdv, setRdv] = useState([]);
  const [consultations, setConsultations] = useState([]);

  useEffect(() => {
    getListe("/patients").then(setPatients);
    getListe("/rendez-vous").then(setRdv);
    getListe("/consultations").then(setConsultations);
  }, []);

  // Pour retrouver un nom de patient à partir d'un numéro
  const patientParId = {};
  patients.forEach((p) => (patientParId[p.idPatient] = p));

  const aujourd = aujourdhui();
  const rdvDuJour = rdv.filter((r) => partieDate(r.dateHeure) === aujourd);

  // Les 5 prochains rendez-vous (aujourd'hui ou plus tard)
  const prochains = rdv
    .filter((r) => partieDate(r.dateHeure) >= aujourd)
    .sort((a, b) => String(a.dateHeure).localeCompare(String(b.dateHeure)))
    .slice(0, 5);

  // Les 5 dernières consultations
  const dernieres = [...consultations]
    .sort((a, b) => String(b.dateConsultation).localeCompare(String(a.dateConsultation)))
    .slice(0, 5);

  return (
    <>
      <h1>Tableau de bord - Médecin</h1>
      <p className="sous-titre">Bienvenue, {user.name} - Médecin</p>

      <div className="grille-stats">
        <StatCard titre="Rendez-vous du jour" valeur={rdvDuJour.length} Icone={CalendarDays} />
        <StatCard titre="Consultations terminées" valeur={consultations.length} Icone={ClipboardCheck} couleur="vert" />
        <StatCard titre="Patients" valeur={patients.length} Icone={Users} />
      </div>

      <div className="carte">
        <h3>Prochains rendez-vous</h3>
        {prochains.length === 0 && <p className="gris">Aucun rendez-vous à venir.</p>}
        {prochains.map((r) => (
          <div className="rdv-ligne" key={r.idRendeVous}>
            <div className="rdv-heure">
              <strong>{partieHeure(r.dateHeure)}</strong>
              <small>{formaterDate(r.dateHeure)}</small>
            </div>
            <div className="rdv-info">
              <strong>Patient {nomComplet(patientParId[r.idPatient])}</strong>
              <small>Rendez-vous n°{r.idRendeVous}</small>
            </div>
          </div>
        ))}
      </div>

      <div className="carte">
        <h3>Dernières consultations</h3>
        {dernieres.length === 0 && <p className="gris">Aucune consultation.</p>}
        {dernieres.map((c) => {
          // Le backend envoie le patient avec la consultation
          const rendezVous = c.rendez_vous || c.rendezVous;
          const patient = rendezVous ? rendezVous.patient : null;
          return (
            <div className="rdv-ligne" key={c.idConsultation}>
              <span className="avatar"><ClipboardList size={15} /></span>
              <div className="rdv-info">
                <strong>Consultation n°{c.idConsultation}</strong>
                <small>{patient ? nomComplet(patient) : "Patient inconnu"}</small>
              </div>
              <small className="droite gris">{formaterDate(c.dateConsultation)}</small>
            </div>
          );
        })}
      </div>
    </>
  );
}
