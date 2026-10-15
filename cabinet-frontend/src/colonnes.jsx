// Colonnes supplémentaires affichées dans certains tableaux.
import { aujourdhui, partieDate } from "./utils";

// Stock : alerte si la quantité est basse ou si le médicament est expiré
export const colonnesStock = [
  {
    titre: "Alerte",
    afficher: (s) => {
      const alertes = [];
      if (Number(s.quantite) <= Number(s.quantiteMin)) {
        alertes.push(<span className="badge rouge" key="bas">Stock bas</span>);
      }
      if (partieDate(s.dateExpiration) < aujourdhui()) {
        alertes.push(<span className="badge rouge" key="expire">Expiré</span>);
      }
      return alertes.length > 0 ? alertes : "-";
    },
  },
];

// Mouvements : on affiche le nom du médicament (envoyé par le backend)
export const colonnesMouvements = [
  {
    titre: "Médicament",
    afficher: (m) => (m.stock && m.stock.medicament ? m.stock.medicament.nomMedicament : "-"),
  },
];
