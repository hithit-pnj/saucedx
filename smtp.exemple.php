<?php
/**
 * Modèle du fichier de réglages SMTP du formulaire de contact.
 *
 * Ce fichier-ci n'est qu'un exemple : il ne sert à rien tel quel et n'est pas
 * envoyé en ligne. Le vrai fichier se crée à la main sur le serveur, une seule
 * fois, et se nomme « smtp.php ».
 *
 * Où le déposer : un cran AU-DESSUS du dossier publié, à côté de public_html et
 * non dedans. Chez Hostinger, cela donne :
 *
 *     domains/saucedexister.fr/smtp.php          ← ici
 *     domains/saucedexister.fr/public_html/      ← et surtout pas là
 *
 * Deux raisons à cet emplacement. Le mot de passe de la boîte mail n'a rien à
 * faire dans le dépôt Git, qui garde tout son historique même après suppression.
 * Et le déploiement ne peut ni l'écraser ni l'effacer, puisqu'il ne le connaît pas.
 *
 * La messagerie du domaine est chez Google Workspace. Google n'accepte pas le mot
 * de passe du compte pour le SMTP : il faut un « mot de passe d'application », à
 * créer une fois dans le compte Google concerné (myaccount.google.com → Sécurité →
 * Validation en deux étapes, qui doit être activée → Mots de passe des applications).
 * Google affiche seize lettres par groupes de quatre : les espaces ne comptent pas.
 *
 * L'adresse ci-dessous doit être la même que $EXPEDITEUR dans public/api/contact.php
 * et public/api/rendez-vous.php : Google rejette un From différent du compte connecté.
 */

return [
    'hote'         => 'smtp.gmail.com',
    // 465 chiffré de bout en bout, ou 587 si le 465 pose problème.
    'port'         => 465,
    // L'adresse complète, pas seulement la partie avant l'arobase.
    'utilisateur'  => 'alicecamillefrenchy@saucedexister.fr',
    'mot_de_passe' => 'à remplacer par le mot de passe d’application (16 lettres)',
];
