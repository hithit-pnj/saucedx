import type { APIRoute } from 'astro';
import { getEntry } from 'astro:content';

/**
 * Les règles de disponibilité, déposées à la racine du site sous forme de fichier.
 *
 * Le site est statique : Alice modifie ses plages dans le CMS, la mise en ligne
 * les recopie ici, et le script PHP les relit à chaque visite. C'est ce fichier
 * qui fait le pont entre le contenu — versionné, relu, réversible — et le calcul
 * des créneaux, qui doit se faire côté serveur pour connaître l'heure de Paris et
 * les moments déjà pris.
 */
export const GET: APIRoute = async () => {
  const r = (await getEntry('rendezVous', 'reglages'))!.data;

  const corps = {
    actif: r.actif,
    duree: r.duree,
    battement: r.battement,
    delaiMinimum: r.delaiMinimum,
    horizon: r.horizon,
    expiration: r.expiration,
    plages: r.plages,
    fermetures: r.fermetures.map(({ du, au }) => ({ du, au })),
  };

  return new Response(JSON.stringify(corps), {
    headers: { 'Content-Type': 'application/json; charset=utf-8' },
  });
};
