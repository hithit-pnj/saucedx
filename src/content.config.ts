import { defineCollection, z } from 'astro:content';
import { glob, file } from 'astro/loaders';

const climats = ['garance', 'ambre', 'prune', 'vert', 'bronze', 'nuit', 'ardoise'] as const;
const glyphes = ['rayonnement', 'visavis', 'meandre', 'fleche', 'collectif', 'cernes'] as const;

const bloc = z.object({
  titre: z.string(),
  texte: z.string(),
});

const seo = z.object({
  titre: z.string(),
  description: z.string(),
});

/** Une offre = une page. Certaines offres réunissent deux formats (La Cordée). */
const offres = defineCollection({
  loader: glob({ base: 'src/content/offres', pattern: '**/*.md' }),
  schema: z.object({
    ordre: z.number(),
    publie: z.boolean().default(true),

    climat: z.enum(climats),
    glyphe: z.enum(glyphes),

    /** Libellé court utilisé dans le menu et les portes de l'accueil. */
    menu: z.string(),
    porte: z.object({
      intitule: z.string(),
      precision: z.string(),
    }),

    hero: z.object({
      lignes: z.array(z.string()).min(1).max(3),
      surtitre: z.string(),
      verbes: z.array(z.string()).default([]),
      citation: z.string().optional(),
      mention: z.string().optional(),
    }),

    /** Une section par format. Le titre n'est affiché que s'il y en a plusieurs. */
    sections: z
      .array(
        z.object({
          titre: z.string().optional(),
          sousTitre: z.string().optional(),
          glyphe: z.enum(glyphes).optional(),
          verbes: z.array(z.string()).default([]),
          texte: z.string(),
          blocs: z.array(bloc).default([]),
        }),
      )
      .min(1),

    cta: z.object({
      titre: z.string(),
      texte: z.string(),
      label: z.string(),
      sujet: z.string().optional(),
    }),

    seo,
  }),
});

/** Les pages qui ne sont pas des offres : accueil, qui suis-je, contact. */
const pages = defineCollection({
  loader: glob({ base: 'src/content/pages', pattern: '**/*.md' }),
  schema: z.object({
    seo,
    climat: z.enum(climats).default('garance'),
    donnees: z.record(z.any()).default({}),
  }),
});

/** Pages légales : Alice les complétera à son immatriculation. */
const legal = defineCollection({
  loader: glob({ base: 'src/content/legal', pattern: '**/*.md' }),
  schema: z.object({
    titre: z.string(),
    seo,
    misAJour: z.string().optional(),
  }),
});

/**
 * Prise de rendez-vous téléphonique.
 *
 * Alice décrit ses habitudes — « mardi de 14 h à 16 h » — et non des dates une à
 * une : le découpage en créneaux est calculé au moment où quelqu'un consulte la
 * page, par le serveur. Elle n'a donc rien à entretenir semaine après semaine.
 */
const rendezVous = defineCollection({
  loader: file('src/content/rendez-vous.json', {
    parser: (texte) => ({ reglages: JSON.parse(texte) }),
  }),
  schema: z.object({
    actif: z.boolean().default(false),

    /** Minutes. Le créneau annoncé au visiteur. */
    duree: z.number().int().min(5).max(120).default(15),
    /** Minutes de battement après chaque appel : deux appels ne se collent pas. */
    battement: z.number().int().min(0).max(120).default(15),
    /** Heures de prévenance : personne ne peut réserver pour dans dix minutes. */
    delaiMinimum: z.number().int().min(0).max(720).default(24),
    /** Jours ouverts à la réservation, à partir d'aujourd'hui. */
    horizon: z.number().int().min(1).max(120).default(21),
    /** Heures au bout desquelles une demande sans réponse libère son créneau. */
    expiration: z.number().int().min(1).max(720).default(72),

    plages: z
      .array(
        z.object({
          jour: z.enum(['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche']),
          debut: z.string().regex(/^\d{2}:\d{2}$/),
          fin: z.string().regex(/^\d{2}:\d{2}$/),
        }),
      )
      .default([]),

    /** Vacances et empêchements : bornes incluses. */
    fermetures: z
      .array(
        z.object({
          du: z.string().regex(/^\d{4}-\d{2}-\d{2}$/),
          au: z.string().regex(/^\d{4}-\d{2}-\d{2}$/),
          motif: z.string().optional(),
        }),
      )
      .default([]),

    textes: z.object({
      surtitre: z.string(),
      titre: z.string(),
      intro: z.string(),
      attente: z.string(),
      vide: z.string(),
      choisi: z.string(),
      changer: z.string(),
      bouton: z.string(),
      boutonEnCours: z.string(),
      consentement: z.string(),
      succes: z.string(),
      erreur: z.string(),
      fuseau: z.string(),
    }),

    champs: z.object({
      nom: z.string(),
      telephone: z.string(),
      email: z.string(),
      motif: z.string(),
    }),
  }),
});

/** Réglages globaux, un seul enregistrement. */
const reglages = defineCollection({
  loader: file('src/content/reglages.json', {
    parser: (texte) => ({ site: JSON.parse(texte) }),
  }),
  schema: z.object({
    nom: z.string(),
    signature: z.string(),
    personne: z.string(),
    email: z.string().email(),
    instagram: z.string(),
    instagramUrl: z.string().url(),
    ville: z.string(),
    region: z.string(),
    baseline: z.string(),
  }),
});

export const collections = { offres, pages, legal, reglages, rendezVous };
