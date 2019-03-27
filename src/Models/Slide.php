<?php

namespace OP\Models;

use SilverStripe\Assets\Image;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextField;


/**
 * Slide - used for a tile with a collection of subobjects. Typically used in 
 * a slider with dots and a left/right button for navigation
 */
class Slide extends DataObject {
    private static $table_name = 'TileImage';
	private static $db = [
		'VideoURL' => 'Boolean', 
		'LinkURL' => 'Text', 
		'Sort' => 'Int'
	];
	private static $has_one = [
		'ParentTile' => Tile::class,
		'Image' => Image::class
	];
    private static $summary_fields = [
		'LinkURL',
		'Thumbnail'
	];
	
    private static $owns = [
        'Image'
    ];
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab('Root.Main', CheckboxField::create('VideoURL', 'Is Video'));
		$fields->addFieldToTab('Root.Main',TextField::create('LinkURL', 'Link or Video resource'));
		
		return $fields;
	}
	
	public function Thumbnail() {
		if($this->Image()) {
			return $this->Image()->CMSThumbnail();
		}
	}
}
