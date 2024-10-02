### FE : L’internaute aura les options suivantes :

1. **Oui**
   En cliquant sur « Oui », le formulaire s’envoie et le message « Votre avis a été envoyé. » s’affiche.

    Après l’envoi de formulaire :
    - Un message « Votre message a été bien envoyé, Merci de nous aider à améliorer Québec.ca. » sera affiché
    - Un champ texte (Obligatoire) : « Voulez-vous ajouter un commentaire »
    - Un Bouton « Soumettre » pour envoyer le commentaire

   Après l’envoi de deuxième formulaire qui contient le commentaire de l’internaute, et au niveau de la BD, il faut associer le commentaire envoyé par l’utilisateur à sa première réponse « Oui » envoyé avant.
   Est-ce qu’il y aura un message après le deuxième envoi ?

2. **Non**
   Si le client clique sur « Non », le formulaire affiche deux champs obligatoires :
    - Un champ radio (Obligatoire) : Pourquoi l’information n’a pas été utile
    - Un champ texte (Obligatoire) : Veuillez ajouter des précisions

   Après l’envoi de formulaire, le message « Votre avis a été envoyé. Merci de nous aider à améliorer Québec.ca » sera affiché.

3. **Signaler un problème**
   Si le client clique sur « Signaler un problème », le formulaire affiche deux champs :
    - Quel est le problème : Un champ obligatoire « Radio »
    - Précisez la nature du problème : Un champ texte obligatoire

   Après l’envoi de formulaire, le message « Votre avis a été envoyé. Merci de nous aider à améliorer Québec.ca » sera affiché.

### BE : L’Edimestre aura 3 sous-modules :

#### Module « Problèmes »
- Module « Problème » (nouveau ?) + (Export CSV ?)
- Dans le module « Problèmes », les enregistrements auront un bouton « Masquer » pour masquer les problèmes traités.
- Est-ce que les « Problème » a une fonctionnalité d’export, si oui, est-ce qu’on aura besoin d’exporter les « Problème » dans le module « Commentaires » aussi ?
- Les colonnes affichées en BE :
    - Id de la page
    - Titre de la page
    - Date et heure
    - Raison avec le numéro de l’option sélectionnée
    - Commentaire

#### Module « Commentaires »
- Une corbeille d’effacement en BE pour supprimer des commentaires, cela va déclencher un re-calcul de taux de satisfaction (Est-ce que tous les commentaires peuvent être supprimés par ce bouton (utile/non utile) ?)
- Afficher les utilisateurs qui ont supprimé des lignes de commentaires (Un filtre qui permet d’afficher les commentaires supprimés avec le nom d’utilisateur qu’il a supprimé ?)
- Les colonnes affichées en BE :
    - Id de la page
    - Titre de la page
    - Date et heure
    - Raison avec le numéro de l’option sélectionnée
    - Commentaire
    - Utile (Oui / Non) (Identique ?)
    - Une colonne pour le bouton « Retirer »
    - Une colonne « Nom utilisateur » si on affiche les commentaires retirés
- **Export CSV**
    - Les réponses obtenues dans « Signaler un problème » ne sont pas comptabilisées dans le taux de satisfaction
    - Les colonnes à exporter :
        - Id de la page
        - Titre de la page
        - Date et heure
        - Raison avec le numéro de l’option sélectionnée
        - Commentaire
        - URL (On a eu un besoin récent pour ajouter l’URL de la page dans l’export)
        - Utile (Oui / Non) (Identique ?)
        - Une colonne « Nom utilisateur » si on affiche les commentaires retirés ?
    - Pour avoir du style sur le fichier exporté, il doit être en format XLS et non pas CSV (on exporte un fichier de format CSV pour le moment).

#### Module « Statistiques »
- Les colonnes affichées en BE :
    - Id de la page
    - Titre de la page
    - Total positif
    - Total négatif
    - Total
    - Moyenne
    - Problème (Le nombre de problèmes signalés n’est pas comptabilisé dans la moyenne affichée)
