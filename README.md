Fonctionnement général
======================

Le plugin FormCreator permet la création de formulaire "simplifiés", principalement destinés aux utilisateurs lambda, aboutissants à la création d'un ou plusieurs tickets GLPI.


Roadmap
=======

V 2.0
-----
- Installation du plugin
     - Créer une valeur d'intitulé pour "Source de la demande" égale à "Formulaire"
- Import / migration à partir de la version actuelle de formcreator
- Menu au 1er niveau de l'interface simplifiée
- Configuration du plugin
     - Catégories de formulaire : Intitulé
     - Présentation : Texte mis en forme qui viendra s'afficher au dessus de la liste des formulaires
- Création de formulaires :
     - Droit d'accès par entités / récursif
     - Droit d'accès public : Formulaire accessible sur une page web publique via une URL sans authentification.
         - Si identifiés uniquement : Droit d'accès par profil (liste des profils avec cases à cocher), Tous par défaut.
     - Champs :
         - Titre : Afficher dans la liste des formulaires et comme titre de la page sur le formulaire (obligatoire)
         - Description courte :  Affichée dans la liste des formulaires (une ligne)
         - Description longue : Texte long mis en forme affiché sur la page du formulaire en haut de page juste après le titre
         - Catégorie : Catégorie du formulaire
         - Actif : Oui / Non (non par défaut, le temps de créer le formulaire)
         - Langue : Liste des langues (la langue de l'utilisateur courant sélectionnée par défaut)
         - Accès direct en Interface simplifiée (case à cocher, non par défaut)
- Cible : Ticket (PDF ou E-mail dans les versions suivantes)
- Si ticket : Afficher champs : titre, description (avec TAG des questions + FULL_FORM), gabarit de ticket.
- Si ticket : La source de la demande est automatiquement remplie par la valeur "Formulaire" créée à l'installation (valeur écrasée par le gabarit de ticket si sa valeur est définie)
- Le ticket créé doit être dans l'entité courante de l'utilisateur et non pas l'entité du formulaire.
- Ajout de sections de questions aux formulaires
    - Les sections peuvent êtres réorganisées. L'ordre est modifiable par des flèches permettant de monter ou descendre les sections
- Ajout de questions aux sections des formulaires
    - Les questions peuvent êtres réorganisées. L'ordre est modifiable par des flèches permettant de monter ou descendre les questions
    - Champs :
        - Intitulé : texte de la question (une ligne, obligatoire)
        - Type :
            - Texte, zone de texte (texte brut) : champs de validation facultatifs : taille max, taille min, validation supplémentaire regex
            - Description (zone de texte mise en forme HTML), pour information de l'utilisateur (pas de saisie)
            - nombre : champs de validation facultatifs : valeur max, valeur min, entier / flottant, validation supplémentaire regex
            - date : Affichage d'un calendrier standard GLPI (localisé)
            - e-mail
            - intitulés : ajouter une valeur vide au début ? Valeur sélectionnée par défaut ?
            - cases à cocher : champs de validation facultatifs : Nombre de sélection max, Nombre de sélection min (si champ obligatoire = oui, min > 0 par défaut)
            - boutons radios
            - fichier
              Liste LDAP : Liste déroulante alimentée par un annuaire LDAP activé dans GLPI. Champs de validation facultatifs : ajouter une valeur vide au début
        - Champ obligatoire : indiqué par une étoile rouge ou grise sur la liste des questions
        - Affichée : Toujours / Si la valeur du champ XXX  est égale|différente|supérieure|inférieure à YYY. (Où XXX = liste des questions et YYY saisie manuelle d'une valeur)
        - Description : Affichée par infobulle au survol d'une icone d'aide (?)
- Ajouter la possibilité de rendre un champ obligatoire en un clic (ajax) à partir de la page du formulaire (liste des questions)
- Ajouter un onglet de prévisualisation du formulaire
- Rendre disponible un formulaire pour toutes les langues.
- L'ensemble des textes seront traduit avec gettext.


V 2.1
-----
- Afficher la liste des champs de base de données disponibles dans les options avancées des questions tickets (proposer un matching des champs / questions)
- Pouvoir remplir un champ de Fields avec une donnée d'un formulaire
- Cible de formulaire : Ticket
- Si ticket : Ajouter la possibilité de créer plusieurs tâches. Pour chaque tâche, on aura les champs : titre, description (avec TAG), catégorie, statut, privée, assignée à.
- Si ticket : Aucun  champs cible par défaut (saisie manuelle par TAG dans la description cible (ticket)), Champ texte permettant la saisie d'un champ de BDD
- Ajouter un contrôle javascript des champs de formulaires
- Ajouter les "cibles" de formulaires (utilisateurs, groupes, entités, etc. Reprendre fonctionnement des notes et KB)


Non planifiées
--------------
- Amélioration du changement d'ordre des sections et questions par drag & drop ( => A faire sur la version GLPI 0.85 avec jQuery, déjà fait sur Davfi)
- Ajouter sur les options avancées des questions une liste des champs disponibles (standard + Fields)
- Avoir la possibilité de cloner un formulaire
- Cible de formulaire : Ticket, PDF ou e-mail
    - Si PDF : Stockage sous forme de Document ? Envoi par mail ? Définir le répertoire de stockage dans la config du plugin ou du formulaire ?
    - Si E-mail : Ajouter les champs : Objet, contenu (avec TAGS), destinataires (Double liste similaire aux notifications)
- Ajouter la possibilité de choisir plusieurs mises en page :
    - labels à gauche
    - labels au dessus
    - Labels en "placeholder"
-  ajouter dans la configuration des tickets cibles une option permettant de définir l'entité où sera créé le ticket ???
