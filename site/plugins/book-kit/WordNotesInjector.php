<?php

// remplace les mots du corps par des notes cliquables (champ structure wordnotes)

use Kirby\Content\Field;
use Kirby\Cms\ModelWithContent;

class BookWordNotesInjector
{
	public static function inject($html, Field $wordnotes)
	{
		if ($html == '' || $wordnotes->isEmpty()) {
			return $html;
		}

		$parent = $wordnotes->parent();
		$ktParent = null;
		if ($parent instanceof ModelWithContent) {
			$ktParent = $parent;
		}

		$liste = [];
		foreach ($wordnotes->toStructure() as $row) {
			$mot = trim($row->term()->value());
			if ($mot == '') {
				continue;
			}
			$noteHtml = self::noteFieldToHtml($row->note(), $ktParent);
			if (trim($noteHtml) == '') {
				continue;
			}
			$liste[] = ['term' => $mot, 'noteHtml' => $noteHtml];
		}

		if (count($liste) == 0) {
			return $html;
		}

		// les expressions longues d'abord sinon "droit" matche avant "droit civil"
		usort($liste, function ($a, $b) {
			return mb_strlen($b['term']) - mb_strlen($a['term']);
		});

		$dom = new DOMDocument('1.0', 'UTF-8');
		libxml_use_internal_errors(true);
		$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		libxml_clear_errors();

		foreach ($liste as $item) {
			self::applyRow($dom, $item['term'], $item['noteHtml']);
		}

		$htmlFinal = '';
		foreach ($dom->childNodes as $child) {
			$htmlFinal .= $dom->saveHTML($child);
		}

		return $htmlFinal;
	}

	private static function applyRow($dom, $term, $noteHtml)
	{
		$xpath = new DOMXPath($dom);
		$nodes = [];
		foreach ($xpath->query('//text()') as $n) {
			if ($n instanceof DOMText && self::okPourInjection($n)) {
				$nodes[] = $n;
			}
		}

		foreach ($nodes as $textNode) {
			if ($textNode->parentNode == null) {
				continue;
			}
			self::replaceInTextNode($dom, $textNode, $term, $noteHtml);
		}
	}

	private static function okPourInjection(DOMText $node)
	{
		$p = $node->parentNode;
		while ($p != null) {
			if ($p instanceof DOMElement) {
				$tag = strtolower($p->tagName);
				if ($tag == 'script' || $tag == 'style' || $tag == 'code' || $tag == 'pre') {
					return false;
				}
				$cls = $p->getAttribute('class');
				if (strpos($cls, 'book-annot') !== false) {
					return false;
				}
			}
			$p = $p->parentNode;
		}
		return true;
	}

	private static function replaceInTextNode($dom, $textNode, $term, $noteHtml)
	{
		if ($textNode->parentNode == null) {
			return;
		}

		$text = $textNode->nodeValue;
		$pattern = '/(' . preg_quote($term, '/') . ')/ui';
		if (!preg_match($pattern, $text)) {
			return;
		}

		$parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		if (!$parts || count($parts) < 2) {
			return;
		}

		$frag = $dom->createDocumentFragment();
		foreach ($parts as $i => $part) {
			if ($part == '') {
				continue;
			}
			if ($i % 2 == 1) {
				$frag->appendChild(self::makeAnnot($dom, $part, $noteHtml));
			} else {
				$frag->appendChild($dom->createTextNode($part));
			}
		}

		$textNode->parentNode->replaceChild($frag, $textNode);
	}

	private static function makeAnnot($dom, $visibleTerm, $noteHtml)
	{
		$id = 'book-tip-' . bin2hex(random_bytes(5));

		$wrap = $dom->createElement('span');
		$wrap->setAttribute('class', 'book-annot-wrap');

		$btn = $dom->createElement('span');
		$btn->setAttribute('class', 'book-annot');
		$btn->setAttribute('role', 'button');
		$btn->setAttribute('tabindex', '0');
		$btn->setAttribute('aria-haspopup', 'dialog');
		$btn->setAttribute('aria-expanded', 'false');
		$btn->setAttribute('data-book-note', $id);
		if (trim($noteHtml) != '') {
			$btn->setAttribute('data-book-note-payload', base64_encode($noteHtml));
		}
		$btn->appendChild($dom->createTextNode($visibleTerm));

		$tip = $dom->createElement('span');
		$tip->setAttribute('id', $id);
		$tip->setAttribute('class', 'book-annot__tip book-annot__tip--source');
		$tip->setAttribute('data-book-annot', '1');
		$tip->setAttribute('hidden', 'hidden');
		$tip->setAttribute('aria-hidden', 'true');

		$wrap->appendChild($btn);
		$wrap->appendChild($tip);

		return $wrap;
	}

	private static function noteFieldToHtml(Field $noteField, $ktParent)
	{
		if ($noteField->isEmpty()) {
			return '';
		}

		$raw = trim($noteField->value());
		if ($raw == '') {
			return '';
		}

		// contenu du writer (html)
		if (preg_match('/^\s*</', $raw)) {
			$out = $noteField->permalinksToUrls()->value();
			if (trim($out) == '' && trim($raw) != '') {
				return $raw;
			}
			return $out;
		}

		if ($ktParent != null) {
			return kirby()->kirbytext($raw, ['parent' => $ktParent]);
		}
		return kirby()->kirbytext($raw);
	}
}
