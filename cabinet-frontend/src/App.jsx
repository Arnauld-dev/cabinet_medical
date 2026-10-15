import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { AuthProvider } from "./context/AuthContext";
import ProtectedRoute from "./components/ProtectedRoute";
import Layout from "./components/Layout";
import Login from "./pages/Login";
import Dashboard from "./pages/Dashboard";
import ListePage from "./pages/ListePage";
import Utilisateurs from "./pages/Utilisateurs";
import Profil from "./pages/Profil";
import ConsultationsPage from "./pages/ConsultationsPage";
import FacturesPage from "./pages/FacturesPage";
import { colonnesStock, colonnesMouvements } from "./colonnes";
import {
  champsPatients, champsMedecins, champsRendezVous,
  champsDiagnostics, champsMedicaments, champsPrescriptions,
  champsPaiements, champsAnalyses, champsStock, champsMouvements,
} from "./champs";

// Une ligne par page de liste.
//   chemin : adresse dans le navigateur ET dans l'API Laravel
//   cle    : clé primaire de la table
const pages = [
  { chemin: "patients", titre: "Patients", cle: "idPatient", champs: champsPatients },
  { chemin: "medecins", titre: "Médecins", cle: "idMedecin", champs: champsMedecins },
  { chemin: "rendez-vous", titre: "Rendez-vous", cle: "idRendeVous", champs: champsRendezVous },
  { chemin: "diagnostics", titre: "Diagnostics", cle: "codeDiagnostic", champs: champsDiagnostics },
  { chemin: "prescriptions", titre: "Prescriptions", cle: "idPrescription", champs: champsPrescriptions },
  { chemin: "medicaments", titre: "Médicaments", cle: "codeMedicament", champs: champsMedicaments },
  { chemin: "paiements", titre: "Paiements", cle: "idPaiement", champs: champsPaiements },
  { chemin: "analyses-laboratoire", titre: "Analyses de laboratoire", cle: "idAnalyse", champs: champsAnalyses },
  { chemin: "stock-medicaments", titre: "Stock des médicaments", cle: "idStock", champs: champsStock, colonnesExtra: colonnesStock },
  { chemin: "mouvements-stock", titre: "Mouvements de stock", cle: "idMouvement", champs: champsMouvements, colonnesExtra: colonnesMouvements },
];

export default function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<Login />} />

          <Route
            path="/"
            element={
              <ProtectedRoute>
                <Layout />
              </ProtectedRoute>
            }
          >
            <Route index element={<Dashboard />} />
            <Route path="profil" element={<Profil />} />
            <Route path="utilisateurs" element={<Utilisateurs />} />
            <Route path="consultations" element={<ConsultationsPage />} />
            <Route path="factures" element={<FacturesPage />} />

            {pages.map((p) => (
              <Route
                key={p.chemin}
                path={p.chemin}
                element={
                  <ListePage
                    key={p.chemin}
                    titre={p.titre}
                    endpoint={"/" + p.chemin}
                    cle={p.cle}
                    champs={p.champs}
                    colonnesExtra={p.colonnesExtra}
                  />
                }
              />
            ))}
          </Route>

          <Route path="*" element={<Navigate to="/" />} />
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  );
}
