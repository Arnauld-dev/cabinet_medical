import { useState } from "react";
import { Navigate, useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";

export default function Login() {
  const { user, login } = useAuth();
  const navigate = useNavigate();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [erreur, setErreur] = useState("");

  // Déjà connecté ? On va directement au dashboard
  if (user) return <Navigate to="/" />;

  async function envoyer(e) {
    e.preventDefault(); // empêche le rechargement de la page
    setErreur("");
    try {
      await login(email, password);
      navigate("/");
    } catch (err) {
      const message = err.response?.data?.errors?.email?.[0];
      setErreur(message || "Connexion impossible. Le serveur est-il lancé ?");
    }
  }

  return (
    <div className="login-fond">
      <form className="carte login" onSubmit={envoyer}>
        <h1>Connexion</h1>
        <p className="gris">Accédez à votre espace de travail.</p>

        {erreur && <div className="erreur">{erreur}</div>}

        <label>Email</label>
        <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />

        <label>Mot de passe</label>
        <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required />

        <button className="btn" type="submit">Se connecter</button>
      </form>
    </div>
  );
}
