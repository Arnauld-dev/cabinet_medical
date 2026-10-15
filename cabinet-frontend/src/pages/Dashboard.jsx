// Selon le rôle, on affiche le bon tableau de bord.
import { useAuth } from "../context/AuthContext";
import DashboardAdmin from "./DashboardAdmin";
import DashboardMedecin from "./DashboardMedecin";
import DashboardSecretaire from "./DashboardSecretaire";

export default function Dashboard() {
  const { user } = useAuth();

  if (user.role === "administrateur") return <DashboardAdmin />;
  if (user.role === "medecin") return <DashboardMedecin />;
  if (user.role === "secretaire") return <DashboardSecretaire />;
  return <p>Rôle inconnu.</p>;
}
