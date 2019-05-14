<?php

namespace OP\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Group;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Member;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Versioned\Versioned;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use OP\Elements\TileElement;
use SilverStripe\Security\Security;

/**
 * 
 */
class Tile extends DataObject {

    use Injectable;

    // enable cascade publishing
    private static $extensions = [
        Versioned::class
    ];
    private static $table_name = 'Tile';
    private static $singular_name = "Generic Tile";
    private static $db = [
        'Color' => 'Text', // red, green blue etc.
        'Content' => 'HTMLText', // text in the content field
        'Row' => 'Int',
        'Col' => 'Int',
        'Sort' => 'Int', // calculated by TileField
        'Width' => 'Int',
        'Height' => 'Int',
        //..'Name' => 'Text', // used in one-many relationships
        'Disabled' => 'Boolean',
        'CanViewType' => "Enum('Anyone, LoggedInUsers, OnlyTheseUsers, Inherit', 'Inherit')",
        'CanEditType' => "Enum('LoggedInUsers, OnlyTheseUsers, Inherit', 'Inherit')",
    ];
    private static $has_one = [
        'Parent' => TileElement::class
    ];
    private static $many_many = [
        'ViewerGroups' => Group::class,
        'EditorGroups' => Group::class,
    ];
    private static $defaults = [
        'CanViewType' => 'Inherit',
        'CanEditType' => 'Inherit'
    ];
    protected static $maxheight = 2;
    protected static $maxwidth = 2;

    public function __construct($record = null, $isSingleton = false, $model = null) {
        parent::__construct($record, $isSingleton, $model);
    }

    /**
     * create the field names 
     * @return \FieldList
     */
    public function getCMSFields() {
        $fields = FieldList::create();
        $fields->push(new TabSet("Root", $mainTab = new Tab("Main")));
        $fields->addFieldsToTab('Root.Main', CheckboxField::create('Disabled', 'Disabled'));
        $fields->addFieldsToTab('Root.Main', HTMLEditorField::create('Content', 'Content'));

        if (class_exists(\OP\ColorField::class)) {
            $fields->addFieldToTab('Root.Main', \OP\ColorField::create('Color', 'Color Override', $this->Color), 'Content');
        } else {
            $fields->addFieldToTab('Root.Main', TextField::create('Color', 'Color Override'), 'Content');
        }

        $fields->addFieldsToTab('Root.Settings', $this->getSettingsFields());

        return $fields;
    }

    /**
     * how big this tile can grow side ways
     * @return int
     */
    public function getMaxWidth() {
        return $this::$maxwidth;
    }

    /**
     * how tall this tile can get
     * @return int
     */
    public function getMaxHeight() {
        return $this::$maxheight;
    }

    /**
     * X-Y format of this tile
     * @return string
     */
    public function getSize() {
        return $this->Width . '-' . $this->Height;
    }

    /**
     * Returns fields related to configuration aspects on this record, e.g. access control.
     * See {@link getCMSFields()} for content-related fields.
     * 
     * @return FieldList
     */
    public function getSettingsFields() {
        $groupsMap = array();
        foreach (Group::get() as $group) {
            // Listboxfield values are escaped, use ASCII char instead of &raquo;
            $groupsMap[$group->ID] = $group->getBreadcrumbs(' > ');
        }
        asort($groupsMap);

        $fields = FieldList::create(array(
                    $viewersOptionsField = new OptionsetField(
                    "CanViewType", _t('Tile.ACCESSHEADER', "Who can view this tile?")
                    ),
                    $viewerGroupsField = ListboxField::create("ViewerGroups", _t('SiteTree.VIEWERGROUPS', "Viewer Groups"))
                    ->setSource($groupsMap)
                    ->setAttribute(
                    'data-placeholder', _t('Tile.GroupPlaceholder', 'Click to select group')
                    ),
                    $editorsOptionsField = new OptionsetField(
                    "CanEditType", _t('Tile.EDITHEADER', "Who can edit this tile?")
                    ),
                    $editorGroupsField = ListboxField::create("EditorGroups", _t('SiteTree.EDITORGROUPS', "Editor Groups"))
                    ->setSource($groupsMap)
                    ->setAttribute(
                    'data-placeholder', _t('Tile.GroupPlaceholder', 'Click to select group')
                    )
        ));

        $viewersOptionsSource = array();
        $viewersOptionsSource["Inherit"] = _t('Tile.INHERIT', "Inherit from parent page");
        $viewersOptionsSource["Anyone"] = _t('Tile.ACCESSANYONE', "Anyone");
        $viewersOptionsSource["LoggedInUsers"] = _t('Tile.ACCESSLOGGEDIN', "Logged-in users");
        $viewersOptionsSource["OnlyTheseUsers"] = _t('Tile.ACCESSONLYTHESE', "Only these people (choose from list)");
        $viewersOptionsField->setSource($viewersOptionsSource);

        $editorsOptionsSource = array();
        $editorsOptionsSource["Inherit"] = _t('Tile.INHERIT', "Inherit from parent page");
        $editorsOptionsSource["LoggedInUsers"] = _t('Tile.EDITANYONE', "Anyone who can log-in to the CMS");
        $editorsOptionsSource["OnlyTheseUsers"] = _t('Tile.EDITONLYTHESE', "Only these people (choose from list)");
        $editorsOptionsField->setSource($editorsOptionsSource);

        if (!Permission::check('SITETREE_GRANT_ACCESS')) {
            $fields->makeFieldReadonly($viewersOptionsField);
            if ($this->CanViewType == 'OnlyTheseUsers') {
                $fields->makeFieldReadonly($viewerGroupsField);
            } else {
                $fields->removeByName('ViewerGroups');
            }

            $fields->makeFieldReadonly($editorsOptionsField);
            if ($this->CanEditType == 'OnlyTheseUsers') {
                $fields->makeFieldReadonly($editorGroupsField);
            } else {
                $fields->removeByName('EditorGroups');
            }
        }

        return $fields;
    }

