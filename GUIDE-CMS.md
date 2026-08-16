# Brancher le CMS — guide pas à pas

Ce guide part de zéro et ne suppose aucune connaissance de Git. Suivez-le dans l'ordre :
chaque étape prépare la suivante.

Comptez **20 à 30 minutes** pour tout mettre en place, une seule fois.

---

## 1. Comprendre le montage avant de cliquer

C'est la partie la plus importante du guide. Une fois que le schéma est clair, le reste
n'est plus que de la mécanique.

Un site « classique » avec CMS ressemble à ça : un serveur fait tourner WordPress, WordPress
range les textes dans une base de données, et fabrique les pages à chaque visite. Il faut
donc surveiller le serveur, mettre à jour les extensions, sauvegarder la base — et payer
pour tout ça.

Notre montage remplace la base de données par **GitHub**, et le serveur par un **dossier de
fichiers HTML**. Concrètement :

```
   Alice écrit un texte
   sur app.pagescms.org
            │
            │  Pages CMS enregistre le texte
            │  dans un fichier du dépôt GitHub
            ▼
   GitHub  (github.com/VOTRE-COMPTE/VOTRE-DEPOT)
            │
            │  L'arrivée du texte réveille GitHub Actions,
            │  qui reconstruit tout le site
            ▼
   GitHub Actions  →  fabrique un dossier de pages HTML
            │
            │  puis l'envoie par FTP
            ▼
   Hostinger  →  saucedexister.fr
```

Quatre conséquences utiles :

- **Il n'y a rien à sauvegarder.** GitHub garde l'historique complet de chaque texte. Toute
  modification est annulable, même six mois plus tard.
- **Il n'y a rien à mettre à jour en urgence.** Le site en ligne est du HTML : il n'a pas de
  faille à corriger, pas d'extension à surveiller.
- **Alice ne voit jamais Git.** Elle voit des formulaires en français. Git travaille dessous.
- **Rien de tout ça ne coûte d'argent.** GitHub et Pages CMS sont gratuits à cette échelle ;
  seul l'hébergement Hostinger est payant, une trentaine d'euros la première année, domaine
  compris.

### Le vocabulaire, en une ligne chacun

| Mot | Ce que c'est |
| --- | --- |
| **Git** | Un système qui garde l'historique de chaque modification de fichier. |
| **Dépôt** (*repository*) | Le dossier de projet, avec tout son historique. Ici : `VOTRE-COMPTE/VOTRE-DEPOT`. |
| **GitHub** | Le site qui héberge le dépôt en ligne. |
| **Commit** | Une modification enregistrée, avec sa date, son auteur et sa description. |
| **Push** | Envoyer ses commits locaux vers GitHub. |
| **Branche** | Une version parallèle du projet. Ici, une seule : `main`. |
| **GitHub Actions** | Le robot de GitHub qui reconstruit le site à chaque commit. |
| **Pages CMS** | L'interface d'édition en français qu'utilisera Alice. |

---

## 2. Vérifier que le code est bien arrivé sur GitHub

Le dépôt a déjà été envoyé. Vérifions-le.

1. Ouvrez **https://github.com/VOTRE-COMPTE/VOTRE-DEPOT**
2. Vous devez voir une liste de fichiers et de dossiers : `src`, `public`, `.pages.yml`,
   `README.md`, et les documents de travail (`01 - Brief…`, `02 - Design…`).
3. En bas de la page, le contenu du `README.md` s'affiche automatiquement.

Si la page dit encore *« This repository is empty »*, rafraîchissez. Si elle est toujours
vide, le `push` n'a pas abouti — voir *Problèmes courants* à la fin.

### Si vous devez repousser du code plus tard

Depuis le dossier du projet, dans un terminal :

```bash
git add -A                        # prendre toutes les modifications
git commit -m "Corrige le texte des ateliers"
git push                          # envoyer sur GitHub
```

`git add -A` puis `git commit` créent l'enregistrement, `git push` l'envoie. Ces trois
commandes sont les seules à connaître.

---

## 3. Connecter Pages CMS au dépôt

Pages CMS est un service en ligne. Il ne stocke rien : il lit et écrit dans votre dépôt
GitHub, avec la permission que vous lui donnez.

### 3.1 Se connecter

1. Allez sur **https://app.pagescms.org**
2. Cliquez sur **Sign in with GitHub**.
3. GitHub demande d'autoriser Pages CMS. Cliquez sur **Authorize**.

À ce stade, Pages CMS sait qui vous êtes, mais n'a encore accès à aucun dépôt.

### 3.2 Donner accès au seul dépôt du projet

1. Dans Pages CMS, cliquez sur **Add repository** (ou **Install GitHub App**).
2. GitHub ouvre une page d'installation. Choisissez le compte **VOTRE-COMPTE**.
3. **Important** : à la question *Repository access*, choisissez
   **Only select repositories**, puis sélectionnez **VOTRE-DEPOT**.

   Ne choisissez pas *All repositories*. Le principe est simple : on n'accorde jamais plus
   d'accès que nécessaire. Pages CMS n'a besoin que de ce dépôt.
4. Cliquez sur **Install**.

Vous revenez sur Pages CMS, avec `VOTRE-DEPOT` dans la liste.

### 3.3 Ouvrir le projet

1. Cliquez sur **VOTRE-DEPOT**.
2. Pages CMS demande quelle branche utiliser : choisissez **main**.
3. Pages CMS lit le fichier `.pages.yml` à la racine du dépôt et construit l'interface à
   partir de lui.

