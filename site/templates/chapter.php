<?php snippet('header', ['themeColor' => $page->chapterAccentHex()]) ?>
<?php
$bg = $page->chapterAccentHex();
$fg = $page->chapterBadgeTextColor();
?>

<div class="book-grid">
	<header class="book-chapter-head">
		<h1 class="book-chapter-head__title">
			<span class="book-chapter-badge" style="background-color: <?= $bg ?>; color: <?= $fg ?>;"><?= $page->title()->esc() ?></span>
		</h1>
	</header>

	<div class="book-grid__content">
		<?php $subs = $page->children()->filterBy('template', 'subchapter')->listed(); ?>
		<?php if ($subs->isNotEmpty()): ?>
		<nav class="book-chapter-subs" aria-label="<?= esc('Sous-chapitres') ?>">
			<h2 class="book-chapter-subs__h"><?= esc('Dans ce chapitre') ?></h2>
			<ol class="book-chapter-subs__list">
				<?php foreach ($subs as $sub): ?>
				<li>
					<a href="<?= $sub->url() ?>"><?= $sub->title()->esc() ?></a>
				</li>
				<?php endforeach ?>
			</ol>
		</nav>
		<?php elseif ($page->children()->isNotEmpty()): ?>
		<?php endif ?>
	</div>
</div>

<?php snippet('footer') ?>
