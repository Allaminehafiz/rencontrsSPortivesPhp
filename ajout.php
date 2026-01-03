<?php
require_once 'config.php';

// 1. Récupération des infos du cookie si l'utilisateur est déjà venu (Cahier des charges point 18)
$email_cookie = isset($_COOKIE['user_email']) ? $_COOKIE['user_email'] : '';
$nom = $prenom = $depart = "";

if ($email_cookie != "") {
    $stmt = $pdo->prepare("SELECT * FROM personne WHERE mail = ?");
    $stmt->execute([$email_cookie]);
    $user = $stmt->fetch();
    if ($user) {
        $nom = $user['nom'];
        $prenom = $user['prenom'];
        $depart = $user['depart'];
    }
}

// 2. Logique pour AJOUTER UN NOUVEAU SPORT (Cahier des charges point 43)
if (isset($_POST['add_sport_btn']) && !empty($_POST['nouveau_sport'])) {
    $nouveau_sport = $_POST['nouveau_sport'];
    $ins = $pdo->prepare("INSERT INTO sport (design) VALUES (?)");
    $ins->execute([$nouveau_sport]);
    header("Location: ajout.php"); // Redirection pour mettre à jour la liste
    exit();
}

// 3. Logique d'INSCRIPTION FINALE
if (isset($_POST['inscription_btn'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $depart = $_POST['depart'];
    $email = $_POST['email'];
    $id_sport = $_POST['sport_id'];
    $niveau = $_POST['niveau'];

    // Vérifier si la personne existe déjà
    $check = $pdo->prepare("SELECT id_personne FROM personne WHERE mail = ?");
    $check->execute([$email]);
    $personne = $check->fetch();

    if (!$personne) {
        // Insertion nouvelle personne
        $ins_pers = $pdo->prepare("INSERT INTO personne (nom, prenom, depart, mail) VALUES (?, ?, ?, ?)");
        $ins_pers->execute([$nom, $prenom, $depart, $email]);
        $id_personne = $pdo->lastInsertId();
    } else {
        $id_personne = $personne['id_personne'];
    }

    // Insertion dans la table pratique (Cahier des charges point 68)
    $ins_pratique = $pdo->prepare("INSERT IGNORE INTO pratique (id_personne, id_sport, niveau) VALUES (?, ?, ?)");
    $ins_pratique->execute([$id_personne, $id_sport, $niveau]);

    echo "<script>alert('Inscription réussie ! Identifiant généré : $id_personne');</script>";
}

// 4. Récupérer les sports pour la liste déroulante
$liste_sports = $pdo->query("SELECT * FROM sport")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - ENASTIC</title>
</head>
<body>
    <h1>Page d'Inscription</h1>
    <a href="index.php">Retour à l'accueil</a> [cite: 46]
    <hr>

    <form method="post" action="ajout.php">
        <h3>Vos coordonnées</h3>
        <input type="text" name="nom" placeholder="Nom" value="<?php echo $nom; ?>" required> [cite: 38]
        <input type="text" name="prenom" placeholder="Prénom" value="<?php echo $prenom; ?>" required>
        <input type="text" name="depart" placeholder="Département" value="<?php echo $depart; ?>" required>
        <input type="email" name="email" placeholder="E-mail" value="<?php echo $email_cookie; ?>" required>

        <h3>Choix du sport</h3>
        <select name="sport_id" required> [cite: 39]
            <?php foreach ($liste_sports as $s): ?>
                <option value="<?php echo $s['id_sport']; ?>"><?php echo $s['design']; ?></option>
            <?php endforeach; ?>
        </select>

        <select name="niveau" required> [cite: 41]
            <option value="débutant">Débutant</option> [cite: 42]
            <option value="confirmé">Confirmé</option>
            <option value="pro">Pro</option>
            <option value="supporter">Supporter</option>
        </select>

        <br><br>
        <button type="submit" name="inscription_btn">S'enregistrer</button>
        <button type="reset">Réinitialiser</button> [cite: 45]
    </form>

    <hr>
    <h3>Le sport n'est pas dans la liste ?</h3> [cite: 43]
    <form method="post" action="ajout.php">
        <input type="text" name="nouveau_sport" placeholder="Nom du nouveau sport">
        <button type="submit" name="add_sport_btn">Ajouter ce sport</button>
    </form>
</body>
</html>