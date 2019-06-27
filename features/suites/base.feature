# language: fr
Fonctionnalité: Tester les différentes fonctions du service

Scénario: Récupérer le service Sprinter
    Etant donné que je veux récupérer la routing_key par defaut
    Alors           ca devrait s'être bien déroulé

@Sprinter
Scénario: Lancer un job Sprinter sur la routing_key par défaut
    Etant donné que je veux envoyer le template "remise_mot_de_passe.docx" avec le student "student.php"
    Alors           le producer "SPrinter" devrait avoir publié un message dans la queue "sprinter.lefran_f" avec le corps contenu dans "sprinter_body.json"
    Et              ca devrait s'être bien déroulé

@Sprinter
Scénario: Lancer un job Sprinter sur la routing_key par défaut avec des data vides
    Etant donné que je veux envoyer le template "remise_mot_de_passe.docx" avec le student "empty_student.php"
    Alors           ca ne devrait pas s'être bien déroulé

@Sprinter
Scénario: Lancer un job Sprinter sur la routing_key par défaut avec un print_flag impossible a encoder en json
    Etant donné que je veux envoyer le template "remise_mot_de_passe.docx" avec le student "student.php" et un print_flag moisi
    Et              ca ne devrait pas s'être bien déroulé
    Et              l'exception devrait avoir comme message "Encoding message to producer failed"
