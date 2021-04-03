<?php

//init const
define('MISSING_TEAM', 'Vous avez oublié de spécifier une ou des équipes');
define('NO_TEAM_YET', 'Il n’y a pas d’équipe à lister.');
define('MISSING_FILE', 'le fichier text est absent');
define('FILE_PATH', 'teams.txt');

//init variables
$errors = []; //false si l'arrray est vide
$teams = [];

//Récupération des données du fichier
if (!is_file(FILE_PATH)) {
    $errors[] = MISSING_FILE;
} else {
    // Exécution du code
    $teams = file(FILE_PATH, FILE_IGNORE_NEW_LINES);
    //Test de la méthode du serveur
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        if ($_POST['action'] === 'add') {
            //AJOUT
            //validation : team-name est une chaine ? OU utilisation du bouton supprimé mais avec l'action add?
            $tn = $_POST['team-name'] ?? '';
            if (is_string($tn)) {
                $teamName = trim($tn); //trim supprime les caractères en trop avant et après la chaîne.
            }
            if ($teamName) {
                //on ajoute dans le tableau les nouvelles équipes.
                $teams[] = $teamName;
            }
        } elseif ($_POST['action'] === 'delete') {
            //SUPPRESSION
            $tns = $_POST['team-name'] ??  [];
            if (is_array($tns)) {
                $teamsNames = $tns; //si team-name existe sinon on soustrait un array vide
                //on récupére les teams cochées, ensuite on fait la DIFFÉRENCE entre ce tableau et le tableau existant
                $teams = array_diff($teams, $teamsNames);
            }
        }

        //Suppression du contenu du fichier texte et ajout du contenu de $team avec un retour à la ligne en plus
        $transformedTeams = array_map(fn ($team) => $team . PHP_EOL, $teams); //PHP_EOL est un caractère de fin de ligne, c'est une constante.
        file_put_contents(FILE_PATH, $transformedTeams);
    }
}
$team = array_map(fn ($team) => filter_var($team, FILTER_SANITIZE_FULL_SPECIAL_CHARS), $teams)
?>


<!-- TEMPLATE D'AFFICHAGE -->
<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
    <title>Mes équipes</title>
</head>

<body>
    <main class="container">
        <h1>Mes équipes</h1>

        <!-- Affichage des erreurs -->
        <?php if (count($errors)) : ?>
            <div class="alert alert-warning">
                <ul class="list-group">
                    <?php foreach ($errors as $error) : ?>
                        <li class="list-group-item"><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else : ?>

            <!-- Affichage des équipes -->
            <?php if ($teams) : ?>
                <ul class="list-group">
                    <?php foreach ($teams as $team) : ?>
                        <li class="list-group-item"><?= $team ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <div class="alert alert-warning">
                    <p><?= NO_TEAM_YET ?></p>
                </div>
            <?php endif; ?>

            <!-- Ajout des équipes -->
            <section class="mt-5">
                <h2>Ajout d’une équipe</h2>
                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
                    <label class="form-label" for="team-name">Nom de l’équipe</label>
                    <input class="form-control" type="text" name="team-name" id="team-name" autofocus>
                    <br>
                    <button class="btn btn-primary form-control-sm mt-3" type="submit">Ajouter l’équipe
                    </button>
                    <input type="hidden" name="action" value="add">
                </form>
            </section>

            <!-- Suppression d'équipe -->
            <?php if ($teams) : ?>
                <section class="mt-5">
                    <h2>Suppression d’une ou de plusieurs équipes</h2>
                    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">

                        <!-- liste des équipes -->
                        <ul class="list-group">
                            <?php foreach ($teams as $team) : ?>
                                <li class="form-check list-group-item">
                                    <!-- le name='team-name[]' va créer un array à condition que dans l'ajout, le name soit 'team-name'  -->
                                    <input class="form-check-input" type="checkbox" id="<?= $team ?>" name="team-name[]" value="<?= $team ?>">
                                    <label class="form-check-label" for="<?= $team ?>"><?= $team ?></label>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <button class="btn btn-primary form-control-sm mt-3" type="submit">Supprimer la ou les équipes sélectionné(es)</button>
                        <input type="hidden" name="action" value="delete">
                    </form>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>

</html>