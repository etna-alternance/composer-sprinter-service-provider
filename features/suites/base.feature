# language: fr
Fonctionnalité: Tester les différentes fonctions du service

Scénario: Récupérer le service Sprinter
    Etant donné que je veux récupérer la routing_key par defaut
    Alors           ca devrait s'être bien déroulé

@Sprinter
Scénario: Lancer un job Sprinter sur la routing_key par défaut
    Etant donné que je veux envoyer le template "remise_mot_de_passe.docx" avec le student "student.php"
    Alors           le producer "SPrinter" devrait avoir publié un message dans la queue de la routing_key par defaut
    Et              ca devrait s'être bien déroulé

@Sprinter
Scénario: Lancer un job Sprinter sur la routing_key par défaut avec des data vides
    Etant donné que je veux envoyer le template "remise_mot_de_passe.docx" avec le student "empty_student.php"
    Alors           ca ne devrait pas s'être bien déroulé
