<?php

namespace OP;

use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\RequiredFields;

/**
 * This detail form is used to insert a hidden type field when creating a new 
 * tile.
 */
class TileFieldDetailForm extends GridFieldDetailForm {

	private $tiletype = null;

	public function setTileType($str) {
		$this->tiletype = $str;
	}

	public function getTileType() {
		return $this->tiletype;
	}

	public function __construct($name = 'DetailForm', $tiletype = null) {
		$this->setTileType($tiletype);
		$this->setItemRequestClass(TileFieldDetailForm_ItemRequest::class);
		parent::__construct($name);
	}

	public function getValidator() {
		return new RequiredFields([]);
	}

}
