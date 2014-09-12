Introduction
============

Formcreator est un plugin permettant la création de formulaire personalisés simple d'accès aux utilisateurs aboutissants à la création d'un ou plusieurs tickets GLPI.

Fonctionnalités
---------------

1. Accès par menu directe en interface self-service
2. Mise en avant de formulaires en pages d'accueil
3. Accès des formulaires contrôlé : accès public, accès utilisateurs identifiés, accès restreint à certains profils
4. Des formulaires simples et personnalisables
5. Des formulaires organisé par catégories, par entités et par langues.
6. Des questions ouvertes ou fermées, de tout type de présentation : Champs textes, listes, LDAP, fichiers, etc.
7. Organisation des questions par sections. Choix de l'ordre d'affichage.
8. Possibilité de n'afficher une question que selon certains critères (réponse à une autres question)
9. Un contrôle pointu sur les réponses de formulaires : Texte, nombres, taille des champs, e-mail, champs obligatoires, expressions réguliaires, etc.
10. creation d'un ou plusieurs tickets à partir des réponses aux formulaires
11. Ajout de description par champs, par section de questions, par formulaires, par entités et langues.
12. Formatage du/des ticket(s) créé(s) : réponses aux questions à affichés, gabarits de tickets.
13. prévisualisation du formulaire créé directement dans la configuration.

Nouveauté de la version 2.0
---------------------------
- Refonte complete du code source
- Modification de la présentation
- Ajout de champs listes alimentées par annuaire LDAP
- Ajout de champs listes des objets du coeur de GLPI (Utilisateurs, Ordinateurs, Profils, etc.)
- Ciblage des formulaires par profils
- Formulaires publics disponibles sans être connecté à GLPI
- Utilisation des gabarits de tickets pour plus de souplesse et de possibilités
- Prévisualisation des formulaires directement depuis la configuration
- Contrôle multiples sur les réponses aux questions (Ex. : E-mail + obligatoire, Nombre + supérieur à X + inférieur à Y, etc.)
- Nouveaux types de champs :
    - date
    - date & heure
    - description
    - intitulé GLPI
    - liste LDAP
    - liste d'objets du coeur
    - e-mails
    - nombres entiers
    - nombres décimaux
    - champs cachés
    - liste de boutons radios
    - listes à choix multiples

Pour plus d'informations, visitez [la page WIKI](https://github.com/TECLIB/formcreator/wiki)


------------------------------------------------------------------------------------------------------------------------

Introduction
============
Formcreator is a plugin for creating personalized form of easy access outs users to create one or more GLPI tickets.

Features
--------
1. Direct access to forms self-service interface in main menu
2. Highlighting forms in homepages
3. Access to forms controlled: public access, identified user access, restricted access to some profiles
4. Simple and customizable forms
5. Forms organized by categories, entities and languages.
6. Questions of any type of presentation: Textareas, lists, LDAP, files, etc.
7. Questions organised in sections. Choice of the display order.
8. Possibility to display a question based on certain criteria (response to a further question)
9. A sharp control on responses from forms: text, numbers, size of fields, email, mandatory fields, regular expressions, etc.
10. Creation of one or more tickets from form answers
11. Adding a description per fields, per sections, per forms, entities or languages.
12. Formatting the ticket set: answers to questions displayed, tickets templates.
13. Preview form created directly in the configuration.

New in version 2.0
---------------------------
- Source code completly refactor
- Change form display
- Added fields lists supplied by LDAP
- Added fields lists of objects from GLPI (Users, Computers, profiles, etc.)
- Targeting forms by profiles
- Public Forms available without connection to GLPI
- Use tickets templates for more flexibility and features
- Previewing forms directly from the configuration
- Multiple Controls on the answers to questions (eg E-mail + mandatory, Number + greater than X + less than Y, etc.)
- New types of fields:
    - Date
    - Date & Time
    - Description
    - GLPI dropdowns
    - LDAP list
    - List of objects from GLPI
    - E-mail
    - Integers
    - Floating numbers
    - Hidden Fields
    - List of radio buttons
    - Multiple choice lists

For more informations, see the [WIKI](https://github.com/TECLIB/formcreator/wiki)

![Configuration](/screenshot.png "Configuration")
