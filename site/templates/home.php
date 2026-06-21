<?php snippet('header', ['themeColor' => null]) ?>

<div class="book-grid">
	<div class="book-grid__content book-grid__content--cover">
		<?php if ($page->booktitle()->isNotEmpty()): ?>
		<h1 class="book-cover__title"><?= $page->booktitle()->esc() ?></h1>
		<?php endif ?>
		<?php if ($page->booksubtitle()->isNotEmpty()): ?>
		<p class="book-cover__subtitle"><?= $page->booksubtitle()->esc() ?></p>
		<?php endif ?>
		<?php if ($page->intro()->isNotEmpty()): ?>
		<div class="book-cover__intro book-prose">
			<?= $page->introHtmlWithNotes() ?>
		</div>
		<?php endif ?>

		<?php
		$chapters = $page->children()->filterBy('template', 'chapter')->listed();
		$first = $chapters->first();
		?>
		<?php if ($first): ?>
		<p class="book-cover__cta">
			<a class="book-btn" href="<?= $first->url() ?>"><?= esc('Commencer la lecture') ?></a>
		</p>
		<?php endif ?>
	</div>
</div>

<?php snippet('footer') ?>
