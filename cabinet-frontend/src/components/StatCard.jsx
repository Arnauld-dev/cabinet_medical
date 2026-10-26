// Une carte avec une icône, un titre et un chiffre.
// couleur : "bleu", "vert" ou "rouge"
export default function StatCard({ titre, valeur, Icone, couleur = "bleu" }) {
  return (
    <div className="carte stat" >
      <span className={"stat-icone " + couleur}>
        <Icone size={18} />
      </span>
      <span className="stat-titre">{titre}</span>
      <span className="stat-valeur">{valeur}</span>
    </div>
  );
}
