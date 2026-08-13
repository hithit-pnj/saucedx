import { getCollection } from 'astro:content';

export interface Lien {
  href: string;
  libelle: string;
}

/** Les offres publiées, dans l'ordre voulu par Alice. */
export async function offresPubliees() {
  const offres = await getCollection('offres', ({ data }) => data.publie);
  return offres.sort((a, b) => a.data.ordre - b.data.ordre);
}

/** Le menu principal : accueil, les offres, qui suis-je, contact. Rien d'autre. */
export async function menuPrincipal(): Promise<Lien[]> {
  const offres = await offresPubliees();
  return [
    { href: '/', libelle: 'Accueil' },
    ...offres.map((o) => ({ href: `/${o.id}/`, libelle: o.data.menu })),
    { href: '/qui-suis-je/', libelle: 'Qui suis-je' },
    { href: '/contact/', libelle: 'Contact' },
  ];
}

/** Compare deux chemins en ignorant la barre oblique finale. */
export function memeChemin(a: string, b: string): boolean {
  const net = (s: string) => (s.length > 1 ? s.replace(/\/+$/, '') : s);
  return net(a) === net(b);
}
