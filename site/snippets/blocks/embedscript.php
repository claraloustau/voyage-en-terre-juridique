<?php /** @var \Kirby\Cms\Block $block */
$markup = trim((string) $block->markup()->value());
if ($markup === '') {
	return;
}
?>
<div class="book-blocks__embed book-blocks__embed--raw">
	<?= $markup ?>
</div>
