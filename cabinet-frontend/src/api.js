// Ce fichier configure axios : l'outil qui parle avec Laravel.
import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: { Accept: "application/json" },
});

// Avant chaque requête, on ajoute le token (s'il existe)
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = "Bearer " + token;
  }
  return config;
});

// Laravel renvoie parfois un tableau, parfois { data: [...] } (pagination).
// Cette fonction gère les deux cas. Si la route est interdite, elle renvoie [].
export async function getListe(url) {
  try {
    const reponse = await api.get(url);
    return Array.isArray(reponse.data) ? reponse.data : reponse.data.data || [];
  } catch (erreur) {
    return [];
  }
}

export default api;