Vous devez voir, dans la colonne de gauche :

- **Les offres** — les six pages d'offres
- **Accueil**
- **Qui suis-je**
- **Contact**
- **Pages légales**
- **Réglages du site**

Si vous voyez un message d'erreur de configuration à la place, c'est que `.pages.yml`
contient une faute. Le message indique la ligne fautive.

---

## 4. Le test : modifier un texte et vérifier qu'il arrive

Faisons le tour complet du circuit, sur une modification sans conséquence.

### 4.1 Modifier depuis le CMS

1. Cliquez sur **Les offres** dans la colonne de gauche.
2. Cliquez sur **À La Cordée**.
3. Trouvez le champ **Ouverture de la page → Sur-titre**. Il contient
   *« Ateliers et permanence — Annecy »*.
4. Remplacez-le par *« Ateliers et permanence — Annecy, test CMS »*.
5. Cliquez sur **Save**.

### 4.2 Vérifier que GitHub a reçu la modification

1. Retournez sur **https://github.com/VOTRE-COMPTE/VOTRE-DEPOT**
2. Cliquez sur **Commits** (ou sur l'icône d'horloge, au-dessus de la liste de fichiers).
3. Un nouveau commit doit apparaître, daté de l'instant, avec votre nom d'utilisateur GitHub.
4. Cliquez dessus : GitHub affiche la modification en rouge et vert — l'ancienne ligne, puis
   la nouvelle.

Si vous voyez ce commit, **le circuit Alice → GitHub fonctionne**. C'est le point crucial.

### 4.3 Vérifier le résultat en local

Tant que l'étape 5 n'est pas faite, le site en ligne n'existe pas encore. En attendant, on
récupère la modification sur votre machine :

```bash
git pull        # récupérer les modifications faites depuis le CMS
npm run dev     # lancer le site en local
```

Ouvrez **http://localhost:4321/a-la-cordee/** : le sur-titre modifié doit s'afficher sous
le titre.

`git pull` est le mouvement inverse de `git push` : il descend les modifications au lieu de
les monter. **Prenez le réflexe de faire `git pull` avant de commencer à travailler**, sinon
vous risquez de modifier une version périmée d'un fichier qu'Alice vient de changer.

### 4.4 Remettre le texte d'origine

Retournez dans Pages CMS, retirez « , test CMS », enregistrez. Puis `git pull` en local.

---

## 5. Brancher la mise en ligne automatique

Le montage retenu : domaine `saucedexister.fr` et hébergement **Hostinger Single**, tous deux
pris chez le même fournisseur. C'est le cas le plus simple : les serveurs de noms sont déjà
ceux de Hostinger, il n'y a aucun réglage DNS à faire à la main.

Le fichier qui pilote la mise en ligne existe déjà dans le dépôt :
`.github/workflows/deploiement.yml`. Il ne demande aucune modification — seulement quatre
identifiants et une variable, à déposer sur GitHub.

Les neuf étapes qui suivent sont dans l'ordre, et l'ordre compte : le certificat avant le
premier envoi, le dossier vidé avant le premier envoi lui aussi.

### 5.1 Vérifier que le domaine porte bien un site

Dans le hPanel, **Sites web**. `saucedexister.fr` doit y apparaître comme un site, pas
seulement comme un nom de domaine acheté.

S'il n'y a pas encore de site, créez-en un et choisissez **site vide** — surtout pas WordPress
ni le constructeur de sites. Ces deux-là déposent leurs propres fichiers dans le dossier du
site, et ce sont eux qui s'afficheraient à la place du nôtre.

### 5.2 Activer le certificat SSL, avant tout le reste

Le `.htaccess` du site force le HTTPS. Sans certificat valide, les visiteurs tomberaient sur
un avertissement de sécurité en travers de la page.

Dans le tableau de bord du site : **Sécurité → SSL**. Il s'installe en général tout seul dans
les minutes qui suivent le rattachement du domaine ; sinon, bouton d'installation. Attendez
l'état **actif**.

Vérification : `https://saucedexister.fr` doit répondre sans avertissement, même si ce n'est
encore qu'une page vide.

### 5.3 Vider le dossier du site

**Fichiers → Gestionnaire de fichiers**, ouvrez `public_html`, et supprimez tout ce qui s'y
trouve.

Cette étape n'est pas cosmétique. Le déploiement *ajoute* des fichiers, il ne supprime pas
ceux qu'il ne connaît pas. Or Hostinger dépose une page de courtoisie, souvent un
`index.php` — et un `index.php` passe avant notre `index.html`. Le site serait en ligne sans
que personne ne le voie.

### 5.4 Créer l'adresse `contact@saucedexister.fr`

**Emails**, puis créez la boîte `contact@saucedexister.fr`. Une seule suffit : elle reçoit les
messages du formulaire *et* les expédie, l'adresse du visiteur étant placée en `Reply-To`.
Un simple « Répondre » écrit donc bien à la personne, pas à soi-même.

C'est exactement cette adresse qui est déjà inscrite dans `public/api/contact.php` et dans
`src/content/reglages.json`. En changer voudrait dire modifier ces deux fichiers.

Puis, dans **Domaines → Zone DNS**, vérifiez que la création de la boîte a bien posé :

- les enregistrements **MX** vers Hostinger ;
- un **TXT** de type `v=spf1 include:_spf.mail.hostinger.com ~all` ;
- un **DKIM**.

Ce n'est pas optionnel. Sans SPF ni DKIM, les messages du formulaire partent en indésirable
chez le destinataire, silencieusement — c'est le pire scénario possible pour un site dont la
seule fonction est d'être contacté.

