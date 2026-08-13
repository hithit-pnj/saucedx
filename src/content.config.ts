import { defineCollection, z } from 'astro:content';
import { glob, file } from 'astro/loaders';

const climats = ['garance', 'ambre', 'prune', 'vert', 'nuit', 'ardoise'] as const;
const glyphes = ['rayonnement', 'suspension', 'virage', 'strates', 'cercles', 'bifurcation'] as const;

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

export const collections = { offres, pages, legal, reglages };
