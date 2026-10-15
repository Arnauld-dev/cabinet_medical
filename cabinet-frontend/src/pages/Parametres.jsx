// Les paramètres de MON compte : photo, informations et mot de passe.
// Disponible pour tous les rôles.
import { useRef, useState } from "react";
import api from "../api";
import Avatar from "../components/Avatar";
import { useAuth } from "../context/AuthContext";
import { nomDuRole } from "../menus";
import { messageErreur } from "../utils";

export default function Parametres() {
  const { user, mettreAJourUser } = useAuth();
  const champFichier = useRef(null); // le <input type="file"> caché

  // ---- Photo ----
  const [messagePhoto, setMessagePhoto] = useState("");
  const [erreurPhoto, setErreurPhoto] = useState("");

  // ---- Informations ----
  const [infos, setInfos] = useState({ name: user.name, email: user.email });
  const [messageInfos, setMessageInfos] = useState("");
  const [erreurInfos, setErreurInfos] = useState("");

  // ---- Mot de passe ----
  const motDePasseVide = { current_password: "", password: "", password_confirmation: "" };
  const [mdp, setMdp] = useState(motDePasseVide);
  const [messageMdp, setMessageMdp] = useState("");
  const [erreurMdp, setErreurMdp] = useState("");

  async function choisirPhoto(e) {
    const champ = e.target;
    const fichier = champ.files[0];
    if (!fichier) return;

    setMessagePhoto("");
    setErreurPhoto("");

    // Vérifications avant l'envoi
    if (!fichier.type.startsWith("image/")) {
      setErreurPhoto("Choisissez une image (JPG, PNG ou WEBP).");
      return;
    }
    if (fichier.size > 2 * 1024 * 1024) {
      setErreurPhoto("L'image ne doit pas dépasser 2 Mo.");
      return;
    }

    // Pour envoyer un fichier, on utilise FormData
    const donnees = new FormData();
    donnees.append("photo", fichier);

    try {
      const reponse = await api.post("/profil/photo", donnees);
      mettreAJourUser(reponse.data.user);
      setMessagePhoto("Photo mise à jour.");
    } catch (err) {
      setErreurPhoto(messageErreur(err, "Envoi de la photo impossible."));
    }
    champ.value = ""; // permet de rechoisir le même fichier
  }

  async function supprimerPhoto() {
    setMessagePhoto("");
    setErreurPhoto("");
    try {
      const reponse = await api.delete("/profil/photo");
      mettreAJourUser(reponse.data.user);
      setMessagePhoto("Photo supprimée.");
    } catch (err) {
      setErreurPhoto(messageErreur(err, "Suppression impossible."));
    }
  }

  async function enregistrerInfos(e) {
    e.preventDefault();
    setMessageInfos("");
    setErreurInfos("");
    try {
      const reponse = await api.put("/profil", infos);
      mettreAJourUser(reponse.data.user);
      setMessageInfos("Informations enregistrées.");
    } catch (err) {
      setErreurInfos(messageErreur(err, "Enregistrement impossible."));
    }
  }

  async function changerMotDePasse(e) {
    e.preventDefault();
    setMessageMdp("");
    setErreurMdp("");

    if (mdp.password !== mdp.password_confirmation) {
      setErreurMdp("La confirmation ne correspond pas au nouveau mot de passe.");
      return;
    }

    try {
      await api.put("/profil/mot-de-passe", mdp);
      setMessageMdp("Mot de passe modifié avec succès.");
      setMdp(motDePasseVide);
    } catch (err) {
      setErreurMdp(messageErreur(err, "Changement impossible."));
    }
  }

  return (
    <>
      <h1>Paramètres</h1>
      <p className="sous-titre">Gérez votre photo, vos informations et votre mot de passe.</p>

      {/* ----- Photo de profil ----- */}
      <div className="carte">
        <h3>Photo de profil</h3>
        {messagePhoto && <div className="succes marge-bas">{messagePhoto}</div>}
        {erreurPhoto && <div className="erreur">{erreurPhoto}</div>}

        <div className="photo-zone">
          <Avatar nom={user.name} photo={user.photo} grand />
          <div>
            <div className="boutons">
              <button className="btn" onClick={() => champFichier.current.click()}>
                {user.photo ? "Changer la photo" : "Ajouter une photo"}
              </button>
              {user.photo && (
                <button className="btn btn-clair" onClick={supprimerPhoto}>
                  Supprimer
                </button>
              )}
            </div>
            <p className="gris petit">JPG, PNG ou WEBP - 2 Mo maximum.</p>
          </div>
          <input
            ref={champFichier}
            type="file"
            accept="image/png,image/jpeg,image/webp"
            onChange={choisirPhoto}
            hidden
          />
        </div>
      </div>

      {/* ----- Informations ----- */}
      <form className="carte formulaire" onSubmit={enregistrerInfos}>
        <h3>Informations</h3>
        {messageInfos && <div className="succes">{messageInfos}</div>}
        {erreurInfos && <div className="erreur">{erreurInfos}</div>}

        <label>Nom</label>
        <input value={infos.name} onChange={(e) => setInfos({ ...infos, name: e.target.value })} required />

        <label>Email</label>
        <input type="email" value={infos.email} onChange={(e) => setInfos({ ...infos, email: e.target.value })} required />

        <label>Rôle</label>
        <input value={nomDuRole[user.role] || user.role} disabled />

        <div className="boutons">
          <button className="btn" type="submit">Enregistrer</button>
        </div>
      </form>

      {/* ----- Mot de passe ----- */}
      <form className="carte formulaire" onSubmit={changerMotDePasse}>
        <h3>Changer le mot de passe</h3>
        {messageMdp && <div className="succes">{messageMdp}</div>}
        {erreurMdp && <div className="erreur">{erreurMdp}</div>}

        <label>Mot de passe actuel</label>
        <input
          type="password"
          value={mdp.current_password}
          onChange={(e) => setMdp({ ...mdp, current_password: e.target.value })}
          required
        />

        <label>Nouveau mot de passe (8 caractères minimum)</label>
        <input
          type="password"
          value={mdp.password}
          onChange={(e) => setMdp({ ...mdp, password: e.target.value })}
          required
        />

        <label>Confirmer le nouveau mot de passe</label>
        <input
          type="password"
          value={mdp.password_confirmation}
          onChange={(e) => setMdp({ ...mdp, password_confirmation: e.target.value })}
          required
        />

        <div className="boutons">
          <button className="btn" type="submit">Changer le mot de passe</button>
        </div>
      </form>
    </>
  );
}
