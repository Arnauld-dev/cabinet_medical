// Page des consultations = la page générique (ListePage)
// + les colonnes Patient / Médecin / Diagnostics
// + un bouton "Diagnostics" pour associer des diagnostics à une consultation.
import { useState } from "react";
import ListePage from "./ListePage";
import api, { getListe } from "../api";
import { champsConsultations } from "../champs";
import { nomComplet, messageErreur } from "../utils";

// Le backend envoie le rendez-vous (avec patient et médecin) dans la consultation
function lireRendezVous(consultation) {
  return consultation.rendez_vous || consultation.rendezVous || null;
}

export default function ConsultationsPage() {
  const [version, setVersion] = useState(0); // sert à recharger la liste
  const [consultation, setConsultation] = useState(null); // consultation choisie
  const [tousLesDiagnostics, setTousLesDiagnostics] = useState([]);
  const [codesCoches, setCodesCoches] = useState([]);
  const [erreur, setErreur] = useState("");

  async function ouvrirDiagnostics(ligne) {
    const liste = await getListe("/diagnostics");
    setTousLesDiagnostics(liste);
    // On coche les diagnostics déjà associés à cette consultation
    setCodesCoches((ligne.diagnostics || []).map((d) => d.codeDiagnostic));
    setConsultation(ligne);
    setErreur("");
  }

  function cocher(code) {
    if (codesCoches.includes(code)) {
      setCodesCoches(codesCoches.filter((c) => c !== code));
    } else {
      setCodesCoches([...codesCoches, code]);
    }
  }

  async function enregistrer() {
    if (codesCoches.length === 0) {
      setErreur("Choisissez au moins un diagnostic.");
      return;
    }
    try {
      // Format attendu par Laravel : { diagnostics: ["D001", "D002"] }
      await api.post("/consultations/" + consultation.idConsultation + "/diagnostics", {
        diagnostics: codesCoches,
      });
      setConsultation(null);
      setVersion(version + 1); // change la "key" : la liste se recharge
    } catch (err) {
      setErreur(messageErreur(err, "Enregistrement impossible."));
    }
  }

  const colonnesExtra = [
    {
      titre: "Patient",
      afficher: (c) => nomComplet(lireRendezVous(c)?.patient),
    },
    {
      titre: "Médecin",
      afficher: (c) => {
        const medecin = lireRendezVous(c)?.medecin;
        return medecin ? "Dr " + nomComplet(medecin) : "-";
      },
    },
    {
      titre: "Diagnostics",
      afficher: (c) =>
        c.diagnostics && c.diagnostics.length > 0
          ? c.diagnostics.map((d) => (
              <span className="badge bleu" key={d.codeDiagnostic}>{d.nomDiagnostic}</span>
            ))
          : "-",
    },
  ];

  return (
    <>
      {consultation && (
        <div className="carte formulaire">
          <h3>Diagnostics - consultation n°{consultation.idConsultation}</h3>
          {erreur && <div className="erreur">{erreur}</div>}

          {tousLesDiagnostics.length === 0 && (
            <p className="gris">Aucun diagnostic disponible. Créez-en dans la page Diagnostics.</p>
          )}

          {tousLesDiagnostics.map((d) => (
            <label className="case" key={d.codeDiagnostic}>
              <input
                type="checkbox"
                checked={codesCoches.includes(d.codeDiagnostic)}
                onChange={() => cocher(d.codeDiagnostic)}
              />
              {d.codeDiagnostic} - {d.nomDiagnostic}
            </label>
          ))}

          <div className="boutons">
            <button className="btn" onClick={enregistrer}>Enregistrer</button>
            <button className="btn btn-clair" onClick={() => setConsultation(null)}>
              Annuler
            </button>
          </div>
        </div>
      )}

      <ListePage
        key={version}
        titre="Consultations"
        endpoint="/consultations"
        cle="idConsultation"
        champs={champsConsultations}
        colonnesExtra={colonnesExtra}
        boutonsExtra={(ligne) => (
          <button className="btn btn-petit btn-clair" onClick={() => ouvrirDiagnostics(ligne)}>
            Diagnostics
          </button>
        )}
      />
    </>
  );
}
