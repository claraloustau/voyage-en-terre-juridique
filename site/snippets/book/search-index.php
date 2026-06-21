<?php
// json pour la recherche dans book.js
$home = $site->homePage();
$entries = [];

if ($home) {
	if ($home->intro()->isNotEmpty()) {
		$introHtml = $home->intro()->value();
		if (method_exists($home->intro(), 'toBlocks')) {
			try {
				$introHtml = $home->intro()->toBlocks()->toHtml();
			} catch (Exception $e) {
				// on garde la valeur brute
			}
		}
		$text = strip_tags($introHtml);
		$text = preg_replace('/\s+/', ' ', $text);
		$text = trim($text);
		$entries[] = [
			'title' => $home->booktitle()->or($home->title())->value(),
			'chapter' => '',
			'url' => $home->url(),
			'text' => $text,
			'kind' => 'cover',
		];
	}

	$chapters = $home->children()->filterBy('template', 'chapter')->listed();
	foreach ($chapters as $ch) {
		$subs = $ch->children()->filterBy('template', 'subchapter')->listed();
		foreach ($subs as $sub) {
			$html = '';
			if ($sub->body()->isNotEmpty()) {
				$html = $sub->body()->toBlocks()->toHtml();
			}
			$text = strip_tags($html);
			$text = preg_replace('/\s+/', ' ', $text);
			$text = trim($text);
			if (strlen($text) > 14000) {
				$text = substr($text, 0, 14000);
			}
			$entries[] = [
				'title' => $sub->title()->value(),
				'chapter' => $ch->title()->value(),
				'url' => $sub->url(),
				'text' => $text,
				'kind' => 'article',
			];
		}
	}
}

echo '<script type="application/json" id="book-search-data">';
echo json_encode($entries);
echo '</script>';