### 5.4 bis Donner au formulaire de quoi expédier

Étape indispensable, et celle qu'on découvre le plus tard, parce que son absence ne produit
aucun message d'erreur : le formulaire annonce « message envoyé » et rien n'arrive.

En cause, la fonction `mail()` de PHP, qui expédie sans s'authentifier. Hostinger l'accepte mais
l'achemine mal, et sa documentation le dit clairement : le courrier non authentifié n'est ni
signé, ni couvert par SPF et DKIM, donc filtré ou jeté. Pire, `mail()` renvoie « vrai » même
lorsque le message est abandonné — d'où le silence.

Le site sait donc expédier autrement, par SMTP authentifié, dès qu'il trouve les identifiants de
la boîte. Ces identifiants se déposent **une seule fois**, à la main.

Dans **Fichiers → Gestionnaire de fichiers**, placez-vous dans `domains/saucedexister.fr` —
c'est-à-dire **à côté** de `public_html`, et surtout pas dedans — et créez un fichier nommé
`smtp.php` contenant :

```php
<?php

return [
    'hote'         => 'smtp.hostinger.com',
    'port'         => 465,
    'utilisateur'  => 'contact@saucedexister.fr',
    'mot_de_passe' => 'le mot de passe de la boîte contact@',
];
```

Le mot de passe est celui qui ouvre le webmail ; il n'y a pas de mot de passe d'application à
créer. Le modèle commenté se trouve dans le dépôt, sous `smtp.exemple.php`.

L'emplacement n'est pas négociable. Hors de la racine web, ce fichier est invisible depuis
Internet même si le serveur cessait d'interpréter le PHP. Il échappe aussi au dépôt Git, dont
l'historique conserve tout, même après suppression. Et le déploiement ne peut ni l'écraser ni
l'effacer, puisqu'il ne le connaît pas : posé une fois, il survit à toutes les mises en ligne.

Tant que ce fichier n'existe pas, le site retombe sur `mail()`. C'est utile en local, où l'on
veut juste vérifier que le formulaire réagit ; en ligne, cela revient à ne rien envoyer.

### 5.5 Relever les accès FTP

Dans le tableau de bord du site : **Fichiers → Comptes FTP**. Le compte principal existe déjà,
créé en même temps que le site. La page affiche tout ce qu'il faut :

| Ce qu'affiche Hostinger | Ce que c'est |
| --- | --- |
| **FTP IP** | l'adresse du serveur, une IP du genre `82.29.x.x` |
| **Nom d'utilisateur FTP** | de la forme `u123456789` pour le domaine principal |
| **Port** | `21`, toujours |
| **Dossier** | `public_html` — c'est là que va le site |

Le mot de passe, lui, n'est pas réaffiché : s'il vous est inconnu, posez-en un nouveau depuis
cette même page. Prenez bien le mot de passe **FTP**, ni celui du compte Hostinger ni celui de
SSH : c'est la cause n°1 des erreurs `530 login incorrect`.

Pour le serveur, préférez **`ftp.saucedexister.fr`** à l'IP. Hostinger crée ce sous-domaine
automatiquement, il pointe sur la bonne machine, et il continuera de fonctionner le jour où
l'hébergeur change l'adresse du serveur — ce qui arrive lors des migrations internes, sans
préavis.

Notez la valeur **exactement** telle quelle : `ftp.saucedexister.fr`, sans `ftp://` devant, sans
`:21` derrière, sans espace au bout. C'est un nom de machine, pas une adresse web, et le
déploiement le passe tel quel à la résolution DNS.

### 5.6 Confier ces accès à GitHub

Un mot de passe ne se met **jamais** dans un fichier du dépôt : tout le dépôt est lisible par
qui y a accès, et l'historique garde tout, même après suppression. GitHub propose un coffre
pour ça.

1. Sur GitHub, ouvrez le dépôt → **Settings** (onglet en haut à droite).
2. Colonne de gauche : **Secrets and variables → Actions**, onglet **Secrets**.
3. **New repository secret**, et créez ces quatre secrets, un par un :

| Nom exact du secret | Valeur |
| --- | --- |
| `FTP_SERVEUR` | `ftp.saucedexister.fr` |
| `FTP_UTILISATEUR` | `u123456789` |
| `FTP_MOT_DE_PASSE` | le mot de passe FTP |
| `FTP_DOSSIER` | `/public_html/` |

Les noms doivent être **exactement** ceux-là : le fichier de déploiement les appelle par ce
nom. Une fois enregistré, un secret n'est plus jamais affiché, même à vous.

Le `/public_html/` est le réglage qui se rate le plus souvent. Le compte FTP arrive un cran
au-dessus du site : s'y tromper produit soit un site répondant sur
`saucedexister.fr/public_html/`, soit une simple liste de fichiers à l'accueil.

### 5.7 Poser le domaine pour la construction du site

Au même endroit, mais dans l'onglet **Variables** et non *Secrets* :
**New repository variable**.

| Nom | Valeur |
| --- | --- |
| `SITE_URL` | `https://saucedexister.fr` |

Elle sert au plan du site, aux adresses canoniques et aux vignettes de partage sur les réseaux.
Sans elle, le site se construirait avec le domaine d'exemple, et les moteurs de recherche
indexeraient des adresses qui n'existent pas.

### 5.8 Lancer le premier déploiement

