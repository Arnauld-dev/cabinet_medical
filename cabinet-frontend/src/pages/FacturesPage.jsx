// Page des factures = la page générique (ListePage)
// + un bouton "Imprimer" qui utilise la route /factures/{id}/impression de Laravel
//   pour afficher un aperçu de la facture, prêt à imprimer.
import { useState } from "react";
import ListePage from "./ListePage";
import api from "../api";
import { champsFactures } from "../champs";
import { formaterDate, formaterMontant, nomComplet, messageErreur } from "../utils";

export default function FacturesPage() {
  // Réponse de Laravel : { facture, patient, consultation, medecin, paiements, totalPaye, resteAPayer }
  const [impression, setImpression] = useState(null);

  async function ouvrirApercu(ligne) {
    try {
      const reponse = await api.get("/factures/" + ligne.idFacture + "/impression");
      setImpression(reponse.data);
      window.scrollTo(0, 0);
    } catch (err) {
      alert(messageErreur(err, "Impossible de charger la facture."));
    }
  }

  return (
    <>
      {impression && (
        <>
          {/* Cette zone (et seulement elle) est imprimée */}
          <div className="carte facture zone-impression">
            <div className="facture-entete">
              <div>
                <h2>Cabinet médical</h2>
                <p className="gris">Facture de consultation</p>
              </div>
              <div className="texte-droite">
                <h2>{impression.facture.numeroFacture}</h2>
                <p>Date : {formaterDate(impression.facture.dateFacturation)}</p>
              </div>
            </div>

            <h4>Patient</h4>
            <p>{nomComplet(impression.patient)}</p>
            <p className="gris">
              {impression.patient.adresse || "-"} - Tél : {impression.patient.telephone}
            </p>

            {impression.medecin && (
              <>
                <h4>Médecin</h4>
                <p>
                  Dr {nomComplet(impression.medecin)}
                  {impression.medecin.specialite && " - " + impression.medecin.specialite}
                </p>
              </>
            )}

            <table>
              <thead>
                <tr>
                  <th>Désignation</th>
                  <th className="texte-droite">Montant</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    Consultation
                    {impression.consultation && " n°" + impression.consultation.idConsultation}
                    {impression.consultation && " du " + formaterDate(impression.consultation.dateConsultation)}
                  </td>
                  <td className="texte-droite">{formaterMontant(impression.facture.montant)}</td>
                </tr>
              </tbody>
            </table>

            <h4>Paiements reçus</h4>
            {impression.paiements.length === 0 ? (
              <p className="gris">Aucun paiement enregistré.</p>
            ) : (
              <table>
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Mode</th>
                    <th className="texte-droite">Montant</th>
                  </tr>
                </thead>
                <tbody>
                  {impression.paiements.map((p) => (
                    <tr key={p.idPaiement}>
                      <td>{formaterDate(p.datePaiement)}</td>
                      <td>{p.modePaiement}</td>
                      <td className="texte-droite">{formaterMontant(p.montant)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}

            <div className="facture-totaux">
              <p>Total facture : <strong>{formaterMontant(impression.facture.montant)}</strong></p>
              <p>Total payé : <strong>{formaterMontant(impression.totalPaye)}</strong></p>
              <p>Reste à payer : <strong>{formaterMontant(impression.resteAPayer)}</strong></p>
              <p>Statut : <strong>{impression.facture.statut}</strong></p>
            </div>
          </div>

          <div className="boutons marge-bas">
            <button className="btn" onClick={() => window.print()}>Imprimer</button>
            <button className="btn btn-clair" onClick={() => setImpression(null)}>
              Fermer l'aperçu
            </button>
          </div>
        </>
      )}

      <ListePage
        titre="Factures"
        endpoint="/factures"
        cle="idFacture"
        champs={champsFactures}
        boutonsExtra={(ligne) => (
          <button className="btn btn-petit btn-clair" onClick={() => ouvrirApercu(ligne)}>
            Imprimer
          </button>
        )}
      />
    </>
  );
}
