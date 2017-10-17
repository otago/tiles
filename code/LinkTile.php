<?php

/**
 * A tile the holds an image, and an external or internal link to resource
 */
class LinkTile extends Tile {

	protected static $singular_name = "Link Tile Pages";
	protected static $allowed_sizes = array(
		'1x1'
	);
	private static $db = array(
		'Title' => 'Text', // text in the content field
		'URL' => 'Text'
	);
	private static $has_one = array(
		'Image' => 'Image',
		'Tree' => 'SiteTree'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('TreeID');
		$fields->fieldByName('Root.Main.URL')->setDescription('For external links');
		$tree = new TreeDropdownField("TreeID", "Local page to link", "SiteTree");
		$tree->setDescription('Select the same item twice to clear');
		$fields->addFieldToTab('Root.Main', $tree);

		$fields->removeByName('Content');
		$imageupload = new UploadField('Image', 'Upload Image');
		$fields->addFieldToTab('Root.Main', $imageupload);
		$imageupload->setAllowedFileCategories('image');
		$imageupload->setFolderName('tiles/photo');
		$imageupload->setOverwriteWarning(false);
		$imageupload->setAllowedMaxFileNumber(1);
		$imageupload->setDescription('Image that will be displayed on the tile.');
		$imageupload->setFolderName('widgets');

		return $fields;
	}

	public function Preview() {
		if ($this->Content) {
			return $this->Content;
		}
		if ($this->PageContent) {
			return $this->PageContent;
		}
		if ($this->ImageID) {
			if(!$this->Image()->SetRatioSize(230, 170)) {
				return 'resize failed';
			}
			return $this->Image()->SetRatioSize(230, 170)->getTag();
		}
		return 'Pagination tile requires page content or an image.';
	}

	public function getLink() {
		$tid = $this->TreeID ? : $this->treeid;
		if ($tid && SiteTree::get()->byID($tid)) {
			return Controller::join_links(Director::absoluteBaseURL(), SiteTree::get()->byID($tid)->Link());
		}
		return $this->URL ? : $this->url;
	}

}
