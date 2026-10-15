// UNE seule page réutilisable pour : patients, médecins, factures, etc.
// Elle affiche un tableau + un formulaire d'ajout/modification + la suppression.
//
// Props :
//   titre    : titre de la page
//   endpoint : l'URL de l'API (ex : "/patients")
//   cle      : nom de la clé primaire (ex : "idPatient")
//   champs   : les champs du formulaire (voir champs.js)
//   colonnesExtra (facultatif) : colonnes en plus [{ titre, afficher: (ligne) => ... }]
//   boutonsExtra  (facultatif) : fonction (ligne) => boutons en plus dans "Actions"
import { useEffect, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import api, { getListe } from "../api";
import {
  partieDate, partieHeure, formaterDate, formaterDateHeure,
  formaterMontant, messageErreur,
} from "../utils";

export default function ListePage({ titre, endpoint, cle, champs, colonnesExtra = [], boutonsExtra }) {
  const location = useLocation();
  const navigate = useNavigate();

  const [liste, setListe] = useState([]);
  const [options, setOptions] = useState({}); // contenu des listes déroulantes
  const [formulaire, setFormulaire] = useState({});
  const [enEdition, setEnEdition] = useState(null); // clé de la ligne modifiée
  const [afficherForm, setAfficherForm] = useState(false);
  const [erreur, setErreur] = useState("");
  const [erreurPage, setErreurPage] = useState("");

  // Au chargement de la page : on charge la liste et les listes déroulantes
  useEffect(() => {
    charger().then(traiterDemande);
    chargerOptions();
  }, []);

  async function charger() {
    setErreurPage("");
    try {
      const reponse = await api.get(endpoint);
      const donnees = Array.isArray(reponse.data) ? reponse.data : reponse.data.data || [];
      setListe(donnees);
      return donnees;
    } catch (err) {
      if (err.response?.status === 403) {
        setErreurPage("Accès refusé : votre rôle n'a pas accès à cette page.");
      } else {
        setErreurPage("Impossible de charger les données. Le serveur est-il lancé ?");
      }
      setListe([]);
      return [];
    }
  }

  // Remplit les <select> (patients, médecins, etc.)
  async function chargerOptions() {
    const resultat = {};
    for (const champ of champs) {
      if (champ.type !== "select") continue;
      if (champ.choix) {
        resultat[champ.nom] = champ.choix.map((c) => ({ valeur: c, etiquette: c }));
      } else {
        const donnees = await getListe(champ.source);
        resultat[champ.nom] = donnees.map((d) => ({
          valeur: d[champ.valeur],
          etiquette: champ.etiquette(d),
        }));
      }
    }
    setOptions(resultat);
  }

  // Le dashboard peut demander d'ouvrir le formulaire (ex : bouton "Modifier")
  function traiterDemande(donnees) {
    const demande = location.state;
    if (!demande) return;
    if (demande.ajouter) {
      ouvrirAjout(donnees);
    }
    if (demande.modifierId) {
      const ligne = donnees.find((l) => String(l[cle]) === String(demande.modifierId));
      if (ligne) modifier(ligne);
    }
    navigate(location.pathname, { replace: true, state: null });
  }

  function fermerFormulaire() {
    setFormulaire({});
    setEnEdition(null);
    setAfficherForm(false);
    setErreur("");
  }

  function ouvrirAjout(donnees) {
    const nouveau = {};

    // Valeurs proposées par défaut (date du jour...)
    champs.forEach((c) => {
      if (c.defaut !== undefined) nouveau[c.nom] = c.defaut;
    });

    // Clé numérique (ex : idPatient) : on propose le numéro suivant
    const champCle = champs.find((c) => c.cle && c.type === "number");
    if (champCle) {
      const numeros = donnees.map((l) => Number(l[champCle.nom]) || 0);
      nouveau[champCle.nom] = Math.max(0, ...numeros) + 1;
    }

    setFormulaire(nouveau);
    setEnEdition(null);
    setErreur("");
    setAfficherForm(true);
  }

  // Adapte une valeur de la base pour un <input> HTML
  function versChamp(valeur, type) {
    if (valeur === null || valeur === undefined) return "";
    if (type === "date") return partieDate(valeur);
    if (type === "datetime-local") return partieDate(valeur) + "T" + partieHeure(valeur);
    return valeur;
  }

  // Un champ est verrouillé quand on modifie une ligne existante si c'est
  // la clé primaire ou un champ "creationSeulement"
  function verrouille(champ) {
    return (champ.cle || champ.creationSeulement) && enEdition !== null;
  }

  function modifier(ligne) {
    const copie = {};
    champs.forEach((c) => {
      copie[c.nom] = versChamp(ligne[c.nom], c.type);
    });
    setFormulaire(copie);
    setEnEdition(ligne[cle]);
    setErreur("");
    setAfficherForm(true);
  }

  function changerChamp(nom, valeur) {
    setFormulaire({ ...formulaire, [nom]: valeur });
  }

  async function enregistrer(e) {
    e.preventDefault();
    setErreur("");

    // On prépare les données à envoyer à Laravel
    const donnees = {};
    champs.forEach((c) => {
      if (c.tableauSeulement) return; // affiché seulement dans le tableau
      if (verrouille(c)) return; // non modifiable : on ne l'envoie pas

      let valeur = formulaire[c.nom];
      if (valeur === "" || valeur === undefined) valeur = null;
      if (c.type === "datetime-local" && valeur) {
        valeur = valeur.replace("T", " ") + ":00"; // "2026-10-10 09:00:00"
      }
      donnees[c.nom] = valeur;
    });

    try {
      if (enEdition !== null) {
        await api.put(endpoint + "/" + encodeURIComponent(enEdition), donnees);
      } else {
        await api.post(endpoint, donnees);
      }
      fermerFormulaire();
      charger();
      chargerOptions(); // les listes déroulantes peuvent avoir changé
    } catch (err) {
      setErreur(messageErreur(err, "Erreur lors de l'enregistrement."));
    }
  }

  async function supprimer(id) {
    if (!window.confirm("Voulez-vous vraiment supprimer ?")) return;
    try {
      await api.delete(endpoint + "/" + encodeURIComponent(id));
      charger();
    } catch (err) {
      alert(messageErreur(err, "Suppression impossible."));
    }
  }

  // Ce qui s'affiche dans une cellule du tableau
  function afficher(champ, valeur) {
    if (valeur === null || valeur === undefined || valeur === "") return "-";

    if (champ.format === "statut") {
      const classes = {
        "payée": "badge vert",
        "partiellement payée": "badge bleu",
        "en attente": "badge orange",
      };
      return <span className={classes[valeur] || "badge orange"}>{valeur}</span>;
    }
    if (champ.type === "select") {
      const choix = (options[champ.nom] || []).find((o) => String(o.valeur) === String(valeur));
      return choix ? choix.etiquette : valeur;
    }
    if (champ.type === "date") return formaterDate(valeur);
    if (champ.type === "datetime-local") return formaterDateHeure(valeur);
    if (champ.format === "montant") return formaterMontant(valeur);
    return String(valeur);
  }

  return (
    <>
      <div className="ligne-titre">
        <h1>{titre}</h1>
        <button className="btn" onClick={() => ouvrirAjout(liste)}>
          Ajouter
        </button>
      </div>

      {erreurPage && <div className="erreur">{erreurPage}</div>}

      {afficherForm && (
        <form className="carte formulaire" onSubmit={enregistrer}>
          <h3>{enEdition !== null ? "Modifier" : "Ajouter"}</h3>
          {erreur && <div className="erreur">{erreur}</div>}

          {champs
            .filter((c) => !c.tableauSeulement)
            .map((champ) => (
              <div key={champ.nom}>
                <label>
                  {champ.label}
                  {champ.facultatif && " (facultatif)"}
                </label>

                {champ.type === "select" ? (
                  <select
                    value={formulaire[champ.nom] ?? ""}
                    onChange={(e) => changerChamp(champ.nom, e.target.value)}
                    required={!champ.facultatif}
                    disabled={verrouille(champ)}
                  >
                    <option value="">-- Choisir --</option>
                    {(options[champ.nom] || []).map((o) => (
                      <option key={o.valeur} value={o.valeur}>
                        {o.etiquette}
                      </option>
                    ))}
                  </select>
                ) : champ.type === "textarea" ? (
                  <textarea
                    rows="3"
                    value={formulaire[champ.nom] ?? ""}
                    onChange={(e) => changerChamp(champ.nom, e.target.value)}
                    required={!champ.facultatif}
                    disabled={verrouille(champ)}
                  />
                ) : (
                  <input
                    type={champ.type || "text"}
                    step={champ.type === "number" ? "any" : undefined}
                    placeholder={champ.exemple}
                    value={formulaire[champ.nom] ?? ""}
                    onChange={(e) => changerChamp(champ.nom, e.target.value)}
                    required={!champ.facultatif}
                    disabled={verrouille(champ)}
                  />
                )}
              </div>
            ))}

          <div className="boutons">
            <button className="btn" type="submit">Enregistrer</button>
            <button className="btn btn-clair" type="button" onClick={fermerFormulaire}>
              Annuler
            </button>
          </div>
        </form>
      )}

      <div className="carte">
        {liste.length === 0 ? (
          <p className="gris">Aucun élément pour le moment.</p>
        ) : (
          <table>
            <thead>
              <tr>
                {champs.map((c) => (
                  <th key={c.nom}>{c.label}</th>
                ))}
                {colonnesExtra.map((c) => (
                  <th key={c.titre}>{c.titre}</th>
                ))}
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {liste.map((ligne) => (
                <tr key={ligne[cle]}>
                  {champs.map((c) => (
                    <td key={c.nom} className={c.type === "textarea" ? "cellule-longue" : ""}>
                      {afficher(c, ligne[c.nom])}
                    </td>
                  ))}
                  {colonnesExtra.map((c) => (
                    <td key={c.titre}>{c.afficher(ligne)}</td>
                  ))}
                  <td className="actions">
                    {boutonsExtra && boutonsExtra(ligne)}
                    <button className="btn btn-petit btn-clair" onClick={() => modifier(ligne)}>
                      Modifier
                    </button>
                    <button className="btn btn-petit btn-rouge" onClick={() => supprimer(ligne[cle])}>
                      Supprimer
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </>
  );
}