    /**
     * render the tile
     * @return type
     */
    public function forTemplate() {
        $shortname = (new \ReflectionClass($this))->getShortName();
        return $this->renderWith(array('Tiles/' . $shortname, $shortname));
    }

    /**
     * Returns CSS friendly name
     * @return string
     */
    public function CSSName() {
        $shortname = (new \ReflectionClass($this))->getShortName();
        return strtolower($shortname);
    }

    /**
     * Validates the tile data object
     * @return A {@link ValidationResult} object
     */
    public function validate() {
        $result = parent::validate();

        if ($this->Height > $this::$maxheight) {
            $result->addError("Height of $this::\$maxheight exceeded" . $this->Height . ' ' . $this::$maxheight);
        }

        if ($this->Width > $this::$maxwidth) {
            $result->addError("Width of $this::\$maxheight exceeded");
        }

        return $result;
    }

    public function canCreate($member = null, $context = array()) {
        if (!$this->Parent()) {
            return true;
        }
        return $this->Parent()->canCreate($member, $context);
    }

    /**
     * This function should return true if the current user can view this
     * page. It can be overloaded to customise the security model for an
     * application.
     * 
     * Denies permission if any of the following conditions is TRUE:
     * - canView() on any extension returns FALSE
     * - "CanViewType" directive is set to "Inherit" and any parent page return false for canView()
     * - "CanViewType" directive is set to "LoggedInUsers" and no user is logged in
     * - "CanViewType" directive is set to "OnlyTheseUsers" and user is not in the given groups
     *
     * @uses DataExtension->canView()
     * @uses ViewerGroups()
     *
     * @param Member|int|null $member
     * @return boolean True if the current user can view this page.
     */
    public function canView($member = null) {
        if (!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
            $member = Security::getCurrentUser() && Security::getCurrentUser()->ID;
        }

        // admin override
        if ($member && Permission::checkMember($member, array("ADMIN", "SITETREE_VIEW_ALL")))
            return true;

        // Standard mechanism for accepting permission changes from extensions
        $extended = $this->extendedCan('canView', $member);
        if ($extended !== null)
            return $extended;

        // check for empty spec
        if (!$this->CanViewType || $this->CanViewType == 'Anyone')
            return true;

        // check for inherit
        if ($this->CanViewType == 'Inherit') {
            if (!$this->ParentID) {
                return true;
            }
            return DataObject::get_by_id(TileElement::class, $this->ParentID)->canView($member);
        }

        // check for any logged-in users
        if ($this->CanViewType == 'LoggedInUsers' && $member) {
            return true;
        }

        // check for specific groups
        if ($member && is_numeric($member))
            $member = DataObject::get_by_id(Member::class, $member);
        if (
                $this->CanViewType == 'OnlyTheseUsers' && $member && $member->inGroups($this->ViewerGroups())
        )
            return true;

        return false;
    }

