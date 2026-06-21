<?php snippet('header', ['themeColor' => $page->chapterAccentHex()]) ?>

<?php
$chapter = $page->parent();
$chapterBg = '#1e4d6b';
$chapterFg = '#ffffff';
if ($chapter && $chapter instanceof ChapterPage) {
	$chapterBg = $chapter->chapterAccentHex();
	$chapterFg = $chapter->chapterBadgeTextColor();
}
?>

<div class="book-grid book-grid--article">
	<header class="book-article-head">
		<?php if ($chapter): ?>
		<p class="book-article-head__chapter">
			<a class="book-chapter-badge book-chapter-badge--compact" href="<?= $chapter->url() ?>" style="background-color: <?= $chapterBg ?>; color: <?= $chapterFg ?>;"><?= $chapter->title()->esc() ?></a>
		</p>
		<?php endif ?>
		<h1 class="book-article-head__title"><?= $page->title()->esc() ?></h1>
		<?php if ($page->lede()->isNotEmpty()): ?>
		<p class="book-article-head__lede"><?= $page->lede()->esc() ?></p>
		<?php endif ?>
	</header>

	<div class="book-grid__content book-prose book-blocks">
		<?= $page->bodyHtmlWithNotes() ?>
	</div>

	<?php snippet('book/article-nav') ?>
</div>

<?php snippet('footer') ?>
