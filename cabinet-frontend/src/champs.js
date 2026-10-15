// ============================================================
//  Les champs de chaque formulaire = les colonnes de VOTRE base.
//  C'est le seul fichier à modifier si votre base change.
//
//  Options d'un champ :
//   nom        : nom exact de la colonne
//   label      : texte affiché
//   type       : "text" (défaut), "number", "date", "datetime-local",
//                "textarea" ou "select"
//   cle        : true si c'est la clé primaire (modifiable seulement à la création)
//   creationSeulement : true si le champ ne peut plus être modifié après la création
//   tableauSeulement  : true si le champ est affiché dans le tableau mais pas dans le formulaire
//   facultatif : true si le champ peut rester vide
//   defaut     : valeur proposée à la création
//   format     : "montant" ou "statut" (affichage dans le tableau)
//   exemple    : texte gris d'aide dans le champ
//  Pour type "select" :
//   source + valeur + etiquette : liste venant de l'API
//   choix                       : liste fixe de textes
// ============================================================
import { aujourdhui, formaterMontant } from "./utils";

// ---- Listes déroulantes réutilisables (clés étrangères) ----
const choixPatient = {
  nom: "idPatient", label: "Patient", type: "select",
  source: "/patients", valeur: "idPatient",
  etiquette: (p) => p.nom + " " + p.prenom,
};

const choixMedecin = {
  nom: "idMedecin", label: "Médecin", type: "select",
  source: "/medecins", valeur: "idMedecin",
  etiquette: (m) => "Dr " + m.nom + " " + m.prenom,
};

const choixRendezVous = {
  nom: "idRendeVous", label: "Rendez-vous", type: "select",
  source: "/rendez-vous", valeur: "idRendeVous",
  etiquette: (r) => {
    // Le backend envoie le patient avec le rendez-vous
    const patient = r.patient ? r.patient.nom + " " + r.patient.prenom + " - " : "";
    return "RDV n°" + r.idRendeVous + " - " + patient + String(r.dateHeure).slice(0, 16);
  },
};

const choixConsultation = {
  nom: "idConsultation", label: "Consultation", type: "select",
  source: "/consultations", valeur: "idConsultation",
  etiquette: (c) => {
    // Le backend envoie le patient dans c.rendez_vous.patient
    const rv = c.rendez_vous || c.rendezVous;
    const patient = rv && rv.patient ? " - " + rv.patient.nom + " " + rv.patient.prenom : "";
    return "Consultation n°" + c.idConsultation + patient + " - " + String(c.dateConsultation).slice(0, 10);
  },
};

const choixMedicament = {
  nom: "codeMedicament", label: "Médicament", type: "select",
  source: "/medicaments", valeur: "codeMedicament",
  etiquette: (m) => m.nomMedicament + " (" + m.codeMedicament + ")",
};

const choixFacture = {
  nom: "idFacture", label: "Facture", type: "select",
  source: "/factures", valeur: "idFacture",
  etiquette: (f) => {
    // Le backend envoie les paiements avec la facture : on calcule le reste à payer
    const paye = (f.paiements || []).reduce((somme, p) => somme + Number(p.montant), 0);
    const reste = Math.max(0, Number(f.montant) - paye);
    return f.numeroFacture + " (reste " + formaterMontant(reste) + ")";
  },
};

const choixStock = {
  nom: "idStock", label: "Stock", type: "select",
  source: "/stock-medicaments", valeur: "idStock",
  etiquette: (s) => {
    const nom = s.medicament ? s.medicament.nomMedicament : s.codeMedicament;
    return "Stock n°" + s.idStock + " - " + nom + " (" + s.quantite + " en stock)";
  },
};

// ---- Les formulaires ----

export const champsPatients = [
  { nom: "idPatient", label: "N° patient", type: "number", cle: true },
  { nom: "nom", label: "Nom" },
  { nom: "prenom", label: "Prénom" },
  { nom: "dateNaissance", label: "Date de naissance", type: "date" },
  { nom: "adresse", label: "Adresse" },
  { nom: "telephone", label: "Téléphone" },
];

