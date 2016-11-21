<?php


/**
 * A serries of images
 */
class GalleryTile extends Tile {
	private static $has_many = array(
		'Images' => 'TileImage'
	);
	
	protected static $allowed_sizes = array('1x1','2x2','3x2');
	
	protected static $singular_name = "Gallery tile";
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('Content');
		
		$uploadField = UploadField::create( 'Images', 'Upload one or more images');
		$fields->addFieldToTab('Root.Main', $uploadField);
		return $fields;
	}
	
	public function Preview() {
		return $this->Images()->First();
	}
}