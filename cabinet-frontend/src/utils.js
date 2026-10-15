// Petites fonctions utiles (dates, montants...) utilisées dans plusieurs pages.

export const MONNAIE = "FBu";

// Date JavaScript -> "2026-10-14"
export function dateEnTexte(date) {
  const mois = String(date.getMonth() + 1).padStart(2, "0");
  const jour = String(date.getDate()).padStart(2, "0");
  return date.getFullYear() + "-" + mois + "-" + jour;
}

// La date d'aujourd'hui en texte : "2026-10-14"
export function aujourdhui() {
  return dateEnTexte(new Date());
}

// "2026-10-10 09:00:00" -> "2026-10-10"
export function partieDate(valeur) {
  return valeur ? String(valeur).slice(0, 10) : "";
}

// "2026-10-10 09:00:00" -> "09:00"
export function partieHeure(valeur) {
  return valeur ? String(valeur).replace(" ", "T").slice(11, 16) : "";
}

// "2026-10-10" -> "10/10/2026"
export function formaterDate(valeur) {
  if (!valeur) return "-";
  const morceaux = partieDate(valeur).split("-");
  return morceaux[2] + "/" + morceaux[1] + "/" + morceaux[0];
}

export function formaterDateHeure(valeur) {
  if (!valeur) return "-";
  return formaterDate(valeur) + " " + partieHeure(valeur);
}

// 50000 -> "50 000 FBu"
export function formaterMontant(valeur) {
  return Number(valeur || 0).toLocaleString("fr-FR") + " " + MONNAIE;
}

// { nom: "Ndayizeye", prenom: "Jean" } -> "Ndayizeye Jean"
export function nomComplet(personne) {
  return personne ? personne.nom + " " + personne.prenom : "-";
}

// "Docteur Jean" -> "DJ"
export function initiales(texte) {
  if (!texte) return "?";
  return texte
    .split(" ")
    .filter((mot) => mot)
    .slice(0, 2)
    .map((mot) => mot[0])
    .join("")
    .toUpperCase();
}

// Transforme une erreur Laravel en message lisible
export function messageErreur(err, messageParDefaut) {
  if (err.response?.status === 403) {
    return "Action non autorisée pour votre rôle.";
  }
  const data = err.response?.data;
  // Paiement trop élevé : on indique ce qu'il reste à payer
  if (data && data.reste_a_payer !== undefined) {
    return data.message + " (reste à payer : " + formaterMontant(data.reste_a_payer) + ")";
  }
  // Sortie de stock trop grande : on indique la quantité disponible
  if (data && data.quantite_disponible !== undefined) {
    return data.message + " (disponible : " + data.quantite_disponible + ")";
  }
  const erreurs = err.response?.data?.errors;
  if (erreurs) {
    return Object.values(erreurs)[0][0];
  }
  return err.response?.data?.message || messageParDefaut;
}

// "photos/abc.jpg" -> "http://localhost:8000/storage/photos/abc.jpg"
export function urlPhoto(photo) {
  if (!photo) return null;
  const adresseServeur = import.meta.env.VITE_API_URL.replace(/\/api\/?$/, "");
  return adresseServeur + "/storage/" + photo;
}
