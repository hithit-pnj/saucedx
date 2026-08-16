import type { APIRoute } from 'astro';

// Généré plutôt que posé dans public/ : l'adresse du sitemap suit ainsi
// automatiquement le domaine configuré, sans fichier à corriger à la main.
export const GET: APIRoute = ({ site }) => {
  const sitemap = new URL('sitemap-index.xml', site).href;

  const corps = [
    'User-agent: *',
    'Allow: /',
    'Disallow: /contact/merci/',
    'Disallow: /contact/erreur/',
    'Disallow: /rendez-vous/',
    'Disallow: /api/',
    '',
    `Sitemap: ${sitemap}`,
    '',
  ].join('\n');

  return new Response(corps, {
    headers: { 'Content-Type': 'text/plain; charset=utf-8' },
  });
};
