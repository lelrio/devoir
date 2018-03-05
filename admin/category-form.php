<?php
require_once '../tools/common.php';

if(!isset($_SESSION['is_admin']) OR $_SESSION['is_admin'] == 0){
	header('location:../index.php');
	exit;
}

//Si $_POST['save'] existe, cela signifie que c'est un ajout d'une catégorie
if(isset($_POST['save'])){
    $query = $db->prepare('INSERT INTO category (name, description, image) VALUES (?, ?, ?)');
    $newCategory = $query->execute(
		[
			$_POST['name'],
			$_POST['description'],
            $_POST['image'],
		]
    );

    //redirection après enregistrement
//si $newArticle alors l'enregistrement a fonctionné
    if($newCategory){

//upload de l'image si image envoyée via le formulaire
        if(isset($_FILES['image'])){

//tableau des extentions que l'on accepte d'uploader
            $allowed_extensions = array( 'jpg' , 'jpeg' , 'gif' , 'png' );
//extension dufichier envoyé via le formulaire
            $my_file_extension = pathinfo( $_FILES['image']['name'] , PATHINFO_EXTENSION);

//si l'extension du fichier envoyé est présente dans le tableau des extensions acceptées
            if ( in_array($my_file_extension , $allowed_extensions) ){

//je génrère une chaîne de caractères aléatoires qui servira de nom de fichier
//le but étant de ne pas écraser un éventuel fichier ayant le même nom déjà sur le serveur
                $new_file_name = md5(rand());

//destination du fichier sur le serveur (chemin + nom complet avec extension)
                $destination = '../img/category/' . $new_file_name . '.' . $my_file_extension;

//déplacement du fichier à partir du dossier temporaire du serveur vers sa destination
                $result = move_uploaded_file( $_FILES['image']['tmp_name'], $destination);

//on récupère l'id du dernier enregistrement en base de données (ici l'article inséré ci-dessus)
                $lastInsertedCategoryId = $db->lastInsertId();

//mise à jour de l'article enregistré ci-dessus avec le nom du fichier image qui lui sera associé
                $query = $db->prepare('UPDATE article SET
image = :image
WHERE id = :id'
                );

                $resultUpdateImage = $query->execute(
                    [
                        'image' => $new_file_name . '.' . $my_file_extension,
                        'id' => $lastInsertedCategoryId
                    ]
                );
            }
        }
//redirection après enregistrement
        header('location:article-list.php');
        exit;
    }
    else{ //si pas $newArticle => enregistrement échoué => générer un message pour l'administrateur à afficher plus bas
        $message = "Impossible d'enregistrer le nouvel article...";
    }
//Si $_POST['update'] existe, cela signifie que c'est une mise à jour de catégorie
    if(isset($_POST['update'])){

        $query = $db->prepare('UPDATE category SET
		name = :name,
		description = :description
		image = :image
		WHERE id = :id'
        );

        //données du formulaire
        $resultCategory = $query->execute(
            [
                'description' => $_POST['description'],
                'name' => $_POST['name'],
                'id' => $_POST['id'],
                'image' => $_POST['image']
            ]
        );

        if($resultCategory){
            header('location:category-list.php');
            exit;
        }
        else{
            $message = 'Erreur.';
        }
    }

//si on modifie une catégorie, on doit séléctionner la catégorie en question (id envoyé dans URL) pour pré-remplir le formulaire plus bas
    if(isset($_GET['category_id']) && isset($_GET['action']) && $_GET['action'] == 'edit'){

        $query = $db->prepare('SELECT * FROM category WHERE id = ?');
        $query->execute(array($_GET['category_id']));
        //$category contiendra les informations de la catégorie dont l'id a été envoyé en paramètre d'URL
        $category = $query->fetch();
    }
}
?>


<!DOCTYPE html>
<html>
	<head>

		<title>Administration des catégories - Mon premier blog !</title>

		<?php require 'partials/head_assets.php'; ?>

	</head>
	<body class="index-body">
		<div class="container-fluid">

			<?php require 'partials/header.php'; ?>

			<div class="row my-3 index-content">

				<?php require 'partials/nav.php'; ?>

				<section class="col-9">
					<header class="pb-3">
						<!-- Si $category existe, on affiche "Modifier" SINON on affiche "Ajouter" -->
						<h4><?php if(isset($user)): ?>Modifier<?php else: ?>Ajouter<?php endif; ?> une catégorie</h4>
					</header>

					<?php if(isset($message)): //si un message a été généré plus haut, l'afficher ?>
					<div class="bg-danger text-white">
						<?php echo $message; ?>
					</div>
					<?php endif; ?>
					
					<!-- Si $category existe, chaque champ du formulaire sera pré-remplit avec les informations de la catégorie -->
					
					<form action="category-form.php" method="post">
						<div class="form-group">
							<label for="name">Nom :</label>
							<input class="form-control" <?php if(isset($category)): ?>value="<?php echo $category['name']?>"<?php endif; ?> type="text" placeholder="Nom" name="name" id="name" />
						</div>
						<div class="form-group">
							<label for="description">Description : </label>
							<input class="form-control" <?php if(isset($category)): ?>value="<?php echo $category['description']?>"<?php endif; ?> type="text" placeholder="Description" name="description" id="description" />
						</div>
						
						<div class="text-right">
							<!-- Si $category existe, on affiche un lien de mise à jour -->
							<?php if(isset($category)): ?>
							<input class="btn btn-success" type="submit" name="update" value="Mettre à jour" />
							<!-- Sinon on afficher un lien d'enregistrement d'une nouvelle catégorie -->
							<?php else: ?>
							<input class="btn btn-success" type="submit" name="save" value="Enregistrer" />
							<?php endif; ?>
						</div>

						<!-- Si $category existe, on ajoute un champ caché contenant l'id de la catégorie à modifier pour la requête UPDATE -->
						<?php if(isset($category)): ?>
						<input type="hidden" name="id" value="<?php echo $category['id']?>" />
						<?php endif; ?>

					</form>
				</section>
			</div>

		</div>
	</body>
</html>
