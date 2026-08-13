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
   Infomaniak  →  votredomaine.fr
```

Quatre conséquences utiles :

- **Il n'y a rien à sauvegarder.** GitHub garde l'historique complet de chaque texte. Toute
  modification est annulable, même six mois plus tard.
- **Il n'y a rien à mettre à jour en urgence.** Le site en ligne est du HTML : il n'a pas de
  faille à corriger, pas d'extension à surveiller.
- **Alice ne voit jamais Git.** Elle voit des formulaires en français. Git travaille dessous.
- **Rien de tout ça ne coûte d'argent.** GitHub et Pages CMS sont gratuits à cette échelle ;
  seul l'hébergement Infomaniak est payant, et il l'était déjà.

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

- **Les offres** — les cinq pages d'offres
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

Le site en ligne n'existe pas encore (l'hébergement Infomaniak reste à configurer). En
attendant, on récupère la modification sur votre machine :

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

Cette étape suppose que l'hébergement Infomaniak est prêt. Le fichier de configuration
existe déjà : `.github/workflows/deploiement.yml`.

### 5.1 Souscrire la bonne offre, et ne pas se tromper de « Starter »

Attention à un piège de nom chez Infomaniak. Deux produits portent des noms voisins :

| Produit | Prix | PHP | Convient ? |
| --- | --- | --- | --- |
| **Hébergement Web Starter** | offert avec le domaine | **non** | non — le formulaire ne marcherait pas |
| **Hébergement Web** | à partir de ~5,75 € HT/mois | oui | **c'est celui-là** |

Le Starter ne sert que du HTML et du CSS. Notre site tient dans ses 10 Mo (il pèse 0,56 Mo),
mais `contact.php` ne s'exécuterait pas : le formulaire renverrait une erreur à chaque envoi.

Il y a 30 jours d'essai gratuit sur l'offre payante, de quoi tout valider avant de s'engager.

### 5.2 Créer l'adresse mail et signer le domaine

Une adresse mail à votre domaine est **offerte** avec le nom de domaine enregistré chez
Infomaniak, avec stockage illimité. Créez `contact@votredomaine.fr` : c'est la seule
nécessaire. Elle reçoit les messages du formulaire *et* les expédie ; l'adresse du visiteur est
placée en `Reply-To`, donc un simple « Répondre » écrit bien à la personne.

Puis, dans le Manager : **Domaine → SPF/DKIM**, et activez les deux. Cette étape n'est pas
optionnelle : sans elle, les mails du formulaire partent en indésirable, et les messages des
prescripteurs se perdent silencieusement — c'est le pire scénario possible pour ce site.

### 5.3 Récupérer les accès FTP chez Infomaniak

Le nom du serveur n'est **pas** `ftp.votredomaine.fr` — c'est une adresse technique propre à
votre hébergement, et Infomaniak ne l'affiche qu'à un seul endroit :

1. Ouvrez le *Manager* Infomaniak.
2. **Cliquez sur le nom de l'hébergement** (pas sur le domaine — c'est l'erreur classique, et
   la page du domaine ne contient aucune information FTP).
3. Dans le menu **latéral gauche**, cliquez sur **FTP** — libellé **FTP / SSH** sur les offres
   payantes, simplement **FTP** sur le Starter, qui n'a pas de SSH.
4. Le **nom d'hôte est affiché en haut de la page**, avec une icône pour le copier. Il ressemble
   à `xyzb.ftp.infomaniak.com`.

Sur cette même page, bouton **Ajouter** pour créer un compte FTP si aucun n'existe. Notez alors :

- le **serveur** : `xyzb.ftp.infomaniak.com` ;
- l'**identifiant** : de la forme `xyzb_abcdefg` ;
- le **mot de passe** : vous le choisissez, et il n'est **plus jamais réaffiché** ensuite — en cas
  d'oubli il faut en poser un nouveau ;
- le **dossier** du site. Attention, c'est le réglage qui se rate le plus souvent : sur beaucoup
  d'hébergements Infomaniak le compte FTP arrive **déjà** dans la racine du site, et il faut alors
  mettre `/` et non `/web/`. Mettre `/web/` crée dans ce cas un sous-dossier `web` et le site
  répond sur `votredomaine.fr/web/` au lieu de `votredomaine.fr`, avec une simple liste de
  fichiers à l'accueil.

Pour trancher sans deviner : ouvrez le **Web FTP** depuis cette même page et regardez où vous
atterrissez. Si vous voyez déjà un dossier `web/`, alors le bon réglage est `/web/`. Si vous voyez
directement des fichiers de site, c'est `/`. Après le premier déploiement, vérifiez que la racine
du site affiche bien la page d'accueil et non un « Index of / ».

Si le nom d'hôte ne répond pas — typiquement parce que le domaine ne pointe pas encore sur
l'hébergement — utilisez à la place l'**adresse IP** indiquée sur la même page.

Pour vérifier vos identifiants sans installer de logiciel, le bouton **Web FTP** de cette page
ouvre un explorateur de fichiers dans le navigateur.

### 5.4 Les confier à GitHub, sans les écrire dans le code

Un mot de passe ne se met **jamais** dans un fichier du dépôt : tout le dépôt est lisible
par qui y a accès, et l'historique garde tout, même après suppression. GitHub propose un
coffre pour ça.

1. Sur GitHub, ouvrez le dépôt → **Settings** (onglet en haut à droite).
2. Dans la colonne de gauche : **Secrets and variables → Actions**.
3. Cliquez sur **New repository secret**, puis créez ces quatre secrets, un par un :

| Nom exact du secret | Valeur |
| --- | --- |
| `FTP_SERVEUR` | le nom d'hôte, `xyzb.ftp.infomaniak.com` |
| `FTP_UTILISATEUR` | l'identifiant FTP, `xyzb_abcdefg` |
| `FTP_MOT_DE_PASSE` | le mot de passe FTP |
| `FTP_DOSSIER` | le dossier, par exemple `/web/` |

Les noms doivent être **exactement** ceux-là : le fichier de déploiement les appelle par ce
nom. Une fois enregistré, un secret n'est plus jamais affiché, même à vous.

### 5.5 Sur le Starter : basculer le protocole en FTP nu

À ne pas sauter si vous êtes sur le Starter, sans quoi le déploiement échouera : **cette offre
n'accepte que du FTP nu sur le port 21**. Ni FTPS, ni SFTP — le port 2121 est fermé. Or le
déploiement demande du `ftps` par défaut, et se soldera par une erreur de chiffrement.

Au même endroit que les secrets — **Settings → Secrets and variables → Actions** — mais dans
l'onglet **Variables** et non *Secrets*, cliquez sur **New repository variable** :

| Nom | Valeur |
| --- | --- |
| `FTP_PROTOCOLE` | `ftp` |

**À supprimer au passage sur l'offre payante** : sans cette variable, le déploiement repasse
tout seul en `ftps` chiffré.

Conséquence à assumer le temps du Starter : le mot de passe FTP circule **en clair** sur le
réseau. Créez donc un compte FTP dédié au déploiement, restreint au dossier du site, et changez
son mot de passe le jour du passage à l'offre payante.

### 5.6 Lancer le premier déploiement

1. Sur GitHub, onglet **Actions**.
2. Dans la colonne de gauche, cliquez sur **Mise en ligne**.
3. Bouton **Run workflow** → **Run workflow**.
4. Le déploiement s'affiche et se déroule en deux à trois minutes. Une coche verte signifie
   que c'est en ligne ; une croix rouge s'ouvre sur le journal, qui indique l'étape fautive.

Ensuite, plus rien à faire : chaque enregistrement dans le CMS déclenche ce déploiement.

### 5.7 Ce qui ne marchera pas sur le Starter

Le Starter est conçu pour une page de courtoisie, pas pour ce site. Il permet de tout valider —
le CMS, le dépôt, le déploiement, l'apparence, la navigation — mais trois choses resteront
cassées, et il vaut mieux le savoir avant de s'inquiéter :

- **Le formulaire de contact.** Le Starter n'exécute pas PHP : `contact.php` ne tournera pas et
  l'envoi échouera. Ne le faites pas essayer à Alice tant que l'hébergement n'a pas changé, elle
  croirait à un défaut du site.
- **Le `.htaccess`**, donc peut-être la page 404 sur mesure, la redirection du `.com` vers le
  `.fr` et les en-têtes de sécurité. À vérifier au cas par cas.
- **Le trafic**, plafonné à 1 Go par mois : confortable pour des essais, juste pour une mise en
  ligne réelle.

L'espace disque, lui, ne pose aucun problème : le site pèse 0,56 Mo pour 10 Mo offerts.

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
  bon escient : ils forment la cohérence de la collection.

**Ce qu'elle ne peut pas casser**, volontairement :

- renommer ou supprimer une page d'offre (le nom du fichier fait l'adresse de la page) ;
- créer ou supprimer une page légale ;
- supprimer les réglages du site.

**Une invitation à ajouter à son compte.** Alice a besoin d'un compte GitHub gratuit, puis
d'être invitée sur le dépôt : **Settings → Collaborators → Add people**, en rôle **Write**.

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
un autre compte, il faut recréer à la main les quatre secrets FTP et, le cas échéant, la variable
`FTP_PROTOCOLE` (étapes 5.4 et 5.5). Les accès Infomaniak et le compte Pages CMS se reconfigurent
également côté services, pas côté code.

---

## Annexe — Brancher le domaine, et les pièges d'un domaine déjà utilisé

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
contraire que les fichiers ont été déposés dans un sous-dossier — voir l'étape 5.3 sur le réglage
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
**Actions** donne le message exact.

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
certificat par défaut d'Apache. Activez Let's Encrypt dans le Manager Infomaniak, sur la page de
l'hébergement, rubrique **SSL**. Vérifiez au préalable qu'aucun enregistrement `AAAA` ne traîne
dans la zone DNS, car l'offre Starter n'a pas d'IPv6 et un `AAAA` résiduel fait échouer l'émission.

En dépannage, si le certificat ne peut pas être émis tout de suite, commentez les quatre lignes
« Forcer HTTPS » du `.htaccess` et redéployez : le site répondra en HTTP le temps de régler le
certificat, ce qui vaut mieux qu'une page d'erreur.

**Le formulaire de contact ne fonctionne pas en local.**
C'est normal : `npm run dev` n'exécute pas PHP. Utilisez `npm run preview:php` si PHP est
installé, ou testez en ligne.

**Les mails du formulaire arrivent en indésirable.**
SPF et DKIM ne sont pas activés sur le domaine chez Infomaniak. C'est à faire avant
d'annoncer le site : sans cela, les messages des prescripteurs se perdent.
