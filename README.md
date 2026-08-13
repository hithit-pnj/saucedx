# La Sauce d'Exister — site vitrine

Site statique de dialogues philosophants à Annecy.
Identité graphique **Le Fil Pensant**, contenus rédigés par Alice.

> **Copie de travail autonome.** Ce dépôt reprend à l'identique le site construit pour Alice,
> mais sans aucun de ses accès : domaine, hébergement, dépôt Git et CMS sont les vôtres. Le
> domaine n'est écrit en dur nulle part, il se règle en un endroit (voir *Premier paramétrage*).
> L'objectif est de pouvoir construire seul, puis transmettre les accès une fois le site stable.

---

## La pile technique, et pourquoi

| Choix | Raison |
| --- | --- |
| **Astro 5**, sortie statique | Le site produit du HTML pur. Aucune base de données, aucun serveur à maintenir, chargement quasi instantané, et une durée de vie qui se compte en années plutôt qu'en versions majeures. |
| **Contenus en Markdown / YAML** dans `src/content` | Les textes sont séparés du code. Alice peut les modifier sans toucher à une ligne de gabarit, et chaque modification est versionnée dans Git — donc annulable. |
| **Pages CMS** (`.pages.yml`) | Interface d'édition en français, gratuite, hébergée. Elle écrit directement dans le dépôt Git ; la mise en ligne se déclenche toute seule. Aucun abonnement, aucun serveur CMS à surveiller. |
| **Formulaire → endpoint PHP** (`public/api/contact.php`) | L'hébergement mutualisé exécute PHP nativement : le formulaire envoie un mail sans service tiers, sans abonnement, sans données qui transitent par un prestataire américain. |
| **Polices auto-hébergées** (`@fontsource`) | Cormorant Garamond et EB Garamond servies depuis le domaine : pas d'appel à Google Fonts, donc pas de bandeau cookies à prévoir et un affichage plus rapide. |
| **Aucun outil de mesure d'audience** | Rien à déclarer, rien à consentir. |

Le site ne pèse qu'une poignée de kilo-octets de JavaScript (le menu mobile, le formulaire et deux
animations discrètes) ; sans JavaScript, tout reste utilisable, formulaire compris.

---

## Démarrer

```bash
npm install
npm run dev          # http://localhost:4321
```

Autres commandes :

```bash
npm run build        # vérification des types + génération dans dist/
npm run preview      # sert dist/ tel qu'il sera en ligne
npm run preview:php  # idem, avec PHP, pour tester réellement le formulaire (PHP requis)
```

Le formulaire de contact ne fonctionne pas sous `npm run dev` : le serveur de développement
n'exécute pas PHP. Utilisez `npm run preview:php`, ou testez directement en ligne.

---

## Le domaine, et où il se règle

Le site tourne sur **saucedexister.fr**, hébergé chez Hostinger. Le domaine n'est écrit en dur
nulle part dans le code : il vient d'une variable, lue au moment de la construction.

| Où | Quoi | Quand |
| --- | --- | --- |
| `.env` (copié depuis `.env.example`) | `SITE_URL=https://saucedexister.fr` | en local, à créer après un clone |
| Variable de dépôt `SITE_URL` | la même valeur | sur GitHub, pour la construction automatique |

Sur GitHub, la variable se pose dans **Settings → Secrets and variables → Actions**, onglet
**Variables** — pas *Secrets*, ce n'est pas une donnée sensible. Sans elle, le site se construit
avec un domaine d'exemple : l'apparence est intacte, mais le plan du site, les adresses
canoniques et les vignettes de partage pointent dans le vide.

Le reste suit tout seul : `robots.txt` est généré à partir de `SITE_URL`, la redirection `www`
du `.htaccess` ne nomme aucun domaine, et les pages de confirmation du formulaire lisent
l'adresse mail dans les réglages. Si vous exploitez un second domaine à rediriger vers le
principal, deux lignes sont à décommenter dans `public/.htaccess`.

Trois endroits nomment le domaine en toutes lettres, parce qu'ils relèvent du contenu et non de
la configuration : `src/content/reglages.json`, les deux pages de `src/content/legal/` et le
message d'erreur de `src/content/pages/contact.md`. Ils s'éditent depuis le CMS. Un quatrième,
`public/api/contact.php`, porte l'adresse de destination en tête de fichier.

Changer de domaine, ce serait donc : la variable `SITE_URL`, ces quatre fichiers, et le nom de
l'hébergeur dans les mentions légales.

---

## Où se trouve quoi

