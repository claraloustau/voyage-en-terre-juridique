<?php

require_once __DIR__ . '/WordNotesInjector.php';

Kirby::plugin('local/book-kit', [
	'tags' => [
		// dans markdown : (annot: mot note: ma note)
		'annot' => [
			'attr' => [
				'note' => null,
			],
			'html' => function ($tag) {
				$term = trim($tag->value);
				$note = trim($tag->attr('note'));

				if ($term == '') {
					return '';
				}
				if ($note == '') {
					return esc($term, 'html');
				}

				$parent = $tag->parent();
				if ($parent != null) {
					$noteHtml = kirby()->kirbytext($note, ['parent' => $parent]);
				} else {
					$noteHtml = kirby()->kirbytext($note);
				}

				$id = 'book-tip-' . bin2hex(random_bytes(5));
				$payload = base64_encode($noteHtml);
				$attrPayload = ' data-book-note-payload="' . esc($payload, 'attr') . '"';

				$html = '<span class="book-annot-wrap">';
				$html .= '<span class="book-annot" role="button" tabindex="0" aria-haspopup="dialog" aria-expanded="false" data-book-note="' . esc($id, 'attr') . '"' . $attrPayload . '>' . esc($term, 'html') . '</span>';
				$html .= '<span id="' . esc($id, 'attr') . '" class="book-annot__tip book-annot__tip--source" data-book-annot="1" hidden aria-hidden="true"></span>';
				$html .= '</span>';

				return $html;
			}
		],
	],
]);
