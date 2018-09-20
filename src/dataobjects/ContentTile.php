<?php


namespace OP;
use SilverStripe\Forms\TextField;

/**
 * Content tile - some text. also a background image
 */
class ContentTile extends Tile {
    private static $table_name = 'ContentTile';
	private static $singular_name = "Content tile";
	
	private static $db = [
		'Title' => 'Text'
	];
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldsToTab('Root.Main', TextField::create('Title', 'Title'), 'Content');
		return $fields;
		
	}
}