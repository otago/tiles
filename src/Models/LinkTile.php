<?php

namespace OP\Models;

use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Forms\TextField;
use SilverStripe\Control\Director;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * A tile the holds an image, and an external or internal link to resource
 */
class LinkTile extends Tile {

    private static $table_name = 'LinkTile';
    private static $singular_name = "Link Tile";
    protected static $allowed_sizes = [
        '1x1'
    ];
    private static $db = [
        'Title' => 'Text', // text in the content field
        'URL' => 'Text'
    ];
    private static $has_one = [
        'Image' => Image::class,
        'Tree' => SiteTree::class
    ];

    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main', TextField::create('URL', 'URL')->setDescription('For external links'));
        $tree = new TreeDropdownField("TreeID", "Local page to link", SiteTree::class, 'ID', 'MenuTitle');
        $tree->setDescription('Select the same item twice to clear. You may have to save before seeing pages in the dropdown.');
        $fields->addFieldToTab('Root.Main', $tree);

        $imageupload = new UploadField('Image', 'Upload Image');
        $fields->addFieldToTab('Root.Main', $imageupload);
        $imageupload->setAllowedFileCategories('image');
        $imageupload->setFolderName('tiles/photo');
        $imageupload->setAllowedMaxFileNumber(1);
        $imageupload->setDescription('Image that will be displayed on the tile.');
        $imageupload->setFolderName('widgets');

        return $fields;
    }

    public function getPreviewImage() {
        if (!$this->Image()->ThumbnailURL(230, 170)) {
            return null;
        }
        return $this->Image()->ThumbnailURL(230, 170);
    }

    public function getPreviewContent() {
        if (!$this->Tree()) {
            return '';
        }
        return DBField::create_field(DBHTMLText::class, $this->Tree()->Title)->LimitCharacters(150);
    }

    public function getLink() {
        $tid = $this->TreeID ?: $this->treeid;
        if ($tid && SiteTree::get()->byID($tid)) {
            return Controller::join_links(Director::absoluteBaseURL(), SiteTree::get()->byID($tid)->Link());
        }
        return $this->URL ?: $this->url;
    }

}
