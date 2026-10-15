// La structure commune : barre latérale à gauche + en-tête en haut.
import { useState } from "react";
import { NavLink, Outlet, useNavigate } from "react-router-dom";
import { Bell, ChevronDown, LogOut, Settings } from "lucide-react";
import { useAuth } from "../context/AuthContext";
import { menus, nomDuRole } from "../menus";
import Avatar from "./Avatar";

export default function Layout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [menuOuvert, setMenuOuvert] = useState(false);
  const menu = menus[user.role] || [];

  function allerAuProfil() {
    setMenuOuvert(false);
    navigate("/parametres");
  }

  return (
    <div className="app">
      <aside className="sidebar">
        <div className="logo">Cabinet médical</div>
        <nav>
          {menu.map((lien) => {
            const Icone = lien.icone;
            return (
              <NavLink
                key={lien.chemin}
                to={lien.chemin}
                end={lien.chemin === "/"}
                className={({ isActive }) => (isActive ? "lien actif" : "lien")}
              >
                <Icone size={17} />
                {lien.nom}
              </NavLink>
            );
          })}
        </nav>
      </aside>

      <div className="contenu">
        <header className="header">
          <Bell size={18} className="cloche" />

          <div className="profil-zone">
            <button className="profil-bouton" onClick={() => setMenuOuvert(!menuOuvert)}>
              <Avatar nom={user.name} photo={user.photo} />
              <span className="profil-texte">
                <strong>{user.name}</strong>
                <small>{nomDuRole[user.role] || user.role}</small>
              </span>
              <ChevronDown size={16} />
            </button>

            {menuOuvert && (
              <div className="menu-deroulant">
                <button onClick={allerAuProfil}>
                  <Settings size={15} /> Paramètres
                </button>
                <button onClick={logout}>
                  <LogOut size={15} /> Se déconnecter
                </button>
              </div>
            )}
          </div>
        </header>

        <main className="page">
          {/* Outlet = l'endroit où s'affiche la page choisie */}
          <Outlet />
        </main>
      </div>
    </div>
  );
}
