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
| **Formulaire et rendez-vous → endpoints PHP** (`public/api/`) | L'hébergement mutualisé exécute PHP nativement : le formulaire envoie un mail et la prise de rendez-vous tient son agenda sans service tiers, sans abonnement, sans données qui transitent par un prestataire américain. |
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
    rendez-vous.json       Disponibilités d'Alice et textes de la prise de rendez-vous
    offres/*.md            Une page par offre (ordre, climat, glyphe, textes, blocs pratiques)
    pages/*.md             Accueil, Qui suis-je, Contact
    legal/*.md             Mentions légales et confidentialité
  components/              Glyphe, Fil, Portes, Formulaire, Filigrane…
  layouts/                 Base (SEO, polices, en-tête, pied), Placard, PageLegale
  pages/                   Les routes du site
  styles/global.css        Le système Fil Pensant : couleurs, typographie, rythme
public/
  api/contact.php          Réception du formulaire et envoi du mail
  api/creneaux.php         Les créneaux téléphoniques encore libres
  api/rendez-vous.php      Demande d'un créneau, puis décision d'Alice
  api/agenda.php           Découpage des créneaux et rangement des demandes
  api/courrier.php         Envoi SMTP, partagé par le formulaire et les rendez-vous
  .htaccess                HTTPS, domaine canonique, page 404, cache, sécurité
  favicon.svg
.pages.yml                 Configuration de l'interface d'édition
```

### Le système graphique en trois règles

1. **Deux registres typographiques seulement**, dans un rapport d'au moins un à six : les mots
   essentiels en Cormorant Garamond italique (`.display`), les informations pratiques en petites
   capitales très espacées (`.micro`). Le contraste *est* la hiérarchie — pas d'encadré, pas d'aplat.
2. **Un climat par offre.** La couleur d'accent se pose sur `data-climat="…"` — le `<body>`, ou un
   volet de la section entreprise — et se propage par la variable `--accent`. Sept climats existent :
   `garance`, `ambre`, `prune`, `vert`, `bronze`, `nuit`, `ardoise`.
3. **Un geste graphique par offre**, jamais expliqué : en filigrane immense et pâle derrière le
   titre, et en sceau net sous la citation. Les glyphes vivent dans `src/components/Glyphe.astro`.
   Six gestes pour les offres — le rayonnement, le vis-à-vis, le méandre, la flèche du temps, le
   collectif, les cernes — auxquels s'ajoutent la suspension (pages légales), la bifurcation
   (contact) et la marque, qui signe *Qui suis-je*. Ils sont tracés dans une boîte de 200 × 200, à
   40 px par défaut, et ne descendent jamais sous 22 px.
4. **Les deux offres entreprise se lisent côte à côte**, sous un chapeau commun, au lieu de suivre
   les autres dans la liste des portes. La section est pilotée par le CMS : les offres citées dans
   *Accueil → La section « En entreprise »* sortent d'elles-mêmes de la liste des portes.

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

- **PHP**, pour les scripts de `public/api/`. C'est la seule exigence dynamique, mais elle est
  éliminatoire : les offres statiques d'entrée de gamme ne conviennent pas.
- **Un dossier accessible en écriture au-dessus de `public_html`**, pour les demandes de rendez-vous.
  C'est le cas par défaut chez Hostinger ; rien à créer à la main.
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

## La prise de rendez-vous téléphonique

Sous le formulaire, la page contact propose des créneaux de quinze minutes au téléphone. Alice
valide chaque demande : rien ne se pose dans son agenda sans son accord.

### Ce qu'Alice fait, et où

Tout se règle dans le CMS, section **Rendez-vous téléphoniques**. Elle y décrit ses **habitudes** —
« mardi de 14 h à 16 h » — et non des dates une à une : la règle vaut pour tous les mardis à venir,
donc il n'y a rien à entretenir chaque semaine. Elle y déclare aussi ses vacances, la durée d'un
appel, le battement entre deux, la prévenance minimale et l'horizon de réservation. Une case
**Proposer des rendez-vous** retire le bloc de la page sans rien supprimer.

### Le trajet d'une demande

1. Le visiteur choisit un créneau, laisse son nom, son numéro, son mail et un mot sur ce qui
   l'amène.
2. La demande est déposée sur le serveur et le créneau disparaît aussitôt de la liste. Le visiteur
   reçoit un accusé de réception ; Alice reçoit la demande avec **un lien**.
3. Ce lien ouvre `/rendez-vous/decider/`, qui affiche la demande et deux boutons : *Accepter* ou
   *Proposer un autre moment*.
4. À l'acceptation, le visiteur reçoit la confirmation avec un fichier `.ics` à glisser dans son
   agenda ; Alice reçoit le sien. Au refus, le visiteur reçoit un mot et le créneau redevient libre.
5. Sans réponse d'Alice au bout du délai réglé (72 heures par défaut), la demande expire d'elle-même
   et le créneau se rouvre. Un oubli ne condamne donc jamais un moment.

### Trois choix de conception qui méritent une explication

**Le mail d'Alice ne contient qu'un lien, et ce lien ne décide de rien.** Certains services de
messagerie ouvrent les liens d'un message pour les analyser : un lien « accepter » se déclencherait
tout seul, avant même qu'Alice ait lu la demande. Le lien ouvre donc une page, et seule la pression
d'un bouton — un POST — engage quelque chose.

**Le nom du fichier est le créneau.** Une demande est un fichier nommé `2026-08-18-1400.json`, créé
en mode exclusif. Deux visiteurs qui cliquent sur le même créneau à la même seconde ne peuvent pas
créer deux fois le même fichier : c'est le système de fichiers qui arbitre, donc l'arbitrage est
indivisible et il n'y a aucun verrou à tenir.

**Les créneaux sont calculés par le serveur, jamais par le navigateur.** Lui seul sait l'heure qu'il
est réellement à Paris, quel que soit le fuseau du visiteur, et ce qui est déjà pris.

### Où vivent les demandes

Dans un dossier `rendez-vous/` placé **un cran au-dessus de `public_html`**, à côté de `smtp.php` :
les noms, numéros et adresses n'y sont lisibles par personne depuis le web. Le dossier se crée tout
seul au premier besoin. Si l'hébergement refuse d'y écrire, le script se rabat sur un dossier caché
dans la racine publiée, que le `.htaccess` protège déjà.

Le jeton des liens de décision n'est **jamais stocké en clair** : seule son empreinte l'est. Un accès
en lecture au dossier ne permettrait pas de fabriquer un lien valable.

Les demandes archivées sont effacées automatiquement au bout de quatre-vingt-dix jours — c'est ce
qu'annonce la politique de confidentialité.

### Tester en local

`npm run dev` ne suffit pas : il n'exécute pas PHP, donc la liste des créneaux reste vide et le bloc
affiche son message de repli. Utilisez `npm run preview:php`. L'envoi des mails, lui, exige un
`smtp.php` valide — sans quoi la demande est refusée avec un message clair plutôt que de dormir dans
un dossier que personne ne regarde.

---

## Ce qui reste à faire avant la mise en ligne publique

- [ ] Remplacer les textes marqués `TEXTE PROVISOIRE` : **Entretiens individuels** et **Qui suis-je**.
- [ ] Faire valider les cinq autres textes d'offres, puis passer leur état en *Final*.
- [ ] Ajouter la **photo d'Alice** (accueil et Qui suis-je) via le CMS.
- [ ] Compléter **mentions légales** et **confidentialité** à l'immatriculation (SIRET, statut, TVA).
- [ ] Trancher les tarifs qui portent encore des crochets dans le document des six prestations.
- [ ] Créer une image de partage `public/og.png` (1200 × 630) pour les liens envoyés aux prescripteurs.
- [ ] Créer `public/apple-touch-icon.png` (180 × 180).
- [ ] Faire valider par Alice la planche des six gestes et le climat `bronze` d'*En résidence*.
- [ ] Faire relire par Alice le texte de l'**atelier philosophique en entreprise** : la page est en
      ligne avec un texte provisoire, écrit d'après le kit web et le format de La Cordée.

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
- **Les climats et les gestes.** Un climat et un geste par offre, d'après le kit web fourni :
  garance et le rayonnement pour La Cordée, bleu nuit et le vis-à-vis pour les entretiens, prune et
  le méandre pour les grands tournants, ambre et la flèche du temps pour la permanence, vert et le
  collectif pour l'atelier en entreprise, bronze et les cernes pour la résidence. Le kit nomme
  « ardoise » le bleu des entretiens, mais donne la valeur du bleu nuit du site : c'est la valeur qui
  a été suivie, l'ardoise restant le climat des pages légales et de *Qui suis-je*.
- **Verbes des entretiens individuels.** Proposés faute de texte source : *s'attabler, déplier,
  éprouver, poursuivre*.
