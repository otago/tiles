<?php

/**
 * 
 * Creates a reorderable 2D tile field. For example:
 * <code>
 *   static $db = array(
 * 		'Tiles' => 'Text'
 * 	);
 *   
 * 	function getCMSFields() {
 * 		$fields = parent::getCMSFields();
 * 		$fields->addFieldToTab('Root.Main', TileField::create('Tiles', 'Tiles'));
 *  }
 * </code>
 */
class TileField extends TextField {

	/**
	 * The datasource
	 * @var SS_List
	 */
	protected $list = null;

	/**
	 * Sets the maximum number of columns to render in the TileField
	 * @var int
	 */
	protected $maxColumns = 4;
	private static $allowed_actions = array(
		'addTile',
		'editTile',
		'removeTile',
		'saveTiles', // saving the Col/Row positions via AJAX
		'item'   // gridfield actions
	);

	/**
	 * include the required js/css
	 * @param type $name
	 * @param type $title
	 * @param SS_List $dataList
	 * @param type $value
	 * @param type $form
	 * @param type $emptyString
	 */
	public function __construct($name, $title = null, SS_List $dataList = null, $value = '', $form = null, $emptyString = null) {
		parent::__construct($name, $title, $dataList, $value, $form, $emptyString);
		Requirements::css(TILEWORKINGFOLDER . '/css/TileField.css');
		Requirements::javascript(TILEWORKINGFOLDER . '/javascript/jquery.gridster.min.js');
		Requirements::javascript(TILEWORKINGFOLDER . '/javascript/tilefield.js');
		Requirements::javascript(TILEWORKINGFOLDER . '/javascript/customtile.js');
		$this->list = $dataList;
	}

	/**
	 * remote a tile given an ID
	 * @param SS_HTTPRequest $request
	 */
	public function removeTile(SS_HTTPRequest $request) {
		$vars = $request->requestVars();
		if (!isset($vars['id'])) {
			header("HTTP/1.0 500 Internal Server Error");
			throw new SS_HTTPResponse_Exception('ID not specified');
		}
		$removedtile = Tile::get()->byID((int) $vars['id']);
		$removedtile->delete();
	}

	/**
	 * we ajax changes in the tile ordering system.
	 * @param SS_HTTPRequest $request
	 */
	public function saveTiles(SS_HTTPRequest $request) {
		$vars = $request->requestVars();
		if (!isset($vars['order'])) {
			header("HTTP/1.0 500 Internal Server Error");
			throw new SS_HTTPResponse_Exception('Order not specified');
		}

		$order = json_decode($vars['order']);

		// strip invalid tiles
		foreach ($order as $key => $tile) {
			if (!isset($tile->id) || !isset($tile->col) || !isset($tile->row)) {
				unset($order[$key]);
			}
		}

		// used to calculate the sorting int. Useful for stacking tile frameworks.
		$Maxcols = 1;
		foreach ($order as $tile) {
			if ($tile->col > $Maxcols) {
				$Maxcols = $tile->col;
			}
		}

		foreach ($order as $do) {
			$tile = Tile::get()->byID((int) $do->id);
			$tile->Row = (int) $do->row;
			$tile->Col = (int) $do->col;
			$tile->Sort = ($tile->Col) + ($Maxcols * ($tile->Row));
			$tile->write();
		}
	}

	/**
	 * add a tile to the field
	 * @param SS_HTTPRequest $request
	 */
	public function addTile(SS_HTTPRequest $request) {
		$vars = $request->requestVars();

		if (!isset($vars['type'])) {
			throw new SS_HTTPResponse_Exception('Tile Type not specified', 500);
		}

		$newdo = $vars['type']::create();
		if (!($newdo instanceof Tile)) {
			throw new SS_HTTPResponse_Exception('New DataObject must be tile object', 500);
		}

		// create the new tile
		$newdo->Name = $this->name;
		$newdo->ParentID = $this->form->getRecord()->ID;
		$newdo->ParentClassName = $this->form->getRecord()->ClassName;

		if (!($this->form->getRecord() instanceof SiteTree)) {
			$newdo->ParentHolderID = $this->form->getRecord()->ParentID;
		}
		$newdo->Row = 1;
		$newdo->Col = 1;
		$newdo->write();
		$newdo->HTML = $newdo->forTemplate()->getValue();
		$newdo->sizex = $newdo->getSizex();
		$newdo->sizey = $newdo->getSizey();
		$newdo->Version = 'Stage';
		$newdo->isversioned = $newdo->isVersioned();

		// return the new object to insert into gridfield
		return(print_r(json_encode($newdo->toMap()), true));
	}

	/**
	 * returns a list of palatable dataobjects used for the template render
	 * @return \ArrayList
	 */
	public function Options() {
		return Tile::get()->filter(array('ParentID' => $this->form->getRecord()->ID, 'Name' => $this->name));
	}

	/**
	 * Creates a rendered Programme Crawler Field using the .ss template
	 * @param type $properties an array of values to decorate the field
	 * @return type a rendered template
	 */
	function Field($properties = array()) {
		$obj = ($properties) ? $this->customise($properties) : $this;
		$obj->Options = $this->Options();
		$tmp = $obj->renderWith("TileField");
		return $tmp;
	}

	/**
	 * return the url so we can reference it in the javascript
	 * @return array
	 */
	public function getAttributes() {
		$attrs = parent::getAttributes();
		$attrs['type'] = 'hidden';
		$attrs['data-url'] = $this->Link();
		$attrs['data-editlink'] = Controller::join_links($this->Link('item'), 'DOID', 'edit');

		return $attrs;
	}

