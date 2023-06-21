<?php

namespace OP\Models;

use OP\Elements\TileElement;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\Versioned\Versioned;

/**
 *
 */
class Tile extends DataObject
{
    use Injectable;

    // enable cascade publishing
    private static $extensions = [
        Versioned::class,
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
        'CanViewType' => "Enum('Anyone, LoggedInUsers, OnlyTheseUsers', 'Anyone')",
    ];
    private static $has_one = [
        'Parent' => TileElement::class,
    ];
    private static $many_many = [
        'ViewerGroups' => Group::class,
    ];
    private static $defaults = [
        'CanViewType' => 'Anyone',

    ];
    protected static $maxheight = 2;
    protected static $maxwidth = 2;

    public function __construct($record = null, $isSingleton = false, $model = null)
    {
        parent::__construct($record, $isSingleton, $model);
    }

    /**
     * create the field names
     * @return \FieldList
     */
    public function getCMSFields()
    {
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

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    /**
     * how big this tile can grow side ways
     * @return int
     */
    public function getMaxWidth()
    {
        return $this::$maxwidth;
    }

    /**
     * how tall this tile can get
     * @return int
     */
    public function getMaxHeight()
    {
        return $this::$maxheight;
    }

    /**
     * X-Y format of this tile
     * @return string
     */
    public function getSize()
    {
        return $this->Width . '-' . $this->Height;
    }

    /**
     * Returns fields related to configuration aspects on this record, e.g. access control.
     * See {@link getCMSFields()} for content-related fields.
     *
     * @return FieldList
     */
    public function getSettingsFields()
    {
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
        ));

        $viewersOptionsSource = array();
        $viewersOptionsSource["Anyone"] = _t('Tile.ACCESSANYONE', "Anyone");
        $viewersOptionsSource["LoggedInUsers"] = _t('Tile.ACCESSLOGGEDIN', "Logged-in users");
        $viewersOptionsSource["OnlyTheseUsers"] = _t('Tile.ACCESSONLYTHESE', "Only these people (choose from list)");
        $viewersOptionsField->setSource($viewersOptionsSource);

        if (!Permission::check('SITETREE_GRANT_ACCESS')) {
            $fields->makeFieldReadonly($viewersOptionsField);
            if ($this->CanViewType == 'OnlyTheseUsers') {
                $fields->makeFieldReadonly($viewerGroupsField);
            } else {
                $fields->removeByName('ViewerGroups');
            }
        }

        return $fields;
    }

    /**
     * render the tile
     * @return type
     */
    public function forTemplate()
    {
        $shortname = (new \ReflectionClass($this))->getShortName();
        return $this->renderWith(array('Tiles/' . $shortname, $shortname));
    }

    /**
     * Returns CSS friendly name
     * @return string
     */
    public function CSSName()
    {
        $shortname = (new \ReflectionClass($this))->getShortName();
        return strtolower($shortname);
    }

    /**
     * Validates the tile data object
     * @return A {@link ValidationResult} object
     */
    public function validate()
    {
        $result = parent::validate();

        if ($this->Height > $this::$maxheight) {
            $result->addError("Height of $this::\$maxheight exceeded" . $this->Height . ' ' . $this::$maxheight);
        }

        if ($this->Width > $this::$maxwidth) {
            $result->addError("Width of $this::\$maxheight exceeded");
        }

        return $result;
    }

    public function canCreate($member = null, $context = array())
    {
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
    public function canView($member = null)
    {

        if (!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
            $member = Security::getCurrentUser();
        }

        // admin override
        if ($member && Permission::checkMember($member, array("ADMIN", "SITETREE_VIEW_ALL"))) {
            return true;
        }
        // Standard mechanism for accepting permission changes from extensions
        $extended = $this->extendedCan('canView', $member);
        if ($extended !== null) {
            return $extended;
        }

        // check for empty spec
        if (!$this->CanViewType || $this->CanViewType == 'Anyone') {
            return true;
        }

        // check for any logged-in users
        if ($this->CanViewType == 'LoggedInUsers' && $member) {
            return true;
        }

        // check for specific groups
        if ($member && is_numeric($member)) {
            $member = DataObject::get_by_id(Member::class, $member);
        }

        if (
            $this->CanViewType == 'OnlyTheseUsers' && $member && $member->inGroups($this->ViewerGroups())
        ) {
            // echo $this->ID."<br>";
            return true;
        }
        return false;
    }

    /**
     * The idea here is that we check for conditions that are not met. if not met we return false
     * This allows us to keep on appending checks
     */
    public function canEdit($member = null)
    {
        if (!$member) {
            $member = Security::getCurrentUser();
        }
        $memberID = ($member instanceof Member) ? $member->ID : $member;

        if (!$memberID) {
            return false;
        }

        // Standard mechanism for accepting permission changes from extensions
        $extended = $this->extendedCan('canEdit', $memberID);
        if ($extended !== null) {
            return $extended;
        }

        // fail if type === Inherit & member cannot edit parent
        if ($this->ParentID  && $this->Parent()->exists()) {
            return DataObject::get_by_id(TileElement::class, $this->ParentID)->canEdit($member);
        }
        // sweet you passed all the checks, proceed
        return true;
    }

    /**
     * get the width of this item (min of 1)
     * @return int
     */
    public function getWidth()
    {
        return max($this->getField('Width'), 1);
    }

    /**
     * get the height of this item (min of 1)
     * @return int
     */
    public function getHeight()
    {
        return max($this->getField('Height'), 1);
    }

    /**
     * takes in position x and y, and saves it
     * @param array $data
     */
    public function writeRawArray($data)
    {
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

    public function isDraft(){
        $draftVersion = Versioned::get_versionnumber_by_stage(Tile::class, Versioned::DRAFT, $this->ID);
        $liveVersion = Versioned::get_versionnumber_by_stage(Tile::class, Versioned::LIVE, $this->ID);

        if ($draftVersion && $draftVersion != $liveVersion) {
            return "Draft";
        } else {
            return "Live";
        }
    }

    /**
     * takes in position x and y, and saves it
     * @param array $data
     */
    public function generateRawArray()
    {
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
            'd' => $this->isDraft(),
            'img' => $this->getPreviewImage(),
            'disabled' => $this->Disabled,
            'canView' => $this->canView(),
            'canEdit' => $this->canEdit(),
        );
    }

    /**
     * if you specify a background color
     * @return string
     */
    public function getTileColor()
    {
        return $this->Color ?: 'transparent';
    }

    /**
     * text to be inside the tile itself
     * @return string
     */
    public function getPreviewContent()
    {
        return DBField::create_field(DBHTMLText::class, $this->Content)->LimitCharacters(150);
    }

    /**
     * a preview image
     * @return Image|null
     */
    public function getPreviewImage()
    {
        return null;
    }

    public function onBeforeWrite()
    {
        // because tiles are not versioned, publish every owning object
        foreach ($this->config()->owns as $owningobject) {
            if (!$this->$owningobject) {
                continue;
            }
            if ($this->$owningobject->hasMethod('publishRecursive')) {
                if ($this->$owningobject->canPublish(Security::getCurrentUser()) && $this->$owningobject->isInDB()) {
                    $this->$owningobject->publishRecursive();
                }
            }
        }
        if (!$this->ID) {
            $this->setTileRowTo9000();
        }
        parent::onBeforeWrite();
    }

    public function setTileRowTo9000()
    {
        $this->Row = 9000;
    }
}