1. Sur GitHub, onglet **Actions**.
2. Colonne de gauche : **Mise en ligne**.
3. **Run workflow** → **Run workflow**.
4. Comptez deux à trois minutes. Une coche verte signifie que c'est en ligne ; une croix rouge
   s'ouvre sur le journal, qui pointe l'étape fautive.

Le déploiement part par défaut en **FTPS**, c'est-à-dire chiffré, et le serveur de Hostinger
l'accepte : vérification faite, `ftp.saucedexister.fr` répond `234 AUTH TLS successful`. Il n'y a
donc rien à changer, et surtout pas de raison de basculer en FTP en clair.

La variable `FTP_PROTOCOLE = ftp` existe comme filet de sécurité si un hébergeur futur refusait
le chiffrement — le journal mentionnerait alors `AUTH TLS` ou un code `534`. Une erreur FTP qui
ne parle pas de TLS ne se règle jamais avec cette variable : voir « Problèmes courants ».

Ensuite, plus rien à faire : chaque enregistrement dans le CMS déclenche ce déploiement tout
seul.

Une coche verte ne veut pas dire que le site est visible : elle dit seulement que les fichiers
ont été déposés quelque part. Si le site répond en 404 malgré un déploiement réussi, c'est que
ce « quelque part » n'est pas le dossier que le domaine publie — voir « Problèmes courants ».

### 5.9 Les quatre vérifications qui closent la mise en ligne

1. `https://saucedexister.fr` affiche la page d'accueil — ni « Index of / », ni la page de
   courtoisie de Hostinger.
2. Le cadenas est là, sans avertissement, et `http://` bascule bien vers `https://`.
3. `https://saucedexister.fr/robots.txt` se termine par
   `Sitemap: https://saucedexister.fr/sitemap-index.xml`. Si vous y lisez `exemple.fr`, c'est
   que la variable `SITE_URL` de l'étape 5.7 manque.
4. Envoyez un vrai message par le formulaire. Vérifiez qu'il arrive dans `contact@`, puis
   **répondez-y** : la réponse doit partir vers votre adresse de test, pas vers `contact@`.

### 5.10 Les limites de l'offre Single, à connaître d'avance

Rien de bloquant pour ce site, mais autant les savoir maintenant :

- **Un seul site.** Pas de préproduction sur le même compte. Pour prévisualiser une refonte,
  ce sera en local avec `npm run dev`.
- **Pas d'accès SSH.** Seul le FTP est disponible, ce qui suffit ici puisque le déploiement ne
  fait que déposer des fichiers.
- **La boîte mail est souvent offerte la première année seulement.** À surveiller au
  renouvellement : sans elle, le formulaire n'a plus de destinataire.
- **Le tarif remonte au renouvellement.** C'est le seul vrai point de vigilance de cette offre,
  et il est commercial, pas technique.

L'espace disque, lui, est hors sujet : le site pèse 0,56 Mo.

---

## 6. Ce qu'Alice a besoin de savoir, et rien de plus

Voici de quoi tient la formation à la livraison. Trois choses.

**Se connecter.** app.pagescms.org, bouton GitHub, puis `VOTRE-DEPOT`.

**Modifier un texte.** Cliquer sur la page dans la colonne de gauche, modifier les champs,
cliquer sur **Save**. Le site se met à jour tout seul en deux ou trois minutes. Il faut
parfois rafraîchir la page du site en ligne pour voir le changement.

**Les trois champs qui ne sont pas du texte.**

- **État du texte** : *À écrire* / *En relecture* / *Final*. C'est un pense-bête pour Alice
  et pour vous ; cela ne change rien à l'apparence du site.
- **Page en ligne** : décochez pour retirer une page du site — elle disparaît du menu et de
  l'accueil sans être supprimée. C'est le bon geste pour une page dont le texte n'est pas
  prêt.
- **Climat** et **Geste graphique** : la couleur et le symbole de la page. À ne toucher qu'à
  bon escient : ils forment la cohérence de la collection — un climat et un geste par offre,
  jamais deux offres avec le même.
- **La section « En entreprise »**, dans *Accueil* : les offres citées là se présentent côte à
  côte sur l'accueil, sous un chapeau commun, au lieu de suivre les autres dans la liste des
  portes. Retirer une offre de cette section la remet simplement dans la liste.

**Ce qu'elle ne peut pas casser**, volontairement :

- renommer ou supprimer une page d'offre (le nom du fichier fait l'adresse de la page) ;
- créer ou supprimer une page légale ;
- supprimer les réglages du site.

**Une invitation à ajouter à son compte.** Alice a besoin d'un compte GitHub gratuit, puis
d'être invitée sur le dépôt : **Settings → Collaborators → Add people**, en rôle **Write**.

---

## Annexe — Tenir l'agenda des rendez-vous téléphoniques

Sous le formulaire, la page contact propose des créneaux de quinze minutes au téléphone. Ce
paragraphe est écrit pour Alice.

### Déclarer vos disponibilités

Dans le CMS, section **Rendez-vous téléphoniques**. Vous décrivez des **habitudes**, pas des
dates : « Mardi, de 14:00 à 16:00 » vaut pour tous les mardis à venir. Il n'y a donc rien à
remettre à jour chaque semaine. Ajoutez autant de lignes que de plages hebdomadaires.

Le site découpe ces plages tout seul. Avec des appels de quinze minutes et un battement de
quinze minutes, « mardi de 14:00 à 16:00 » donne quatre créneaux : 14 h, 14 h 30, 15 h et
15 h 30.