```
src/
  content/
    reglages.json          Nom, mail, Instagram, ville — les constantes du site
    offres/*.md            Une page par offre (ordre, climat, glyphe, textes, blocs pratiques)
    pages/*.md             Accueil, Qui suis-je, Contact
    legal/*.md             Mentions légales et confidentialité
  components/              Glyphe, Fil, Portes, Formulaire, Filigrane…
  layouts/                 Base (SEO, polices, en-tête, pied), Placard, PageLegale
  pages/                   Les routes du site
  styles/global.css        Le système Fil Pensant : couleurs, typographie, rythme
public/
  api/contact.php          Réception du formulaire et envoi du mail
  .htaccess                HTTPS, domaine canonique, page 404, cache, sécurité
  favicon.svg
.pages.yml                 Configuration de l'interface d'édition
```

### Le système graphique en trois règles

1. **Deux registres typographiques seulement**, dans un rapport d'au moins un à six : les mots
   essentiels en Cormorant Garamond italique (`.display`), les informations pratiques en petites
   capitales très espacées (`.micro`). Le contraste *est* la hiérarchie — pas d'encadré, pas d'aplat.
2. **Un climat par offre.** La couleur d'accent se pose sur `<body data-climat="…">` et se propage
   par la variable `--accent`. Six climats existent : `garance`, `ambre`, `prune`, `vert`, `nuit`,
   `ardoise`.
3. **Un geste graphique par offre**, jamais expliqué : en filigrane immense et pâle derrière le
   titre, et en sceau net sous la citation. Les glyphes vivent dans `src/components/Glyphe.astro`.

Le fil vertical relie les blocs et se trace au scroll. Sans JavaScript, ou si le visiteur a demandé
moins d'animations, il est simplement là.

---

## Modifier les textes (Alice)

**Première mise en place : voir [GUIDE-CMS.md](GUIDE-CMS.md)** — guide pas à pas, sans prérequis,
qui couvre la connexion GitHub ↔ Pages CMS, le déploiement automatique et les problèmes courants.

Au quotidien :

