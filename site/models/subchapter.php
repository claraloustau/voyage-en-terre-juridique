<?php

use Kirby\Cms\Page;
use Kirby\Cms\Site;

class SubchapterPage extends Page
{
	public function bodyHtmlWithNotes()
	{
		$html = '';
		if ($this->body()->isNotEmpty()) {
			$html = $this->body()->toBlocks()->toHtml();
		}
		return BookWordNotesInjector::inject($html, $this->wordnotes());
	}

	public function chapterAccent()
	{
		$parent = $this->parent();
		if ($parent && $parent instanceof ChapterPage) {
			return $parent->chapterAccent();
		}
		return '#1e4d6b';
	}

	public function chapterAccentHex()
	{
		$parent = $this->parent();
		if ($parent && $parent instanceof ChapterPage) {
			return $parent->chapterAccentHex();
		}
		return '#1e4d6b';
	}

	public static function flatListedSubchapters(Site $site)
	{
		$home = $site->homePage();
		if (!$home) {
			return [];
		}
		$result = [];
		$chapters = $home->children()->filterBy('template', 'chapter')->listed();
		foreach ($chapters as $chapter) {
			$subs = $chapter->children()->filterBy('template', 'subchapter')->listed();
			foreach ($subs as $sub) {
				$result[] = $sub;
			}
		}
		return $result;
	}

	public function bookPrev()
	{
		$all = self::flatListedSubchapters($this->kirby()->site());
		for ($i = 0; $i < count($all); $i++) {
			if ($all[$i]->id() == $this->id()) {
				if ($i == 0) {
					return null;
				}
				return $all[$i - 1];
			}
		}
		return null;
	}

	public function bookNext()
	{
		$all = self::flatListedSubchapters($this->kirby()->site());
		for ($i = 0; $i < count($all); $i++) {
			if ($all[$i]->id() == $this->id()) {
				if ($i >= count($all) - 1) {
					return null;
				}
				return $all[$i + 1];
			}
		}
		return null;
	}
}
