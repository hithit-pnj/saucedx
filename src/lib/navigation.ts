import { getCollection, getEntry } from 'astro:content';

export interface Lien {
  href: string;
  libelle: string;
}

/** Les offres publiées, dans l'ordre voulu par Alice. */
export async function offresPubliees() {
  const offres = await getCollection('offres', ({ data }) => data.publie);
  return offres.sort((a, b) => a.data.ordre - b.data.ordre);
}

/** Le menu principal : accueil, les offres, qui suis-je, contact. Rien d'autre.
 *  Les libellés fixes viennent des réglages, pour qu'Alice les tienne elle-même. */
export async function menuPrincipal(): Promise<Lien[]> {
  const offres = await offresPubliees();
  const { libelles } = (await getEntry('reglages', 'site'))!.data;
  return [
    { href: '/', libelle: libelles.accueil },
    ...offres.map((o) => ({ href: `/${o.id}/`, libelle: o.data.menu })),
    { href: '/qui-suis-je/', libelle: libelles.quiSuisJe },
    { href: '/contact/', libelle: libelles.contact },
  ];
}

/** Compare deux chemins en ignorant la barre oblique finale. */
export function memeChemin(a: string, b: string): boolean {
  const net = (s: string) => (s.length > 1 ? s.replace(/\/+$/, '') : s);
  return net(a) === net(b);
}