**Vacances et empêchements** : ajoutez une ligne avec la date de début et la date de fin,
bornes comprises. Pour une seule journée, mettez la même date des deux côtés. Aucun créneau ne
sera proposé pendant cette période.

**Pour tout couper**, décochez **Proposer des rendez-vous** : le bloc disparaît de la page
contact. Les rendez-vous déjà confirmés, eux, ne sont pas annulés — prévenez les personnes
concernées vous-même.

### Quand quelqu'un demande un créneau

Vous recevez un mail avec son nom, son numéro, ce qui l'amène, et **un lien**. Ce lien ouvre une
page qui vous montre la demande et vous propose deux boutons : **Accepter ce moment** ou
**Proposer un autre moment**.

- **Accepter** : la personne reçoit aussitôt la confirmation et un fichier à glisser dans son
  agenda. Vous recevez le même. C'est vous qui appelez, au numéro indiqué.
- **Proposer un autre moment** : la personne reçoit un mot vous excusant, avec le lien vers la
  page contact. Le créneau redevient libre pour quelqu'un d'autre.

**Si vous ne répondez pas**, la demande expire au bout de trois jours et le créneau se rouvre
tout seul. Un oubli ne bloque donc jamais un moment définitivement.

Tant que vous n'avez pas tranché, le créneau est retiré du site : personne d'autre ne peut le
demander en même temps.

### Les réglages, et à quoi ils servent

| Réglage | Ce qu'il change |
| --- | --- |
| **Durée d'un appel** | La longueur annoncée, et la taille des créneaux. |
| **Battement** | Le temps de souffler entre deux appels. Deux appels ne se collent jamais. |
| **Prévenance minimale** | Personne ne peut réserver un créneau qui commence dans moins de ce délai. Vingt-quatre heures évite d'être appelée à l'improviste. |
| **Réservable jusqu'à** | Jusqu'où on peut réserver à l'avance. Trois semaines est un bon équilibre. |
| **Expiration d'une demande** | Au bout de combien de temps sans réponse le créneau se rouvre. |

---

## Annexe — Fermer temporairement le site

Le site dispose d'un interrupteur de maintenance qui ne demande ni déploiement, ni ligne de
commande, ni modification du code. Il tient en un fichier.

### Fermer

Dans le hPanel, **Fichiers → Gestionnaire de fichiers**, placez-vous dans le dossier du site —
`domains/saucedexister.fr/public_html` — et créez un fichier vide nommé exactement :

```
.maintenance
```

Le point de tête fait partie du nom. L'effet est immédiat : tout le site renvoie alors la page
`maintenance.html`, sobre et aux couleurs de l'identité, avec l'adresse de contact.

### Rouvrir

Supprimez ce fichier. C'est tout.

### Ce que la fermeture fait, et ne fait pas

Le serveur répond **503**, et non 404. La nuance compte : 503 signifie « repassez plus tard »
là où 404 signifie « cette page n'existe plus ». Les moteurs de recherche conservent donc le
référencement, alors qu'une série de 404 finirait par le faire tomber. Un en-tête `Retry-After`
les invite à revenir dans deux heures.

La fermeture **survit aux déploiements**, y compris à ceux qu'Alice déclenche en enregistrant
depuis le CMS. C'est délibéré : le fichier `.maintenance` n'est pas dans le dépôt, et le
déploiement ne supprime que ce qu'il connaît. Sans cette précaution, une simple correction de
texte rouvrirait le site au public sans que personne ne l'ait décidé.

Ce n'est pas une protection. Le site n'est pas *inaccessible* : les fichiers restent sur le
serveur, et qui connaît l'adresse exacte d'une image l'obtiendra toujours.

### Et le bouton natif de Hostinger ?

Il n'y en a pas pour ce site. Le commutateur « Mode de maintenance » visible dans le hPanel ne
concerne que les sites WordPress, où il passe par leur extension maison : il ne peut rien pour
du HTML statique. La documentation de Hostinger prescrit d'ailleurs, pour les sites
non-WordPress, exactement la méthode décrite ci-dessus.

Le seul levier réellement natif est ailleurs : **Avancé → Répertoires protégés par mot de
passe**. On y choisit le dossier du site, on définit un identifiant et un mot de passe, et le
navigateur les réclame avant d'afficher quoi que ce soit ; une icône de corbeille retire la
protection.

Les deux outils ne servent pas la même chose :

| | Fichier `.maintenance` | Mot de passe sur le répertoire |
| --- | --- | --- |
| Ce que voit le visiteur | une page aux couleurs du site | une fenêtre de connexion grise |
| Les fichiers restent atteignables | oui, par leur adresse directe | non, rien ne passe |
| Ce que reçoivent les moteurs | `503`, « repassez plus tard » | `401`, sans intérêt sur la durée |
| Bon pour | signaler une interruption au public | un site qui ne doit pas encore exister |

### Continuer à voir le site pendant la fermeture

Ouvrez `public/.htaccess` dans le dépôt et décommentez la ligne prévue à cet effet, en y
inscrivant votre adresse IP publique (cherchez « mon ip » dans un moteur de recherche) :

```apache
RewriteCond %{REMOTE_ADDR} !=203.0.113.7
```

Cette ligne-là, contrairement au fichier `.maintenance`, vit dans le dépôt : elle demande donc
un déploiement. Pensez à la recommenter ensuite, sans quoi elle deviendra fausse le jour où
votre adresse IP changera — ce qui, sur une connexion domestique, arrive tout seul.

### Si la page de maintenance ne s'affiche pas tout de suite

