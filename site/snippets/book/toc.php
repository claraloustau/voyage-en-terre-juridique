<?php
$home = $site->homePage();
if (!$home) {
	return;
}
$chapters = $home->children()->filterBy('template', 'chapter')->listed();
?>
<div class="book-toc__inner">
	<p class="book-toc__title" id="book-toc-heading">Sommaire</p>
	<ol class="book-toc__chapters" aria-labelledby="book-toc-heading">
		<?php foreach ($chapters as $ch): ?>
			<?php
			// couleurs badge chapitre
			if ($ch instanceof ChapterPage) {
				$bg = $ch->chapterAccentHex();
			} else {
				$bg = ChapterPage::panelColorToHex($ch->chaptercolor()->value());
			}
			$fg = ChapterPage::badgeTextColorForHex($bg);

			$subs = $ch->children()->filterBy('template', 'subchapter')->listed();
			$hasSubs = $subs->count() > 0;
			$isOpen = $ch->isOpen();
			$chId = esc($ch->id(), 'attr');
			$subsId = 'book-toc-subs-' . $ch->id();
			$subsId = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $subsId);
			$subsId = esc($subsId, 'attr');
			?>
			<li class="book-toc__chapter<?= $isOpen ? ' book-toc__chapter--open' : '' ?><?= ($hasSubs && !$isOpen) ? ' book-toc__chapter--collapsed' : '' ?>"
				<?php if ($hasSubs) echo ' data-chapter-id="' . $chId . '"'; ?>>
				<?php if ($hasSubs) : ?>
				<div class="book-toc__chapter-bar">
					<a class="book-toc__chapter-link book-chapter-badge book-chapter-badge--toc" href="<?= $ch->url() ?>" style="background-color: <?= $bg ?>; color: <?= $fg ?>;"><?= $ch->title()->esc() ?></a>
					<button
						type="button"
						class="book-toc__toggle"
						aria-expanded="<?= $isOpen ? 'true' : 'false' ?>"
						aria-controls="<?= $subsId ?>"
						aria-label="<?= $isOpen ? 'Replier' : 'Déplier' ?> les sous-chapitres"
					>
						<span class="book-toc__chev" aria-hidden="true"></span>
					</button>
				</div>
				<?php else : ?>
					<div class="book-toc__chapter-bar book-toc__chapter-bar--link-only">
						<a class="book-toc__chapter-link book-chapter-badge book-chapter-badge--toc" href="<?= $ch->url() ?>" style="background-color: <?= $bg ?>; color: <?= $fg ?>;"><?= $ch->title()->esc() ?></a>
					</div>
				<?php endif ?>
				<?php if ($hasSubs) : ?>
				<ol class="book-toc__subs" id="<?= $subsId ?>" <?php if (!$isOpen) echo 'hidden'; ?>>
					<?php foreach ($subs as $sub) : ?>
					<li>
						<a href="<?= $sub->url() ?>" <?php e($sub->isActive(), 'aria-current="page"') ?>><?= $sub->title()->esc() ?></a>
					</li>
					<?php endforeach ?>
				</ol>
				<?php endif ?>
			</li>
		<?php endforeach ?>
	</ol>
</div>