export const champsMedecins = [
  { nom: "idMedecin", label: "N° médecin", type: "number", cle: true },
  { nom: "nom", label: "Nom" },
  { nom: "prenom", label: "Prénom" },
  { nom: "specialite", label: "Spécialité" },
];

export const champsRendezVous = [
  choixPatient,
  choixMedecin,
  { nom: "dateHeure", label: "Date et heure", type: "datetime-local" },
];

export const champsConsultations = [
  { nom: "idConsultation", label: "N° consultation", type: "number", cle: true },
  choixRendezVous,
  { nom: "dateConsultation", label: "Date", type: "date", defaut: aujourdhui() },
];

export const champsDiagnostics = [
  { nom: "codeDiagnostic", label: "Code", cle: true, exemple: "D004" },
  { nom: "nomDiagnostic", label: "Nom du diagnostic" },
  { nom: "description", label: "Description", type: "textarea" },
];

export const champsMedicaments = [
  { nom: "codeMedicament", label: "Code", cle: true, exemple: "MED002" },
  { nom: "nomMedicament", label: "Nom du médicament" },
  { nom: "prix", label: "Prix", type: "number", format: "montant" },
];

export const champsPrescriptions = [
  { nom: "idPrescription", label: "N° prescription", type: "number", cle: true },
  choixConsultation,
  choixMedicament,
  { nom: "posologie", label: "Posologie", exemple: "1 comprimé matin et soir" },
  { nom: "dureeTraitement", label: "Durée (jours)", type: "number", facultatif: true },
];

export const champsFactures = [
  { nom: "numeroFacture", label: "N° facture", exemple: "FAC-2026-002" },
  choixPatient,
  choixConsultation,
  { nom: "dateFacturation", label: "Date", type: "date", defaut: aujourdhui() },
  { nom: "montant", label: "Montant", type: "number", format: "montant" },
  // Le statut est calculé automatiquement par les paiements : on l'affiche seulement
  { nom: "statut", label: "Statut", format: "statut", tableauSeulement: true },
];

export const champsPaiements = [
  choixFacture,
  { nom: "datePaiement", label: "Date", type: "date", defaut: aujourdhui() },
  { nom: "montant", label: "Montant", type: "number", format: "montant" },
  {
    nom: "modePaiement", label: "Mode de paiement", type: "select",
    choix: ["Espèces", "Mobile money", "Carte bancaire", "Virement"],
  },
];

export const champsAnalyses = [
  { nom: "idAnalyse", label: "N° analyse", type: "number", cle: true },
  choixPatient,
  choixConsultation,
  { nom: "typeAnalyse", label: "Type d'analyse", exemple: "Analyse de sang" },
  { nom: "datePrescription", label: "Date de prescription", type: "date", defaut: aujourdhui() },
  { nom: "laboratoire", label: "Laboratoire" },
  { nom: "resultat", label: "Résultat", facultatif: true },
  { nom: "dateResultat", label: "Date du résultat", type: "date", facultatif: true },
];

export const champsStock = [
  { nom: "idStock", label: "N° stock", type: "number", cle: true },
  choixMedicament,
  { nom: "quantite", label: "Quantité", type: "number" },
  { nom: "quantiteMin", label: "Quantité minimale", type: "number" },
  { nom: "dateExpiration", label: "Date d'expiration", type: "date" },
  { nom: "fournisseur", label: "Fournisseur" },
  { nom: "emplacement", label: "Emplacement", exemple: "Rayon A1" },
];

// Un mouvement ne peut pas être modifié après sa création (sauf la date),
// car il change la quantité en stock.
export const champsMouvements = [
  { nom: "idMouvement", label: "N°", type: "number", tableauSeulement: true },
  { ...choixStock, creationSeulement: true },
  {
    nom: "typeMouvement", label: "Type", type: "select",
    choix: ["entrée", "sortie"], creationSeulement: true,
  },
  { nom: "quantite", label: "Quantité", type: "number", creationSeulement: true },
  { nom: "dateMouvement", label: "Date", type: "date", defaut: aujourdhui() },
];