Hostinger interpose un CDN qui garde les réponses en mémoire. Videz son cache depuis
**Performance → CDN**, ou vérifiez en court-circuitant le CDN, comme expliqué dans
« Problèmes courants ».

---

## Annexe — Reprendre le projet sur une autre machine

### Avant tout : passer le dépôt en privé

Le dépôt est actuellement **public**, donc lisible et clonable par n'importe qui sur Internet.
Or il contient les documents de travail : le brief général et les attentes commerciales, le suivi
des contenus, le cahier des charges. Ces fichiers n'ont rien à faire sur la place publique.

Sur GitHub : **Settings → General → Danger Zone → Change repository visibility → Make private**.

Rien ne se casse en le faisant : le CMS fonctionne avec un dépôt privé, et le déploiement
automatique dispose de 2 000 minutes gratuites par mois sur un dépôt privé, soit largement de
quoi tenir plusieurs centaines de mises en ligne. La seule contrainte publique aurait été
GitHub Pages, que nous n'utilisons pas.

Attention toutefois : ce qui a été public l'a été. Si le contenu de ces documents est sensible,
passer en privé ne suffit pas — il faut aussi considérer qu'ils ont pu être copiés.

### Le dépôt devrait finir sur le compte d'Alice

Il est aujourd'hui sur le compte `VOTRE-COMPTE`. Le brief prévoit que le code et les accès restent
au nom d'Alice : à la livraison, **Settings → General → Transfer ownership** vers son compte, ce
qui conserve tout l'historique. Vous pourrez rester collaborateur pour la maintenance.

### Ce qu'il faut installer sur son poste

Deux logiciels, rien de plus :

