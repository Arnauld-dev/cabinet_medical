// Le "contexte" garde l'utilisateur connecté et le partage avec toutes les pages.
import { createContext, useContext, useState } from "react";
import api from "../api";

const AuthContext = createContext();

export function AuthProvider({ children }) {
  // On relit l'utilisateur sauvegardé (pour rester connecté après un refresh)
  const [user, setUser] = useState(() => {
    const sauvegarde = localStorage.getItem("user");
    return sauvegarde ? JSON.parse(sauvegarde) : null;
  });

  async function login(email, password) {
    const reponse = await api.post("/login", { email, password });
    localStorage.setItem("token", reponse.data.token);
    localStorage.setItem("user", JSON.stringify(reponse.data.user));
    setUser(reponse.data.user);
  }

  async function logout() {
    try {
      await api.post("/logout");
    } catch (e) {
      // on ignore l'erreur : on se déconnecte quand même côté frontend
    }
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    setUser(null);
  }

  return (
    <AuthContext.Provider value={{ user, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

// Raccourci pour utiliser le contexte dans les pages
export function useAuth() {
  return useContext(AuthContext);
}
