# **Guide d’administration de l’application Vis mon Métier**

Ce document détaille les étapes nécessaires, dans l'interface d'administration, pour préparer une nouvelle campagne d'immersion.

Les actions doivent être réalisées dans l'ordre suivant :

1. Création des questionnaires (modèle).
2. Création et ouverture de la session.
3. Création des comptes pour les référents partenaires.
4. Effectuer les matchs.

Il revient également plus en détail sur:

5. Les rôles et permissions.
6. Les différents exports et leur niveau de confidentialité.

## **1\. Créer une liste de questions**

Avant de lancer une session, il faut définir les questions qui seront posées aux participants et aux employeurs. C'est ce qu'on appelle un "Modèle de questionnaire".

1. Dans le menu latéral gauche, cliquez sur "Questions" (icône ?).
2. Cliquez sur le bouton "Ajouter un questionnaire" en haut à droite.
3. Remplissez le formulaire :
    * Nom : Donnez un nom clair (ex: *Questionnaire 2024*).
    * Actif : Laissez coché pour que ce modèle soit utilisable.
    * Questions Score : Cette section définit les questions d'intérêt (pour le "matching").
        * Cliquez sur les onglets ou les boutons pour ajouter/modifier des questions.
        * Pour chaque question, définissez le libellé pour le participant ("*Aimez-vous...*") et pour l'employeur ("*Ce métier nécessite...*").
    * Questions Disposition : Cette section définit les questions logistiques (horaires, mobilité, dates...).
        * Vous pouvez configurer le type de réponse (Choix unique, Dates multiples, etc.).
        * *Note importante* : Assurez-vous d'avoir une question de type "choix dates multiples" pour gérer le calendrier.
4. Cliquez sur "Sauvegarder" en bas de page.

## **2\. Créer une session active**

Une fois le modèle de questions prêt, vous devez créer la session temporelle (la période durant laquelle les inscriptions sont ouvertes).

1. Dans le menu latéral, cliquez sur "Sessions" (icône calendrier).
2. Cliquez sur "Créer une session".
3. Remplissez les informations :
    * Nom de la session : (ex: *Session Juin 2025 \- Nantes*).
    * Date début session et date fin session : Définissent la période officielle.
    * Statut de la session :
        * Sélectionnez "ouverte" pour rendre le formulaire accessible immédiatement sur le site public.
        * Sélectionnez *"brouillon"* si vous préparez la session mais ne voulez pas encore recevoir de réponses.
    * Questionnaire à associer : Tapez les premières lettres du modèle créé à l'étape 1 et sélectionnez-le.
4. Cliquez sur "Sauvegarder".

Vérification : Si la session est "ouverte", elle apparaîtra sur votre tableau de bord avec les statistiques en temps réel, et les formulaires publics seront fonctionnels.

### **Clôturer une session terminée**

Une fois la période d'inscription passée, il est impératif de fermer la session pour empêcher de nouvelles réponses via le formulaire public.

1. Allez dans le menu **"Sessions"**.
2. Repérez votre session active et cliquez sur l'icône **Crayon** (Modifier) à droite de la ligne.
3. Dans le champ **Statut de la session**, changez la valeur de "ouverte" à **"fermée"**.
4. Cliquez sur **"Sauvegarder"**.
    * *Conséquence* : Le formulaire public affichera désormais un message indiquant qu'aucune session n'est ouverte. Les données restent accessibles en lecture seule dans le back-office.

## **3\. Créer un Référent avec son compte associé**

La création d'un référent se fait en deux temps : d'abord son compte de connexion (Utilisateur), puis sa fiche d'identité (Référent).

### **Étape A : Créer le compte utilisateur (Login/Mot de passe)**

1. Allez dans le menu "Utilisateur·ices".
2. Cliquez sur "Créer Utilisateur·rice".
3. Remplissez les champs :
    * Email : L'identifiant de connexion du référent (ex: [jean.dupont@mission-locale.fr](mailto:jean.dupont@mission-locale.fr)). *L’adresse n’a pas besoin d’être une “vraie”, elle servira uniquement d’identifiant et ne fera jamais l’objet d’envoi de mail.*
    * Mot de passe : Définissez un mot de passe provisoire (à conserver car il faudra le transmettre au référent).
    * Rôles : Sélectionnez "Référent·e" (Badge bleu). *Veillez à ne surtout pas sélectionner “Administrateur·ice”. Ce rôle doit être réservé aux gestionnaires du projet.*
4. Cliquez sur "Sauvegarder".

### **Étape B : Créer la fiche Référent (Identité)**

Une fois le compte technique créé, il faut lier les informations personnelles.

1. Allez dans le menu "Référent·es".
2. Cliquez sur "Créer Référent·e".
3. Remplissez les informations :
    * Compte utilisateur associé : Recherchez l'email du compte créé à l'Étape A.
        * *Note : Seuls les utilisateurs ayant le rôle "Référent" apparaissent ici.*
    * Prénom et Nom.
    * Email : (Pour contact, généralement le même que le compte utilisateur).
    * Téléphone, Fonction, Structure : Informations utiles pour la gestion.
