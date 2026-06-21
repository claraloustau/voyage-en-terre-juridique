<?php

$themeColor = $themeColor ?? null;

if ($themeColor && preg_match('/^#[0-9a-f]{6}$/i', $themeColor)) {
	$themeChapterHex = $themeColor;
} else {
	$themeChapterHex = '#1e4d6b';
}

$bookSurfaceRaw = trim($site->booksurface()->value());
if ($bookSurfaceRaw == '') {
	$bookSurfaceHex = '#faf9f7';
} else {
	$bookSurfaceHex = ChapterPage::panelColorToHex($bookSurfaceRaw);
}

$bookAccentRaw = trim($site->bookaccent()->value());
if ($bookAccentRaw == '') {
	$bookAccentHex = '#ffeb00';
} else {
	$bookAccentHex = ChapterPage::panelColorToHex($bookAccentRaw);
}

if ($page->isHomePage()) {
	$docTitle = $page->booktitle()->or($site->title())->esc();
} else {
	$docTitle = $site->title()->esc() . ' | ' . $page->title()->esc();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= $docTitle ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
  <?= css(['assets/css/book.css']) ?>
  <style id="book-theme">
    :root {
      --book-chapter: <?= $themeChapterHex ?>;
      --book-surface: <?= $bookSurfaceHex ?>;
      --book-brutal-yellow: <?= $bookAccentHex ?>;
    }
  </style>

  <link rel="shortcut icon" type="image/x-icon" href="<?= url('favicon.ico') ?>">
</head>
<body class="book-site book--toc-collapsed">

  <a class="book-skip" href="#book-main"><?= esc('Aller au contenu') ?></a>
  <header class="book-header">
	<div class="book-header__inner">
		<?php
		$logoTitle = $site->title()->value();
		$logoChars = mb_str_split($logoTitle);
		?>
		<a class="book-logo" href="<?= $site->url() ?>" aria-label="<?= $site->title()->esc() ?>">
			<span class="book-logo__wave" aria-hidden="true">
				<?php foreach ($logoChars as $i => $ch): ?>
				<span class="book-logo__char" style="--i: <?= (int) $i ?>"><?php
					echo $ch === ' ' ? "\u{00A0}" : esc($ch);
				?></span>
				<?php endforeach ?>
			</span>
		</a>
		<div class="book-header__actions">
			<div class="book-search" id="book-search">
				<label class="book-search__label" for="book-search-input"><?= esc('Rechercher dans le livre') ?></label>
				<input
					type="search"
					id="book-search-input"
					class="book-search__input"
					placeholder="<?= esc('Rechercher…') ?>"
					autocomplete="off"
					spellcheck="false"
					aria-controls="book-search-results"
					aria-autocomplete="list"
				>
				<div class="book-search__results" id="book-search-results" role="listbox" aria-label="<?= esc('Résultats de recherche') ?>" hidden></div>
			</div>
			<button type="button" class="book-toc-toggle" id="book-toc-toggle" aria-expanded="false" aria-controls="book-aside">
				<span class="book-toc-toggle__text"><?= esc('Sommaire') ?></span>
			</button>
		</div>
	</div>
  </header>
  <div class="book-layout">
	<?php snippet('book/sidebar') ?>
	<div class="book-layout__main">

  <main class="book-main" id="book-main">
