# language: fr
Fonctionnalité: Tester l'injection du service dans un kernel de test

Scénario: Instancier un kernel et vérifier la présence du service Sprinter
    Etant donné que je crée un nouveau kernel de test
    Et              je configure le kernel avec le fichier "config/good.php"
    Et              je boot le kernel
    Alors           ca devrait s'être bien déroulé
    Et              le service "sprinter.sprinter_service" devrait exister
    Et              les paramêtres de mon application devraient être :
    """
    {
        "application_name": "Super appli pour sprinter",
        "version": "1.4.2",
        "sprinter.default.routing_key": "sprinter.lefranc"
    }
    """
    Et              je n'ai plus besoin du kernel de test
    Alors           ca devrait s'être bien déroulé

Scénario: Instancier un kernel sans les paramètres rabbitMQ
    Etant donné que je crée un nouveau kernel de test
    Et              je configure le kernel avec le fichier "config/no_rabbit.php"
    Et              je boot le kernel
    Alors           ca devrait s'être bien déroulé
    Et              le service "sprinter.sprinter_service" devrait exister
    Et              les paramêtres de mon application devraient être :
    """
    {
        "application_name": "Super appli pour sprinter",
        "version": "1.4.2",
        "sprinter.default.routing_key": "sprinter.lefranc"
    }
    """
    Et              je n'ai plus besoin du kernel de test
    Alors           ca devrait s'être bien déroulé
