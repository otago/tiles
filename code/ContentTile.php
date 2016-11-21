<?php


/**
 * Content tile - some text. also a background image
 */
class ContentTile extends Tile {
	protected static $singular_name = "Generic content tile";
	
	public function getCMSFields() {
		return parent::getCMSFields();
	}
	public function Preview() {
		return $this->Content;
	}
}