    /**
     * This function should return true if the current user can edit this
     * page. It can be overloaded to customise the security model for an
     * application.
     * 
     * Denies permission if any of the following conditions is TRUE:
     * - canEdit() on any extension returns FALSE
     * - canView() return false
     * - "CanEditType" directive is set to "Inherit" and any parent page return false for canEdit()
     * - "CanEditType" directive is set to "LoggedInUsers" and no user is logged in or doesn't have the CMS_Access_CMSMAIN permission code
     * - "CanEditType" directive is set to "OnlyTheseUsers" and user is not in the given groups
     * 
     * @uses canView()
     * @uses EditorGroups()
     * @uses DataExtension->canEdit()
     *
     * @param Member $member Set to FALSE if you want to explicitly test permissions without a valid user (useful for unit tests)
     * @return boolean True if the current user can edit this page.
     */
    public function canEdit($member = null) {
        if ($member instanceof Member) {
            $memberID = $member->ID;
        } else if (is_numeric($member)) {
            $memberID = $member;
        } else {
            $memberID = Security::getCurrentUser()->ID;
        }
        if ($memberID && Permission::checkMember($memberID, array("ADMIN", "SITETREE_EDIT_ALL"))) {
            return true;
        }

        // Standard mechanism for accepting permission changes from extensions
        $extended = $this->extendedCan('canEdit', $memberID);
        if ($extended !== null) {
            return $extended;
        }

        // check for inherit
        if ($this->CanEditType == 'Inherit') {
            if (!$this->ParentID) {
                return true;
            }
            return DataObject::get_by_id(TileElement::class, $this->ParentID)->canEdit($member);
        }

        // check for any logged-in users
        if ($this->CanEditType == 'LoggedInUsers' && $member) {
            return true;
        }

        // check for specific groups
        if ($member && is_numeric($member)) {
            $member = DataObject::get_by_id(Member::class, $member);
        }
        if ($this->CanEditType == 'OnlyTheseUsers' && $member && $member->inGroups($this->ViewerGroups())) {
            return true;
        }

        return false;
    }

    /**
     * get the width of this item (min of 1)
     * @return int
     */
    public function getWidth() {
        return max($this->getField('Width'), 1);
    }

    /**
     * get the height of this item (min of 1)
     * @return int
     */
    public function getHeight() {
        return max($this->getField('Height'), 1);
    }

    /**
     * takes in position x and y, and saves it
     * @param array $data
     */
    public function writeRawArray($data) {
        if (isset($data['x'])) {
            $this->Col = (int) $data['x'];
        }
        if (isset($data['y'])) {
            $this->Row = (int) $data['y'];
        }
        if (isset($data['w'])) {
            $this->Width = (int) $data['w'];
        }
        if (isset($data['h'])) {
            $this->Height = (int) $data['h'];
        }
        $this->write();
    }

    /**
     * takes in position x and y, and saves it
     * @param array $data
     */
    public function generateRawArray() {
        return array(
            'i' => $this->ID,
            'n' => $this->singular_name(),
            'x' => (int) $this->Col,
            'y' => (int) $this->Row,
            'w' => (int) $this->getWidth(),
            'h' => (int) $this->getHeight(),
            'maxW' => $this->getMaxWidth(),
            'maxH' => $this->getMaxHeight(),
            'c' => $this->getTileColor(),
            'p' => $this->getPreviewContent(),
            'img' => $this->getPreviewImage(),
            'disabled' => $this->Disabled
        );
    }

    /**
     * if you specify a background color 
     * @return string
     */
    public function getTileColor() {
        return $this->Color ?: 'transparent';
    }

    /**
     * text to be inside the tile itself
     * @return string
     */
    public function getPreviewContent() {
        // return DBField::create_field(DBHTMLText::class, $this->Content)->LimitCharacters(150);
        return DBField::create_field(DBHTMLText::class, "(".$this->Col."x".$this->Row.")".$this->Content)->LimitCharacters(150);
    }

    /**
     * a preview image
     * @return Image|null
     */
    public function getPreviewImage() {
        return null;
    }

    /**
     * Maybe here we just have to get all the tiles for this elemental and run the SortTiles on it.
     * Then I guess we just take the last one and add the tile position beside that.
     * Probably have to get how many columns the grid has and make sure we arent doing something retarded like
     * col=5 when the grid is only supporting 4
     */
    public function onBeforeWrite()
    {
        error_log(var_export("Cool looks like it is in here to set initial x: y: ", true));
        error_log(var_export("onBeforeWrite Tile", true));
        error_log(var_export($this, true));
        parent::onBeforeWrite();
    }

}
