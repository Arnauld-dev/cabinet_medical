// Affiche la photo de profil, ou les initiales s'il n'y a pas de photo.
import { initiales, urlPhoto } from "../utils";

export default function Avatar({ nom, photo, grand }) {
  const url = urlPhoto(photo);
  return (
    <span className={grand ? "avatar grand" : "avatar"}>
      {url ? <img src={url} alt={nom} /> : initiales(nom)}
    </span>
  );
}
