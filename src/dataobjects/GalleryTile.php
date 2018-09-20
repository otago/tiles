<?php

namespace OP;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
/**
 * A series of images. Note the API change between 3 and 4: this now contains a 
 * dataobject and not an extension of image
 */
class GalleryTile extends Tile {
    private static $table_name = 'GalleryTile';
	private static $has_many = [
		'Slides' => Slide::class
	];
	
	private static $singular_name = "Gallery tile";
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('Content');
		
		if($this->ID) {
			$conf = GridFieldConfig_RelationEditor::create();
			$conf->addComponent(new GridFieldOrderableRows('Sort'));
			$conf->removeComponentsByType(new GridFieldAddExistingAutocompleter());
			$gridField = GridField::create( 'Slides', 'Slides', $this->Slides(), $conf);
			$fields->addFieldToTab('Root.Main', $gridField);
		} else {
			$fields->addFieldToTab('Root.Main', LiteralField::create('message', 'You need to save before creating slides'));
		}
		
		return $fields;
	}
	
	public function getPreviewImage() {
		if(!$this->Slides()->First()) {
			return null;
		}
		if(!$this->Slides()->First()->Image()) {
			return null;
		}
		return $this->Slides()->First()->Image()->ThumbnailURL(250,250) ;
	}
}