- **Git** — [git-scm.com](https://git-scm.com/downloads) ;
- **Node.js version 20 ou supérieure** — [nodejs.org](https://nodejs.org), la version *LTS*.

### Récupérer et lancer le projet

Ouvrez un terminal dans le dossier où vous voulez le projet, puis :

```bash
git clone https://github.com/VOTRE-COMPTE/VOTRE-DEPOT.git
cd VOTRE-DEPOT
npm install
npm run dev
```

Le site s'ouvre sur `http://localhost:4321`. Si `git clone` demande une authentification, c'est
que le dépôt est privé : connectez-vous avec le compte GitHub qui y a accès. GitHub n'accepte plus
le mot de passe du compte — voir « Problèmes courants » plus bas.

### Ce qui ne se clone pas

Un point à ne pas oublier, parce qu'il ne provoque aucune erreur visible : les **secrets de
déploiement** ne sont pas dans le dépôt, par construction. Si vous repartez sur un autre dépôt ou
un autre compte, il faut recréer à la main les quatre secrets FTP, la variable `SITE_URL` et, le
cas échéant, `FTP_PROTOCOLE` (étapes 5.6 à 5.8). Les accès Hostinger et le compte Pages CMS se
reconfigurent également côté services, pas côté code. Le fichier `.env`, lui, n'est pas dans le
dépôt non plus : recopiez `.env.example` et remettez-y le domaine.

---

## Annexe — Brancher le domaine, et les pièges d'un domaine déjà utilisé

**Cette annexe ne concerne pas la mise en place actuelle.** `saucedexister.fr` est un domaine
neuf, acheté chez l'hébergeur lui-même : sa zone DNS est propre et déjà correcte. Gardez ces
pages sous le coude pour le jour où le site changerait de domaine ou d'hébergeur.

Si le domaine a déjà servi ailleurs — Squarespace, Wix, un ancien hébergeur — la zone DNS garde
presque toujours des traces. Ces restes produisent des symptômes déroutants, où le site semble
« bloqué » par l'ancien prestataire alors qu'il n'en est rien.

### Le piège des enregistrements mélangés

C'est le cas le plus courant, et le plus trompeur. La zone se retrouve avec **plusieurs
enregistrements `A`** : ceux de l'ancien hébergeur, jamais supprimés, plus celui du nouveau.

Quand plusieurs `A` coexistent, les navigateurs en choisissent un **au hasard**. Avec quatre
anciennes adresses et une nouvelle, quatre visiteurs sur cinq atterrissent sur l'ancien site — ou
sur une page « site expiré » si l'abonnement a pris fin — et un sur cinq voit le bon site. D'où
l'impression d'un blocage, alors que c'est un tirage au sort.

Deuxième piège fréquent : un `www` qui cumule un `CNAME` vers l'ancien prestataire **et** des
enregistrements `A`. La norme DNS interdit cette combinaison.

### Nettoyer la zone

Vérifiez d'abord **qui gère la zone**, car c'est là qu'il faut intervenir :

```bash
nslookup -type=NS votredomaine.fr
```

Si les serveurs de noms sont ceux de votre hébergeur, tout se règle dans son panneau, sans rien
changer chez le registrar. Ensuite :

1. **Supprimer tous les `A` de l'ancien hébergeur.** N'en garder qu'un : celui du nouveau.
2. Sur `www` : supprimer le `CNAME` résiduel et les `A`, puis créer un unique `CNAME`
   `www` → `votredomaine.fr.` — sans quoi les visiteurs qui tapent « www » auront une erreur.
3. Si vous exploitez un second domaine censé rediriger vers le principal, vérifiez qu'il a bien
   un `A` : un domaine sans `A` ne résout pas du tout, et aucune redirection ne peut s'appliquer.
4. Supprimer les `TXT` de vérification de l'ancien prestataire, devenus inutiles.

Regardez le TTL avant de vous inquiéter d'une propagation lente : il est souvent de quelques
minutes, pas de 48 heures.

### Activer le certificat, dans cet ordre

Le `.htaccess` force HTTPS. Tant que le certificat n'est pas émis, la redirection mène à un
avertissement de sécurité — ce qui ressemble à une panne alors que le site est bon.

L'ordre compte : **nettoyer la zone d'abord, activer le certificat ensuite.** Sa validation passe
par une vérification sur le domaine ; avec les anciennes adresses encore présentes, elle tombe
rarement du bon côté et échoue le plus souvent.

Vérifiez aussi qu'aucun `AAAA` ne subsiste si l'hébergement n'a pas d'IPv6 : un `AAAA` résiduel
fait échouer l'émission du certificat.

### Vérifier sans attendre la propagation

Pour tester le nouveau serveur avant que le DNS soit à jour, forcez l'adresse dans la requête :

```bash
curl -s -o /dev/null -w "%{http_code} %{redirect_url}\n" -H "Host: votredomaine.fr" http://ADRESSE_IP/
```

Un `301` vers `https://votredomaine.fr/` est le bon signe : il prouve que les fichiers sont en
place et que le `.htaccess` est actif. Un `200` accompagné d'une page « Index of / » signale au
contraire que les fichiers ont été déposés dans un sous-dossier — voir l'étape 5.6 sur le réglage
`FTP_DOSSIER`.

---

## Problèmes courants

**Le `push` demande un mot de passe et le refuse.**
GitHub n'accepte plus le mot de passe du compte depuis 2021. Sous Windows, *Git Credential
Manager* ouvre normalement une fenêtre de navigateur : connectez-vous par là. Sinon, créez
un jeton d'accès personnel (**GitHub → Settings → Developer settings → Personal access
tokens**) et utilisez-le à la place du mot de passe.

**Pages CMS affiche « No configuration found ».**
Le fichier `.pages.yml` doit être à la racine du dépôt, sur la branche `main`, et poussé sur
GitHub. Vérifiez qu'il apparaît bien dans la liste des fichiers sur github.com.

**Pages CMS ne voit pas le dépôt.**
L'application GitHub n'a pas été installée sur le bon compte, ou le dépôt n'a pas été coché.
Reprenez l'étape 3.2 : **GitHub → Settings → Applications → Pages CMS → Configure**.

**J'ai modifié un fichier en local et le `push` est refusé.**
Alice a modifié le même fichier entre-temps. Faites `git pull` d'abord : Git fusionne les
deux versions, puis `git push` passe.

**Le déploiement échoue sur l'étape FTP.**
Un secret est mal orthographié, ou le dossier n'est pas le bon. Le journal de l'onglet
**Actions** donne le message exact. Les trois messages ci-dessous couvrent la quasi-totalité
des cas, et chacun désigne un secret différent.

**`getaddrinfo ENOTFOUND` — « The server doesn't seem to exist. Do you have a typo? »**
C'est le secret `FTP_SERVEUR`, et lui seul. Le message veut dire que la valeur a été cherchée
dans l'annuaire DNS et n'y figure pas. Ne vous laissez pas égarer par la phrase qui précède,
« are you sure your server works via FTP or FTPS ? » : elle est affichée à tort, puisque la
connexion n'a même pas été tentée — le nom n'a pas pu être traduit en adresse.

Trois causes, par ordre de fréquence : un `ftp://` collé devant, un `:21` collé derrière, ou une
espace restée au bout lors du copier-coller. Dans les trois cas l'ensemble devient un nom de
machine qui n'existe pas. La valeur attendue est un nom nu : `ftp.saucedexister.fr`.

Comme un secret n'est jamais réaffiché, on ne peut pas relire ce qu'il contient : supprimez-le
et recréez-le en tapant la valeur à la main plutôt qu'en la collant.

**`530 login incorrect`**
Le couple `FTP_UTILISATEUR` / `FTP_MOT_DE_PASSE`. Le plus souvent, c'est le mot de passe du
compte Hostinger ou celui de SSH qui a été mis à la place du mot de passe FTP. Posez un nouveau
mot de passe FTP depuis le hPanel et reportez-le.

**Le déploiement réussit, mais le site répond 404.**
`FTP_DOSSIER`, à coup sûr. Une coche verte prouve que les fichiers ont été déposés, pas qu'ils
l'ont été au bon endroit : le déploiement crée le dossier qu'on lui indique s'il n'existe pas,
sans se demander si le domaine le publie. Les fichiers sont donc bien sur le serveur, dans un
dossier que personne ne sert.

Le piège est que `public_html` désigne deux choses différentes selon d'où l'on regarde. Un
compte FTP peut arriver dans le dossier personnel — auquel cas `/public_html/` est correct — ou
directement *dans* `public_html`, auquel cas la même valeur crée un `public_html/public_html/`.
Et si le domaine est déclaré comme site secondaire, ce que Hostinger fait parfois même pour un
compte à un seul site, le dossier publié est ailleurs :
`/domains/saucedexister.fr/public_html/`.

On ne devine pas, on regarde. **Fichiers → Gestionnaire de fichiers**, et cherchez le dossier
qui contient `index.html` et `_astro`. Le chemin complet s'affiche en haut. Deux cas :

- les fichiers sont dans un `public_html` **imbriqué dans un autre** : mettez `FTP_DOSSIER` à `/` ;
- les fichiers sont dans `public_html` mais le domaine publie
  `domains/saucedexister.fr/public_html` — ce dernier est alors vide, c'est le signe qui ne
  trompe pas : mettez `FTP_DOSSIER` à `/domains/saucedexister.fr/public_html/`.

Un indice permet de confirmer sans rien ouvrir. Si le site renvoie une 404, regardez à quoi
ressemble la page d'erreur : la nôtre porte l'identité du site. Une page d'erreur générique de
l'hébergeur signifie que le `.htaccess` du dépôt n'est pas là où le domaine le cherche, donc que
le dossier publié est vide.

Après correction, relancez le déploiement, puis **videz le cache du CDN** (voir l'entrée
suivante) : sans quoi la 404 continuera d'être servie un moment.

**Le site est corrigé mais l'ancienne page continue de s'afficher.**
Hostinger interpose un CDN devant le serveur, actif par défaut. On le reconnaît aux en-têtes de
la réponse : `Server: hcdn`. Il garde les réponses en mémoire, y compris les erreurs.

Dans le hPanel, rubrique **Performance → CDN**, videz le cache. Pour vérifier sans dépendre de
votre navigateur, interrogez le serveur directement en court-circuitant le CDN — remplacez l'IP
par celle de votre hébergement :

```bash
curl -s -o NUL -w "%{http_code}" --resolve saucedexister.fr:443:82.29.191.19 https://saucedexister.fr/
```

Un `200` ici et une erreur dans le navigateur, c'est le cache du CDN, rien d'autre.

**Le navigateur affiche « too many redirects » (`ERR_TOO_MANY_REDIRECTS`).**
Le site se redirige vers lui-même en boucle. Deux causes, souvent présentes en même temps.

La première est un piège d'hébergement mutualisé. Le TLS est terminé par un proxy en amont :
Apache reçoit du HTTP en clair même quand le visiteur est en HTTPS, donc la règle « forcer
HTTPS » du `.htaccess` se déclenche indéfiniment. Le `.htaccess` livré teste désormais aussi les
en-têtes `X-Forwarded-Proto` et `X-Forwarded-SSL`, ce qui coupe la boucle. Si vous êtes sur une
ancienne version, redéployez.

La seconde est l'absence de certificat. Pour la diagnostiquer :

```bash
curl -skI -L --max-redirs 5 https://votredomaine.fr/ | findstr /R "^HTTP ^[Ll]ocation"
```

Une même `Location` répétée à l'identique confirme la boucle. Et pour voir quel certificat est
réellement présenté, si `openssl` est disponible :

```bash
openssl s_client -connect votredomaine.fr:443 -servername votredomaine.fr < NUL | findstr "subject issuer"
```

Un sujet `CN=localhost` signifie qu'aucun certificat n'a été émis pour le domaine : c'est le
certificat par défaut du serveur. Émettez-le depuis le hPanel, tableau de bord du site,
rubrique **Sécurité → SSL** — c'est l'étape 5.2.

En dépannage, si le certificat ne peut pas être émis tout de suite, commentez les quatre lignes
« Forcer HTTPS » du `.htaccess` et redéployez : le site répondra en HTTP le temps de régler le
certificat, ce qui vaut mieux qu'une page d'erreur.

**Le formulaire de contact ne fonctionne pas en local.**
C'est normal : `npm run dev` n'exécute pas PHP. Utilisez `npm run preview:php` si PHP est
installé, ou testez en ligne.

**Le formulaire annonce « message envoyé », mais rien n'arrive.**
Le fichier `smtp.php` de l'étape 5.4 bis manque, ou son mot de passe est faux. Sans lui, le site
expédie par `mail()`, que l'hébergeur achemine mal — et cette fonction répond « vrai » même
quand le message est jeté, d'où l'absence totale de signal.

Vérifiez d'abord que le fichier est bien **à côté** de `public_html` et non dedans. En cas de
doute sur le mot de passe, le journal d'erreurs du hPanel contient la raison exacte du refus,
enregistrée par le site : cherchez « Formulaire de contact ».

**Le formulaire affiche « L'envoi n'a pas abouti ».**
Là, le serveur a bien refusé quelque chose. Trois causes, dans l'ordre : le fichier `smtp.php`
est absent ou incorrect ; la limite anti-spam est atteinte, huit messages par heure et par
visiteur ; ou la connexion a échoué en cours de route. Pour trancher, interrogez l'adresse
directement — une réponse en JSON donne le motif exact :

```bash
curl -s -H "Accept: application/json" -d "nom=Test" -d "email=test@example.org" \
  -d "message=Message de verification suffisamment long." -d "consentement=oui" \
  https://saucedexister.fr/api/contact.php
```

**Les mails du formulaire arrivent en indésirable.**
SPF et DKIM manquent dans la zone DNS du domaine — voir l'étape 5.4. C'est à régler avant
d'annoncer le site : sans cela, les messages des prescripteurs se perdent.

**L'accueil affiche une liste de fichiers, ou la page de courtoisie de l'hébergeur.**
Une liste de fichiers signifie que le secret `FTP_DOSSIER` désigne le mauvais dossier : chez
Hostinger c'est `/public_html/`. La page de courtoisie signifie qu'un `index.php` de
l'hébergeur est resté dans `public_html` et passe avant notre `index.html` : supprimez-le avec
le gestionnaire de fichiers, comme à l'étape 5.3.

**Le site en ligne annonce le mauvais domaine dans `robots.txt` ou le plan du site.**
La variable `SITE_URL` manque sur GitHub, ou n'a pas été posée dans l'onglet *Variables* mais
dans *Secrets*. Reprenez l'étape 5.7, puis relancez un déploiement : la valeur n'est lue qu'au
moment de la construction.
