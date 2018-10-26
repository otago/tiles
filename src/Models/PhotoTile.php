<?php

namespace OP\Models;
use SilverStripe\Assets\Image;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\LiteralField;

/**
 * Content tile - some text. also a background image
 */
class PhotoTile extends Tile {
    private static $table_name = 'PhotoTile';
	private static $has_one = [
		'Image' => Image::class
	];
	
	private static $singular_name = "Photo tile";
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('Content');
		
		if($this->ID) {
			$fields->addFieldToTab('Root.Main', $thumb = UploadField::create('Image', 'Upload Image'));
			$thumb->setAllowedFileCategories('image');
			$thumb->setFolderName('tiles/photo');
			$thumb->setAllowedMaxFileNumber(1);
		} else {
			$fields->addFieldToTab('Root.Main', LiteralField::create('message', 'You need to save before uploading an image'));
		}
		
		return $fields;
	}
	
	public function getPreviewImage() {
		return $this->Image()->ThumbnailURL(250,250);
	}
}
