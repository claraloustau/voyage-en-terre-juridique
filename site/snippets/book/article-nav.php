<?php
// prev / next entre sous-chapitres
if (!($page instanceof SubchapterPage)) {
	return;
}

$prev = $page->bookPrev();
$next = $page->bookNext();

if ($prev == null && $next == null) {
	return;
}
?>
<nav class="book-article-nav" aria-label="<?= esc('Enchaînement des parties') ?>">
	<div class="book-article-nav__cell book-article-nav__cell--prev">
		<?php if ($prev): ?>
		<a class="book-article-nav__link" href="<?= $prev->url() ?>">
			<span class="book-article-nav__label"><?= esc('Partie précédente') ?></span>
			<span class="book-article-nav__title"><?= $prev->title()->esc() ?></span>
		</a>
		<?php endif ?>
	</div>
	<div class="book-article-nav__cell book-article-nav__cell--next">
		<?php if ($next): ?>
		<a class="book-article-nav__link" href="<?= $next->url() ?>">
			<span class="book-article-nav__label"><?= esc('Partie suivante') ?></span>
			<span class="book-article-nav__title"><?= $next->title()->esc() ?></span>
		</a>
		<?php endif ?>
	</div>
</nav>