1. Se connecter sur [app.pagescms.org](https://app.pagescms.org) avec le compte GitHub du projet.
2. Choisir le dépôt, puis la section à modifier : *Les offres*, *Accueil*, *Qui suis-je*, *Contact*,
   *Pages légales* ou *Réglages du site*.
3. Enregistrer. Le site se reconstruit et se met en ligne tout seul en deux à trois minutes.

Chaque offre porte un **état du texte** (*Final*, *En relecture*, *À écrire*). En développement, un
bandeau signale les pages qui ne sont pas encore en *Final* ; il n'apparaît jamais en ligne.

Pour retirer une page du site sans la supprimer, décocher **Page en ligne**.

Le renommage et la suppression sont bloqués sur les offres — le nom du fichier fait l'adresse de la
page — ainsi que sur les pages légales et les réglages.

**Attention côté développement** : le CMS écrit directement dans le dépôt. Faire `git pull` avant
de reprendre le travail, sinon on écrase les modifications d'Alice.

---

## Mise en ligne

Un `push` sur `main` déclenche `.github/workflows/deploiement.yml`, qui construit le site et
l'envoie par FTPS chez l'hébergeur.

Secrets et variables à renseigner dans **GitHub → Settings → Secrets and variables → Actions** :

| Nom | Type | Valeur |
| --- | --- | --- |
| `FTP_SERVEUR` | secret | l'hôte FTP fourni par l'hébergeur |
| `FTP_UTILISATEUR` | secret | l'identifiant FTP |
| `FTP_MOT_DE_PASSE` | secret | le mot de passe FTP |
| `FTP_DOSSIER` | secret | la racine du site — voir l'avertissement ci-dessous |
| `SITE_URL` | variable | `https://votredomaine.fr` |
| `FTP_PROTOCOLE` | variable | uniquement si l'hébergeur refuse le FTPS ; sinon ne pas créer |

**Le réglage qui se rate le plus souvent, c'est `FTP_DOSSIER`.** Selon l'hébergeur, le compte FTP
atterrit soit à la racine du site — la valeur est alors `/` — soit un cran au-dessus, et il faut
`/web/`. Se tromper ne provoque aucune erreur : le déploiement réussit, mais le site répond sur
`votredomaine.fr/web/` et l'accueil affiche une liste de fichiers. Connectez-vous une fois en FTP
pour voir où vous arrivez, plutôt que de deviner.

### Ce qu'il faut de l'hébergeur

- **PHP**, pour `public/api/contact.php`. C'est la seule exigence dynamique, mais elle est
  éliminatoire : les offres statiques d'entrée de gamme ne conviennent pas.
- **Un certificat** Let's Encrypt ou équivalent. À activer **avant** la première visite : le
  `.htaccess` force HTTPS, donc sans certificat le navigateur affiche un avertissement.
- **Une adresse mail** au domaine, qui sert à la fois de destinataire et d'expéditeur du
  formulaire. Une seule suffit.
- **SPF et DKIM** activés sur le domaine. Sans cela les mails du formulaire partent en
  indésirable, et les messages se perdent sans que personne s'en aperçoive.

Le site construit pèse **environ 0,6 Mo** : la contrainte n'est jamais l'espace disque, seulement
la disponibilité de PHP.

---

## Le formulaire de contact

Trois champs (nom, mail, message) et une case de consentement RGPD. Aucune donnée n'est stockée sur
le serveur : le message part par mail, avec le `Reply-To` du visiteur — répondre au mail répond
directement à la personne.

Protections, toutes invisibles pour un humain :

- un champ leurre que seuls les robots remplissent ;
- un délai minimum de trois secondes entre l'ouverture de la page et l'envoi ;
- cinq envois par heure et par adresse IP au maximum ;
- nettoyage des retours à la ligne dans les en-têtes, contre l'injection de destinataires.

Avec JavaScript, la confirmation s'affiche sans quitter la page. Sans JavaScript, le visiteur est
redirigé vers `/contact/merci/` ou `/contact/erreur/`.

---

## Ce qui reste à faire avant la mise en ligne publique

- [ ] Remplacer les textes marqués `TEXTE PROVISOIRE` : **Entretiens individuels** et **Qui suis-je**.
- [ ] Faire valider les cinq autres textes d'offres, puis passer leur état en *Final*.
- [ ] Ajouter la **photo d'Alice** (accueil et Qui suis-je) via le CMS.
- [ ] Compléter **mentions légales** et **confidentialité** à l'immatriculation (SIRET, statut, TVA).
- [ ] Trancher les tarifs qui portent encore des crochets dans le document des six prestations.
- [ ] Créer une image de partage `public/og.png` (1200 × 630) pour les liens envoyés aux prescripteurs.
- [ ] Créer `public/apple-touch-icon.png` (180 × 180).
- [ ] Définir les climats et glyphes des deux offres ajoutées si Alice ne garde pas ceux proposés
      (`nuit` + cercles concentriques pour les entretiens, `ambre` + suspension pour l'entreprise).

## Décisions actées

**Hébergement mutualisé payant, avec PHP.** La question d'un hébergement gratuit a été instruite
puis écartée, et le raisonnement vaut la peine d'être conservé :

- **GitHub Pages** impose un dépôt **public** sur le palier gratuit, et dépublie le site dès qu'il
  passe en privé.
- Les **offres statiques d'entrée de gamme**, souvent offertes avec un domaine, n'exécutent pas
  PHP. Le formulaire devrait alors passer par un tiers : soit un service hors UE, soit un relais
  serverless, donc un compte de plus, une clé secrète à faire tourner et deux cibles de
  déploiement.
- **Cloudflare Pages** est gratuit et accepte les dépôts privés, mais déplace le DNS et ajoute une
  plateforme à gérer.

Quelques dizaines d'euros par an suppriment tout intermédiaire : les messages du formulaire —
parfois des confidences sur un deuil ou une cession d'entreprise — ne transitent par aucun tiers.
C'est aussi la configuration la plus simple à maintenir, ce qui était une exigence explicite.

Le formulaire n'utilise **aucune clé d'API** : une clé de service d'envoi ne peut pas vivre dans
une page statique sans être lisible par n'importe quel visiteur.

## Transmettre le projet à Alice

Le jour venu, il n'y a rien à reconstruire — seulement des accès à ouvrir :

1. **Le dépôt GitHub** : l'inviter en *Write* (**Settings → Collaborators**), ou lui transférer la
   propriété (**Settings → General → Transfer ownership**), ce qui conserve tout l'historique.
2. **Le CMS** : elle se connecte à Pages CMS avec son propre compte GitHub, autorise le dépôt, et
   retrouve la même interface. Rien à reconfigurer.
3. **L'hébergement et le domaine** : à transférer chez elle si le contrat le prévoit, ou à laisser
   à votre nom avec une facturation refacturée — c'est une décision contractuelle, pas technique.

Les secrets de déploiement n'ont pas à être partagés : ils vivent dans le coffre GitHub et
continuent de fonctionner après le changement de propriétaire.

## Décisions à confirmer

- **Regroupement La Cordée.** Les ateliers et la permanence partagent une page, comme demandé dans
  le cahier des charges, avec deux formats nettement distincts et deux jeux de verbes.
- **Cinquième climat.** Le système en comptait quatre pour six prestations ; les entretiens
  individuels ont reçu le bleu nuit et les cercles concentriques, la permanence en entreprise garde
  l'ambre et la suspension. À valider ou à remplacer.
- **Verbes des entretiens individuels.** Proposés faute de texte source : *s'attabler, déplier,
  éprouver, poursuivre*.
