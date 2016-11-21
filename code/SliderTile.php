<?php

/**
 * Slider Tile - Tiles in a slider.
 */
class SliderTile extends Tile {

	protected static $allowed_sizes = array('1x1', '2x2');
	protected static $singular_name = "Slider tile";

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('Content');
		$fields->removeByName('Tiles');

		$fields->addFieldToTab('Root.Main', TileField::create('Tiles', 'Tiles'));

		return $fields;
	}
	public function Preview() {
		return $this->getChildTiles()->First()->Content;
	}

	public function getChildTiles() {
		$tiles = Tile::get()->filter(array('ParentID' => $this->ID, 'Disabled' => false));
		$returnTiles = new ArrayList();
		foreach ($tiles as $tile) {
			if (method_exists($tile, 'IsVisible') && !$tile->IsVisible()) {
				continue;
			}
			$addMe = true;
			if ($tile->ClassName == 'NewsTile') {
				if ($tile->getNumberOfNewsEvents() == 0) {
					$addMe = false;
				}
			}
			if ($addMe)
				$returnTiles->add($tile);
		}
		return $returnTiles;
	}

}