4. Cliquez sur "Sauvegarder".

Le référent peut désormais se connecter au back-office. Il ne verra que les candidats qui lui sont rattachés ou les entreprises (selon les règles définies), contrairement à vous qui voyez tout en tant qu'Administrateur.

## **4\. Gérer les Matching**

Le module de "Matching" permet de relier un Participant (Volontaire) à un Employeur pour une date donnée, en se basant sur un score de compatibilité calculé automatiquement.

### **Réaliser un Matching (Créer une immersion)**

1. Allez dans le menu **"Questionnaires remplis"**.
2. Cliquez sur l'icône **Œil** (Consulter) d'un questionnaire (Participant ou Employeur).
3. Descendez en bas de la page jusqu'à la section **"Liste des profils les plus compatibles"**.
    * Le tableau affiche les candidats/entreprises triés par score de compatibilité (%).
    * La colonne "Dates" indique les créneaux communs disponibles.
4. **Pour valider une immersion**, cliquez sur le badge vert correspondant à la date souhaitée (ex: `05/06`).
    * *Si le badge est orange/jaune et qu’il n’est pas possible de cliquer, c'est que l'une des deux parties n'est pas disponible ou est déjà "matchée" ce jour-là.*
5. Une notification de succès "L'immersion a été créée avec succès" apparaît et la page se recharge.

### **Annuler un Matching**

Si une immersion a été créée par erreur ou doit être annulée :

1. Ouvrez le questionnaire concerné (via **"Questionnaires remplis"** \> **Œil**).
2. En haut de la page, repérez le bloc vert **"Immersion(s) prévue(s)"**.
3. Cliquez sur le bouton rouge **"Annuler"** à droite de l'immersion concernée.
4. Confirmez l'action si demandé. Le lien est rompu et les deux parties redeviennent disponibles pour cette date.

## **5\. Rôles et Permissions**

L'application distingue trois niveaux d'accès pour les utilisateurs du back-office. Il est important d'attribuer le bon rôle à chaque personne.

### **🔴 Administrateur·rice**

* **Accès** : Total.
* **Fonctions** :
    * Peut créer/modifier/supprimer les Sessions et les Modèles de Questionnaires.
    * Peut gérer tous les comptes Utilisateurs et Référents.
    * Voit l'intégralité des réponses (Participants et Employeurs).
    * Peut réaliser des matchings sur n'importe quel dossier.
* **Pour qui ?** : Les chefs de projet "Vis mon Métier" et l'équipe technique uniquement.

### **🔵 Référent**

* **Accès** : Restreint (Opérationnel).
* **Fonctions** :
    * Ne **PEUT PAS** configurer les sessions ou les questions.
    * Voit **tous** les Employeurs inscrits.
    * Voit **uniquement** les Participants qui lui sont rattachés (ceux ayant sélectionné sa structure lors de l'inscription ou ceux qui lui ont été assignés manuellement).
    * Peut réaliser des matchings (créer des immersions) pour ses propres candidats.
    * Peut modifier les réponses de ses candidats (ex: correction d'une erreur de saisie).
* **Pour qui ?** : Les conseillers Mission Locale, France Travail, ou les enseignants partenaires.

### **⚪ Lecteur**

* **Accès** : Consultation seule.
* **Fonctions** :
    * Peut voir les statistiques globales sur le tableau de bord.
    * Peut consulter les listes de contacts et de questionnaires.
    * Ne peut **rien modifier** (ni créer, ni supprimer, ni matcher).
* **Pour qui ?** : Pour les observateurs qui ont besoin de suivre l'avancement sans intervenir opérationnellement et peuvent vouloir exporter les statistiques.

## **6\. Exports et Confidentialité des Données**

Vous pouvez extraire des données depuis le tableau de bord de la session ou les fiches détaillées (Statistiques, Liste des métiers, Fiches PDF).  
Il est également possible d’accéder aux statistiques d’une session précédente (par exemple de l’année antérieure) même si “fermée”.

### **Point de vigilance sur les données**

* **Données vivantes** : Tant que la campagne (session) n'est pas terminée et passée au statut **"fermée"**, les données exportées peuvent changer (nouvelles inscriptions, modification de disponibilités, etc.). Les exports doivent donc être considérés comme provisoires jusqu'à la clôture.

### **Confidentialité et règles de partage**

Il est crucial de distinguer ce qui peut être diffusé de ce qui doit rester confidentiel :

* **Documents STRICTEMENT CONFIDENTIELS** :
    * Exports de questionnaire participants ou employeurs.
    * *Raison* : Ces documents contiennent des données personnelles sensibles (numéros de téléphone, emails personnels, noms). **Ils ne doivent jamais être envoyés par email ou partagés à des tiers.**
* **Documents PARTAGEABLES (Anonymes)** :
    * L'export **"Liste des métiers"** (Bouton "Exporter les métiers" dans la session).
    * L'export **"Statistiques"** (Bouton "Exporter les statistiques").
    * *Raison* : Ces documents sont anonymisés (secteurs d'activité, graphiques globaux) et peuvent être utilisés pour la communication ou le bilan avec les partenaires.

