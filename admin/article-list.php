<?php

require_once '../tools/common.php';

if(!isset($_SESSION['is_admin']) OR $_SESSION['is_admin'] == 0){
	header('location:../index.php');
	exit;
}

//supprimer l'article dont l'ID est envoyé en paramètre URL
if(isset($_GET['article_id']) && isset($_GET['action']) && $_GET['action'] == 'delete'){

	$query = $db->prepare('DELETE FROM article WHERE id = ?');
	$result = $query->execute(
		[
			$_GET['article_id']
		]
	);
	//générer un message à afficher plus bas pour l'administrateur
	if($result){
		$message = "Suppression efféctuée.";
	}
	else{
		$message = "Impossible de supprimer la séléction.";
	}
}

//séléctionner tous les articles pour affichage de la liste
$query = $db->query('SELECT * FROM article');
$articles = $query->fetchall();
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Administration des articles - Mon premier blog !</title>
		<?php require 'partials/head_assets.php'; ?>
	</head>
	<body class="index-body">
		<div class="container-fluid">

			<?php require 'partials/header.php'; ?>

			<div class="row my-3 index-content">

				<?php require 'partials/nav.php'; ?>

				<section class="col-9">
					<header class="pb-4 d-flex justify-content-between">
						<h4>Liste des articles</h4>
						<a class="btn btn-primary" href="article-form.php">Ajouter un article</a>
					</header>

					<?php if(isset($message)): //si un message a été généré plus haut, l'afficher ?>
					<div class="bg-success text-white p-2 mb-4">
						<?php echo $message; ?>
					</div>
					<?php endif; ?>

					<table class="table table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th>Titre</th>
								<th>Publié</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>

							<?php if($articles): ?>
							<?php foreach($articles as $article): ?>

							<tr>
								<!-- htmlentities sert à écrire les balises html sans les interpréter -->
								<th><?php echo htmlentities($article['id']); ?></th>
								<td><?php echo htmlentities($article['title']); ?></td>
								<td>
									<?php if($article['is_published'] == 1): ?>
									Oui
									<?php else: ?>
									Non
									<?php endif; ?>
								</td>
								<td>
									<a href="article-form.php?article_id=<?php echo $article['id']; ?>&action=edit" class="btn btn-warning">Modifier</a>
									<a onclick="return confirm('Are you sure?')" href="article-list.php?article_id=<?php echo $article['id']; ?>&action=delete" class="btn btn-danger">Supprimer</a>
								</td>
							</tr>

							<?php endforeach; ?>
							<?php else: ?>
								Aucun article enregistré.
							<?php endif; ?>

						</tbody>
					</table>

				</section>

			</div>

		</div>
	</body>
</html>
