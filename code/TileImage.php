<?php


/**
 * TileImage 
 */
class TileImage extends Image {
	private static $db = array(
		'VideoURL' => 'Boolean', 
		'LinkURL' => 'Text'
	);
	private static $has_one = array(
		'ParentTile' => 'Tile'
	);
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab('Root.Main', CheckboxField::create('VideoURL', 'Is Video'));
		$fields->addFieldToTab('Root.Main',TextField::create('LinkURL', 'URL Address'));
		
		return $fields;
	}
}