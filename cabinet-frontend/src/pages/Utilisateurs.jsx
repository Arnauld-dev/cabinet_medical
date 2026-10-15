// Réservé à l'administrateur : voir, créer, modifier et supprimer les utilisateurs.
import { useEffect, useState } from "react";
import api from "../api";
import { useAuth } from "../context/AuthContext";
import Avatar from "../components/Avatar";
import { nomDuRole } from "../menus";
import { formaterDate, messageErreur } from "../utils";

const formulaireVide = {
  name: "", email: "", role: "medecin", password: "", password_confirmation: "",
};

export default function Utilisateurs() {
  const { user: moi } = useAuth(); // l'utilisateur connecté
  const [utilisateurs, setUtilisateurs] = useState([]);
  const [formulaire, setFormulaire] = useState(formulaireVide);
  const [enEdition, setEnEdition] = useState(null); // id de l'utilisateur modifié
  const [afficherForm, setAfficherForm] = useState(false);
  const [message, setMessage] = useState("");
  const [erreur, setErreur] = useState("");
  const [erreurPage, setErreurPage] = useState("");

  useEffect(() => {
    charger();
  }, []);

  async function charger() {
    setErreurPage("");
    try {
      const reponse = await api.get("/users");
      setUtilisateurs(Array.isArray(reponse.data) ? reponse.data : reponse.data.data || []);
    } catch (err) {
      if (err.response?.status === 404) {
        setErreurPage("La route /users n'existe pas encore : ajoutez UserController et les routes (dossier backend).");
      } else if (err.response?.status === 403) {
        setErreurPage("Accès refusé : réservé à l'administrateur.");
      } else {
        setErreurPage("Impossible de charger les utilisateurs.");
      }
    }
  }

  function changer(nom, valeur) {
    setFormulaire({ ...formulaire, [nom]: valeur });
  }

  function ouvrirAjout() {
    setFormulaire(formulaireVide);
    setEnEdition(null);
    setErreur("");
    setMessage("");
    setAfficherForm(true);
  }

  function modifier(u) {
    setFormulaire({
      name: u.name, email: u.email, role: u.role, password: "", password_confirmation: "",
    });
    setEnEdition(u.id);
    setErreur("");
    setMessage("");
    setAfficherForm(true);
  }

  function fermer() {
    setAfficherForm(false);
    setEnEdition(null);
    setErreur("");
  }

  async function enregistrer(e) {
    e.preventDefault();
    setErreur("");
    setMessage("");
    try {
      if (enEdition !== null) {
        // Modification : le mot de passe n'est envoyé que s'il est rempli
        const donnees = { name: formulaire.name, email: formulaire.email, role: formulaire.role };
        if (formulaire.password) {
          donnees.password = formulaire.password;
          donnees.password_confirmation = formulaire.password_confirmation;
        }
        await api.put("/users/" + enEdition, donnees);
        setMessage("Utilisateur modifié avec succès.");
      } else {
        // Création : la route dépend du rôle choisi
        const url = formulaire.role === "medecin" ? "/creer-medecin" : "/creer-secretaire";
        await api.post(url, {
          name: formulaire.name,
          email: formulaire.email,
          password: formulaire.password,
          password_confirmation: formulaire.password_confirmation,
        });
        setMessage("Compte créé avec succès.");
      }
      fermer();
      charger();
    } catch (err) {
      setErreur(messageErreur(err, "Enregistrement impossible."));
    }
  }

  async function supprimer(u) {
    if (!window.confirm("Supprimer le compte de " + u.name + " ?")) return;
    setMessage("");
    try {
      await api.delete("/users/" + u.id);
      setMessage("Utilisateur supprimé.");
      charger();
    } catch (err) {
      setErreur(messageErreur(err, "Suppression impossible."));
    }
  }

  return (
    <>
      <div className="ligne-titre">
        <h1>Utilisateurs</h1>
        <button className="btn" onClick={ouvrirAjout}>Ajouter un utilisateur</button>
      </div>

      {message && <div className="succes marge-bas">{message}</div>}
      {erreurPage && <div className="erreur">{erreurPage}</div>}
      {!afficherForm && erreur && <div className="erreur">{erreur}</div>}

      {afficherForm && (
        <form className="carte formulaire" onSubmit={enregistrer}>
          <h3>{enEdition !== null ? "Modifier l'utilisateur" : "Créer un compte"}</h3>
          {erreur && <div className="erreur">{erreur}</div>}

          <label>Rôle</label>
          <select value={formulaire.role} onChange={(e) => changer("role", e.target.value)}>
            <option value="medecin">Médecin</option>
            <option value="secretaire">Secrétaire</option>
            {/* On ne peut créer que médecin ou secrétaire, mais on peut modifier un administrateur */}
            {enEdition !== null && <option value="administrateur">Administrateur</option>}
          </select>

          <label>Nom</label>
          <input value={formulaire.name} onChange={(e) => changer("name", e.target.value)} required />

          <label>Email</label>
          <input type="email" value={formulaire.email} onChange={(e) => changer("email", e.target.value)} required />

          <label>
            {enEdition !== null
              ? "Nouveau mot de passe (laissez vide pour ne pas changer)"
              : "Mot de passe (8 caractères minimum)"}
          </label>
          <input
            type="password"
            value={formulaire.password}
            onChange={(e) => changer("password", e.target.value)}
            required={enEdition === null}
          />

          <label>Confirmer le mot de passe</label>
          <input
            type="password"
            value={formulaire.password_confirmation}
            onChange={(e) => changer("password_confirmation", e.target.value)}
            required={enEdition === null}
          />

          <div className="boutons">
            <button className="btn" type="submit">Enregistrer</button>
            <button className="btn btn-clair" type="button" onClick={fermer}>Annuler</button>
          </div>
        </form>
      )}

      <div className="carte">
        {utilisateurs.length === 0 ? (
          <p className="gris">Aucun utilisateur à afficher.</p>
        ) : (
          <table>
            <thead>
              <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Créé le</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {utilisateurs.map((u) => (
                <tr key={u.id}>
                  <td>
                    <div className="utilisateur-nom">
                      <Avatar nom={u.name} photo={u.photo} />
                      <span>
                        {u.name}
                        {u.id === moi.id && <span className="gris"> (vous)</span>}
                      </span>
                    </div>
                  </td>
                  <td>{u.email}</td>
                  <td><span className="badge bleu">{nomDuRole[u.role] || u.role}</span></td>
                  <td>{formaterDate(u.created_at)}</td>
                  <td className="actions">
                    <button className="btn btn-petit btn-clair" onClick={() => modifier(u)}>
                      Modifier
                    </button>
                    {/* On ne peut pas supprimer son propre compte */}
                    {u.id !== moi.id && (
                      <button className="btn btn-petit btn-rouge" onClick={() => supprimer(u)}>
                        Supprimer
                      </button>
                    )}
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
