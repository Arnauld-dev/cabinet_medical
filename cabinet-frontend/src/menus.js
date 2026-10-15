// Le menu de chaque rôle (comme dans la maquette).
// Pour ajouter une page : ajoutez une ligne ici + une ligne dans App.jsx.
import {
  LayoutDashboard, Users, Stethoscope, CalendarDays, ClipboardList,
  Activity, Pill, Receipt, CreditCard, FlaskConical, Package,
  ArrowLeftRight, UserCog, Settings,
} from "lucide-react";

const dashboard = { nom: "Dashboard", chemin: "/", icone: LayoutDashboard };
const patients = { nom: "Patients", chemin: "/patients", icone: Users };
const medecins = { nom: "Médecins", chemin: "/medecins", icone: Stethoscope };
const rendezVous = { nom: "Rendez-vous", chemin: "/rendez-vous", icone: CalendarDays };
const consultations = { nom: "Consultations", chemin: "/consultations", icone: ClipboardList };
const diagnostics = { nom: "Diagnostics", chemin: "/diagnostics", icone: Activity };
const prescriptions = { nom: "Prescriptions", chemin: "/prescriptions", icone: Pill };
const medicaments = { nom: "Médicaments", chemin: "/medicaments", icone: Pill };
const factures = { nom: "Factures", chemin: "/factures", icone: Receipt };
const paiements = { nom: "Paiements", chemin: "/paiements", icone: CreditCard };
const analyses = { nom: "Analyses", chemin: "/analyses-laboratoire", icone: FlaskConical };
const stock = { nom: "Stock", chemin: "/stock-medicaments", icone: Package };
const mouvements = { nom: "Mouvements", chemin: "/mouvements-stock", icone: ArrowLeftRight };
const utilisateurs = { nom: "Utilisateurs", chemin: "/utilisateurs", icone: UserCog };
const parametres = { nom: "Paramètres", chemin: "/parametres", icone: Settings };

export const menus = {
  administrateur: [
    dashboard, patients, medecins, rendezVous, consultations, diagnostics,
    prescriptions, medicaments, factures, paiements, analyses, stock,
    mouvements, utilisateurs, parametres,
  ],
  medecin: [
    dashboard, patients, rendezVous, consultations, diagnostics,
    prescriptions, analyses, parametres,
  ],
  secretaire: [
    dashboard, patients, rendezVous, consultations, factures, paiements,
    medicaments, stock, mouvements, parametres,
  ],
};

export const nomDuRole = {
  administrateur: "Administrateur",
  medecin: "Médecin",
  secretaire: "Secrétaire",
};
