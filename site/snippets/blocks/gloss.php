<?php
$term = trim($block->term()->value());
$note = trim($block->note()->value());

if ($term != '' && $note != '') {
	$id = 'book-gloss-' . $block->id();
	$parent = $block->parent();
	if ($parent != null) {
		$noteHtml = kirby()->kirbytext($note, ['parent' => $parent]);
	} else {
		$noteHtml = kirby()->kirbytext($note);
	}
	$payloadB64 = base64_encode($noteHtml);
	?>
<p class="book-gloss-block">
	<span class="book-annot-wrap">
		<span class="book-annot" role="button" tabindex="0" aria-haspopup="dialog" aria-expanded="false" data-book-note="<?= esc($id, 'attr') ?>" data-book-note-payload="<?= esc($payloadB64, 'attr') ?>"><?= esc($term) ?></span>
		<span id="<?= esc($id, 'attr') ?>" class="book-annot__tip book-annot__tip--source" data-book-annot="1" hidden aria-hidden="true"></span>
	</span>
</p>
<?php } ?>
