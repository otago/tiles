<?php

namespace OP;

use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\Form;

/**
 * This detail form is used to insert a hidden type field when creating a new 
 * tile.
 */
class TileFieldDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest {
	
	// required to enable actions
    private static $allowed_actions = array(
        'edit',
        'view',
        'ItemEditForm'
    );

	public function ItemEditForm() {
		$form = parent::ItemEditForm();
		if(!$form instanceof Form) {
			return $form;
		}
		$fields = $form->Fields();
		$fields->addFieldToTab('Root.Main', HiddenField::create('TileType', 'Type Type', $this->component->getTileType()));
		$form->setFields($fields);
		return $form;
	}

}
