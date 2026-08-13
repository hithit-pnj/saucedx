// @ts-check
import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import { loadEnv } from 'vite';

// Le domaine se règle dans le fichier .env (voir .env.example) plutôt qu'ici :
// il sert au sitemap, aux URL canoniques et aux métadonnées de partage, et il
// change entre le développement, la préproduction et la mise en ligne.
//
// loadEnv est indispensable : dans ce fichier, process.env ne voit que les
// variables réelles du shell, jamais le contenu d'un .env.
const { SITE_URL } = loadEnv(process.env.NODE_ENV ?? '', process.cwd(), '');
const site = SITE_URL || 'https://exemple.fr';

export default defineConfig({
  site,
  output: 'static',
  trailingSlash: 'always',
  integrations: [sitemap({ i18n: undefined })],
  build: { format: 'directory', inlineStylesheets: 'auto' },
  prefetch: { prefetchAll: true, defaultStrategy: 'hover' },
  compressHTML: true,
});
