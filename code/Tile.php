<?php

/**
 * 
 */
class Tile extends DataObject {

	protected static $allowed_sizes = array(
		'1x1', '1x2', '1x3', '2x1', '2x2', '2x3', '3x1', '3x2', '3x3'
	);
	protected static $singular_name = "Generic Tile";
	private static $db = array(
		'Color' => 'Text', // red, green blue etc.
		'Content' => 'HTMLText', // text in the content field
		'Row' => 'Int',
		'Col' => 'Int',
		'Sort' => 'Int', // calculated by TileField
		'Size' => 'Text', // 1x1 etc. Should be a value from $allowed_sizes
		'Name' => 'Text', // used in one-many relationships
		'ParentID' => 'Int', // danger stupid hack
		'Disabled' => 'Boolean',
		'CanViewType' => "Enum('Anyone, LoggedInUsers, OnlyTheseUsers, Inherit', 'Inherit')",
		'CanEditType' => "Enum('LoggedInUsers, OnlyTheseUsers, Inherit', 'Inherit')",
		'Version' => "Enum('Stage, Live', 'Stage')", // only used if the parent is versioned
		'ParentClassName' => 'Text'
	);
	private static $has_one = array(
		'ParentHolder' => 'SiteTree' // not used anymore
	);
	private static $many_many = array(
		'ViewerGroups' => 'Group',
		'EditorGroups' => 'Group',
	);
	private static $defaults = array(
		'CanViewType' => 'Inherit',
		'CanEditType' => 'Inherit'
	);

	public function __construct($record = null, $isSingleton = false, $model = null) {
		parent::__construct($record, $isSingleton, $model);
	}

	public function populateDefaults() {
		$this->Size = current($this::$allowed_sizes);

		parent::populateDefaults();
	}

	/**
	 * this will allow the tile to be parented to data objects too
	 * @param int $val
	 */
	public function setParentID($val) {
		$this->setField('ParentHolderID', $val);
		$this->setField('ParentID', $val);
	}

	/**
	 * @returns a nice tanem
	 */
	public static function functionGetNiceName() {
		return static::$singular_name;
	}

	/**
	 * create the field names 
	 * @return \FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('ParentID');
		$fields->removeByName('Color');
		$fields->removeByName('Row');
		$fields->removeByName('Col');
		$fields->removeByName('Sort');
		$fields->removeByName('Name');
		$fields->removeByName('ParentHolderID');
		$fields->removeByName('ViewerGroups');
		$fields->removeByName('EditorGroups');
		$fields->removeByName('SliderTileID');
		$fields->removeByName('ParentClassName');
		

		$parent = Tile::get()->byID($this->ParentID);

		if (isset($parent) && ($parent->ClassName == 'SliderTile')) {
			$fields->addFieldToTab('Root.Main', DropdownField::create('Size', 'Size', array($parent->Size => $parent->Size)), 'Content');
		} else {
			$fields->addFieldToTab('Root.Main', DropdownField::create('Size', 'Size', array_combine($this::$allowed_sizes, $this::$allowed_sizes)), 'Content');
		}

		if(class_exists('OpColorField')) {
			$fields->addFieldToTab('Root.Main', OpColorField::create('Color', 'Color Override', $this->Color), 'Content');
		}
		$fields->addFieldsToTab('Root.Main', CheckboxField::create('Disabled', 'Disabled', true), 'Content');

		$fields->addFieldsToTab('Root.Settings', $this->getSettingsFields());

		return $fields;
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
			->setMultiple(true)
			->setSource($groupsMap)
			->setAttribute(
			'data-placeholder', _t('Tile.GroupPlaceholder', 'Click to select group')
			),
			$editorsOptionsField = new OptionsetField(
			"CanEditType", _t('Tile.EDITHEADER', "Who can edit this tile?")
			),
			$editorGroupsField = ListboxField::create("EditorGroups", _t('SiteTree.EDITORGROUPS', "Editor Groups"))
			->setMultiple(true)
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
		return $this->renderWith('Layout/'.$this->ClassName, $this->ClassName);
	}

	public function getSizex() {
		$sizes = explode('x', $this->Size);
		return $sizes[0];
	}

	public function getSizey() {
		$sizes = explode('x', $this->Size);
		return $sizes[1];
	}

	/**
	 * Returns CSS friendly name
	 * @return string
	 */
	public function CSSName() {
		return strtolower($this->ClassName);
	}

	/**
	 * Returns a hex color so we can shade the background of each tile in the CMS
	 * @return string hex code
	 */
	public function PreviewColor() {
		$color = ColourSchemes::get()->filter(array('CSSColor' => $this->Color));

		if (!$color || $color->count() == 0) {
			return '';
		}

		return $color->first()->CSSHex;
	}

	/**
	 * Validates the tile data object
	 * @return A {@link ValidationResult} object
	 */
	public function validate() {
		$result = parent::validate();

		//Ensure that $this::allowed_sizes is in Tile::allowed_sizes
		if (array_diff($this::$allowed_sizes, Tile::$allowed_sizes)) {
			$result->error("Tile size inside $this::\$allowed_sizes does not exist in Tile::\$allowed_sizes");
		}

		//Ensure that $this->Size is in $this::$allowed_sizes
		if (!in_array($this->Size, $this::$allowed_sizes)) {
			$result->error('Tile size is not an allowed size');
		}

		return $result;
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
			$member = Member::currentUserID();
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
			if(!$this->ParentID) {
				return true;
			}
			if (in_array ($this->ParentClassName, ClassInfo::getValidSubClasses())) {
				return DataObject::get_by_id('SiteTree', $this->ParentID)->canView();
			} else {
				if(!$this->ParentClassName || !singleton($this->ParentClassName)) {
					return true;
				}
				return singleton($this->ParentClassName)->canView($member);
			}
		}

		// check for any logged-in users
		if ($this->CanViewType == 'LoggedInUsers' && $member) {
			return true;
		}

		// check for specific groups
		if ($member && is_numeric($member))
			$member = DataObject::get_by_id('Member', $member);
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
			$memberID = Member::currentUserID();
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
			if(!$this->ParentID) {
				return true;
			}
			if (in_array ($this->ParentClassName, ClassInfo::getValidSubClasses())) {
				return DataObject::get_by_id('SiteTree', $this->ParentID)->canEdit();
			} else {
				if(!$this->ParentClassName || !singleton($this->ParentClassName)) {
					return true;
				}
				return singleton($this->ParentClassName)->canEdit($member);
			}
		}

		// check for any logged-in users
		if ($this->CanEditType == 'LoggedInUsers' && $member) {
			return true;
		}

		// check for specific groups
		if ($member && is_numeric($member)) {
			$member = DataObject::get_by_id('Member', $member);
		}
		if ($this->CanEditType == 'OnlyTheseUsers' && $member && $member->inGroups($this->ViewerGroups())) {
			return true;
		}

		return false;
	}

	public function AllowedSizes() {
		return $this::$allowed_sizes;
	}

	/**
	 * If this object is versioned
	 * @return type
	 */
	public function isVersioned() {
		$parentClass = $this->ParentClassName;
		if($parentClass) {
			$parent = $parentClass::get()->byId($this->ParentID);
			return $parent ? $parent->has_extension('Versioned') : false;
		}
		return false;
	}
	
	public function ParentObject () {
		$parentClass = $this->ParentClassName;
		return $parentClass::get()->ByID($this->ParentID);
	}

}