	/**
	 * used to handle editTitle sub urls
	 * @param SS_HTTPRequest $request
	 * @param DataModel $model
	 * @return type
	 */
	public function handleRequest(SS_HTTPRequest $request, DataModel $model) {
		if (strpos($request->remaining(), 'editTile/') === 0) {
			// need to return a RequestHandler
			return $this->handleGridRequest($request);
		}

		if (strpos($request->remaining(), 'item/') === 0) {
			// need to return a RequestHandler
			return $this->handleGridRequest($request);
		}

		return parent::handleRequest($request, $model);
	}

	/**
	 * feed the request into the grid field controller.
	 * @param SS_HTTPRequest $request
	 * @return html
	 */
	public function handleGridRequest(SS_HTTPRequest $request) {
		$action = $request->shift();
		$id = $request->shift();

		$record = Tile::get()->byID((int) $id);

		$requestHandler = $this->form->getController();

		$gridField = GridField::create($this->name, $this->title, $this->Options());
		$gridField->setForm($this->form);
		$class = 'GridFieldDetailForm_ItemRequest';
		$tilefieldeditor = new TileFieldDetailForm($action);
		$tilefieldeditor->setFields($record->getCMSFields());
		$handler = Object::create($class, $gridField, $tilefieldeditor, $record, $requestHandler, $this->name);
		$handler->setTemplate('GridFieldDetailForm');

		// if no validator has been set on the GridField and the record has a
		// CMS validator, use that.
		if (method_exists($record, 'getCMSValidator')) {
			$this->setValidator($record->getCMSValidator());
		}

		return $handler->handleRequest($request, DataModel::inst());
	}

	/**
	 * get file types
	 * @return \ArrayList
	 */
	public function getTileTypes() {
		if($this->list) {
			return $this->list;
		}
		$Arraylist = ArrayList::create();

		$dataClasses = ClassInfo::subclassesFor('Tile');
		foreach ($dataClasses as $key => $item) {
			if ($key == "Tile")
				continue;
			$do = DataObject::create();
			$do->Name = $item;
			$do->NiceName = $item::functionGetNiceName();

			$Arraylist->push($do);
		}
		// todo: fix this shit
		//$myClass = $this->form->getRecord()->ClassName;

		if (!($this->form->getRecord() instanceof SiteTree)) {
			$Arraylist = $this->filterList($Arraylist);
		}

		return $Arraylist;
	}

	/**
	 * I can't tell if this is needed
	 * @param type $request
	 * @return type
	 */
	public function item($request) {
		$id = $request->shift();
		$action = $request->shift();
		$action2 = $request->shift();
		$action3 = $request->shift();

		$record = Tile::get()->byID((int) $id);

		$formfields = $record->getCMSFields();
		$field = $formfields->dataFields();
		$field = $field['Images'];

		$requestHandler = $this->form->getController();

		$gridField = GridField::create($this->name, $this->title, $this->Options());
		$gridField->setForm($this->form);
		$class = 'GridFieldDetailForm_ItemRequest';
		$tilefieldeditor = new TileFieldDetailForm();
		$tilefieldeditor->setFields($record->getCMSFields());
		$handler = Object::create($class, $gridField, $tilefieldeditor, $record, $requestHandler, $this->name);
		$handler->setTemplate('GridFieldDetailForm');

		// if no validator has been set on the GridField and the record has a
		// CMS validator, use that.
		if (method_exists($record, 'getCMSValidator')) {
			$this->setValidator($record->getCMSValidator());
		}

		return $handler->handleRequest($request, DataModel::inst());
	}

	/**
	 * Set the maximum column count allowed for this TileField
	 * @param mixed $to 
	 */
	public function getMaxColumns() {
		return 4;
	}

	/**
	 * Set the maximum column count allowed for this TileField
	 * @param mixed $to 
	 */
	public function setMaxColumns($to) {
		$this->maxColumns = $to;
	}

	/**
	 * Filter list for tiles in dropdown depending on allowed tiles and allowed sizes
	 * At the moment have to save to filter list
	 * @param $Arraylist 
	 */
	public function filterList($Arraylist) {
		//hardcoded for now will
		//not allowed tiles
		$sliderArray = array('SliderTile', 'GalleryTile', 'FeedbackTile', 'FeaturedVideosTile', 'FacebookTile', 'MoodleTile');
		$sidebarArray = array('FeedbackTile', 'FeaturedVideosTile');

		$myClass = $this->form->getRecord()->ClassName;
		$mysize = $this->form->getRecord()->Size;


		if ($myClass === 'SliderTile') {
			$checkArray = $sliderArray;
		} else {
			$checkArray = $sidebarArray;
		}

		foreach ($Arraylist as $tile) {
			if (in_array($tile->Name, $checkArray)) {
				$Arraylist->remove($tile);
			}
		}

		if ($myClass === 'SliderTile') {
			foreach ($Arraylist as $tile) {
				$myTile = Tile::get()->filter(array('ClassName' => $tile->Name))->first();

				if (!$myTile) {
					continue;
				}

				$sizeArray = $myTile->AllowedSizes();

				if (isset($sizeArray) && !(in_array($mysize, $sizeArray))) {
					$Arraylist->remove($tile);
				}
			}
		}

		return $Arraylist;
	}

	/**
	 * Publish this record if the parent is using versioning
	 * @param DataObjectInterface $record
	 */
	public function saveInto(DataObjectInterface $record) {
		if ($record->has_extension('Versioned')) {
			if (Controller::curr()->getRequest()->requestVar('action_publish') === "1") {
				foreach ($this->Options() as $option) {
					$option->Version = 'Live';
					$option->write();
				}
			}
		}
		parent::saveInto($record);
	}

}

/**
 * Used to fake the gridfieldeditor
 */
class TileFieldDetailForm extends GridFieldDetailForm {

	/**
	 * @return Validator
	 */
	public function getValidator() {
		return new RequiredFields(array());
	}

}
