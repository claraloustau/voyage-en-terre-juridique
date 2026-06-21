<?php

use Kirby\Cms\Page;

class HomePage extends Page
{
	// intro + notes liées au champ wordnotes
	public function introHtmlWithNotes()
	{
		$html = '';
		if ($this->intro()->isNotEmpty()) {
			$html = $this->intro()->value();
		}
		return BookWordNotesInjector::inject($html, $this->wordnotes());
	}
}
