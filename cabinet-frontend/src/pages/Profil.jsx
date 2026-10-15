import { useAuth } from "../context/AuthContext";
import { nomDuRole } from "../menus";
import { initiales } from "../utils";

export default function Profil() {
  const { user } = useAuth();
  return (
    <>
      <h1>Mon profil</h1>
      <div className="carte profil-carte">
        <span className="avatar grand">{initiales(user.name)}</span>
        <div>
          <h3>{user.name}</h3>
          <p className="gris">{user.email}</p>
          <p>Rôle : {nomDuRole[user.role] || user.role}</p>
        </div>
      </div>
    </>
  );
}
