<?php


/**
 * Content tile - some text. also a background image
 */
class PhotoTile extends Tile {
	private static $has_one = array(
		'Image' => 'Image'
	);
	
	protected static $allowed_sizes = array('1x1', '1x2', '2x2', '3x2');
	
	protected static $singular_name = "Photo tile";
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('Content');
		
        $fields->addFieldToTab('Root.Main', $thumb = new UploadField('Image', 'Upload Image'));
        $thumb->setAllowedFileCategories('image');
        $thumb->setFolderName('tiles/photo');
        $thumb->setOverwriteWarning(false);
        $thumb->setAllowedMaxFileNumber(1);
        
		return $fields;
	}
	
	public function Preview() {
		return $this->Image;
	}
}
