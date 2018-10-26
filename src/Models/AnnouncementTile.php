<?php

namespace OP\Models;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\DateField;

/**
 * just some content
 */
class AnnouncementTile extends Tile {

	private static $table_name = 'AnnouncementTile';
	
	private static $singular_name = "Announcement tile";
	private static $db = [
		'StartDate' => 'Date',
		'EndDate' => 'Date',
		'Type' => 'Text'
	];

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$typeArray = ['icon-Exclamation' => 'Exclamation', 'icon-InfoSign' => 'Information', 'icon-Question' => 'Question'];

		$fields->addFieldsToTab('Root.Main', [
			DropdownField::create('Type', 'Type', $typeArray),
			DateField::create('StartDate', 'Start Date', $this->StartDate),
			DateField::create('EndDate', 'End Date', $this->EndDate)], 'Content');

		return $fields;
	}

	public function ShowTile() {
		$today = date('Y-m-d');
		$todayDate = date('Y-m-d', strtotime($today));

		$announcementDateBegin = date('Y-m-d', strtotime($this->StartDate));
		$announcementDateEnd = date('Y-m-d', strtotime($this->EndDate));

		if (($todayDate >= $announcementDateBegin) && ($todayDate <= $announcementDateEnd)) {
			return true;
		} else {
			$this->Disabled = true;
			$this->write();
			return false;
		}
	}


}
