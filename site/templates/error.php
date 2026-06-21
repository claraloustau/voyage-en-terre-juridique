<?php snippet('header', ['themeColor' => null]) ?>

<div class="book-grid">
	<div class="book-grid__content book-grid__content--cover">
		<h1 class="book-cover__title"><?= esc('Page introuvable') ?></h1>
		<p class="book-cover__intro book-prose">
			<a href="<?= $site->url() ?>"><?= esc('Retour à l’accueil du livre') ?></a>
		</p>
	</div>
</div>

<?php snippet('footer') ?>
