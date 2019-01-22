<?php

namespace OP\Elements;

use DNADesign\Elemental\Models\BaseElement;
use OP\Fields\TileField;
use OP\Models\Tile;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\NumericField;

class TileElement extends BaseElement {

	private static $singular_name = 'Tile';
	private static $plural_name = 'Tiles';
	private static $description = 'creates a grid of tiles';
	private static $db = [
		'Rows' => 'Int'
	];
	private static $has_many = [
		'Tiles' => Tile::class
	];
	private static $owns = [
		'Tiles'
	];
    private static $table_name = 'TileElement';

	/*public function getCMSFields() {
		$fields = FieldList::create();
		$fields->push(new TabSet("Root", $mainTab = new Tab("Main")));

		//$fields->addFieldToTab('Root.Main', HiddenField::create('Title', 'Title'));
		$fields->addFieldToTab('Root.Main', TextField::create('Title', 'Title'));
		//$fields->addFieldToTab('Root.Main', NumericField::create('Rows', 'Rows'));
		
		$tilefield = TileField::create('Tiles', 'Tiles', $this->Tiles(), null, $this);
		if (!$this->ID) {
			$tilefield->setDisabled(true);
			$tilefield->setDescription('Please save to begin editing');
		}
		$fields->addFieldToTab('Root.Main', $tilefield);
		return $fields;
	}*/
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		//$fields->addFieldToTab('Root.Main', HiddenField::create('Title', 'Title'));
		//$fields->addFieldToTab('Root.Main', TextField::create('Title', 'Title'));
		//$fields->addFieldToTab('Root.Main', NumericField::create('Rows', 'Rows'));
		
		$tilefield = TileField::create('Tiles', 'Tiles', $this->Tiles(), null, $this);
		if (!$this->ID) {
			$tilefield->setDisabled(true);
			$tilefield->setDescription('Please save to begin editing');
		}
		$fields->addFieldToTab('Root.Main', $tilefield);
		return $fields;
	}

	public function getType() {
		return 'Tiles';
	}

}
