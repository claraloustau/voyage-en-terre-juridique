<?php

use Kirby\Cms\Page;

class ChapterPage extends Page
{
	// couleur choisie dans le panel pour ce chapitre
	public function chapterAccent()
	{
		$c = trim($this->chaptercolor()->value());
		if ($c == '') {
			return '#1e4d6b';
		}
		return $c;
	}

	public function chapterAccentHex()
	{
		$c = trim($this->chapterAccent());
		if ($c == '' || $c[0] != '#') {
			return '#1e4d6b';
		}
		$h = ltrim($c, '#');
		if (strlen($h) == 3) {
			$h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
		}
		if (strlen($h) != 6) {
			return '#1e4d6b';
		}
		return '#' . $h;
	}

	// utilisé aussi pour booksurface / bookaccent dans header.php
	public static function panelColorToHex($raw)
	{
		$raw = trim($raw);
		if ($raw == '' || $raw[0] != '#') {
			return '#1e4d6b';
		}
		$h = ltrim($raw, '#');
		if (strlen($h) == 3) {
			$h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
		}
		if (strlen($h) != 6) {
			return '#1e4d6b';
		}
		return '#' . $h;
	}

	public function chapterBadgeTextColor()
	{
		$hex = $this->chapterAccentHex();
		return self::badgeTextColorForHex($hex);
	}

	public static function badgeTextColorForHex($hex)
	{
		$hex = trim($hex);
		if ($hex == '' || strlen($hex) < 7) {
			$hex = '#1e4d6b';
		}
		$r = hexdec(substr($hex, 1, 2));
		$g = hexdec(substr($hex, 3, 2));
		$b = hexdec(substr($hex, 5, 2));
		// si la couleur est plutôt foncée on met du blanc
		if ($r + $g + $b < 420) {
			return '#ffffff';
		}
		return '#14120f';
	}
}
