<?php

namespace OP;

use SilverStripe\Forms\FormField;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\View\Requirements;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\SS_List;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridFieldDetailForm;

/**
 * 
 * Creates a reorderable 2D tile field. For example:
 * <code>
 *  private static $has_many = [
 *     'Tiles' => Tile::class
 *  ];
 *  private static $owns = [
 *      'Tiles'
 *  ];
 *   
 * 	function getCMSFields() {
 *      $fields = parent::getCMSFields();
 *      $fields->addFieldToTab('Root.Main', TileField::create('Tiles', 'Tiles', $this->Tiles()));
 *  }
 * </code>
 */
class TileField extends GridField {

	private static $allowed_actions = array(
		'delete'
	);
	private static $url_handlers = [
		'delete/$Action' => 'delete'
	];
	
    protected $widthholder = null;
    protected $defaultrows = 4;

	/**
	 * The list of avalible items to select from
	 * @var SS_List
	 */
	protected $selectionlist = null;

	/**
	 * include the required js/css
	 * @param string $name of field
	 * @param string $title the fancy title
	 * @param SS_List $dataList list of tile objects
	 * @param SS_List $selectionlist if you want to restrict the types of tiles. if null it will display all
	 * @param DataObject $widthholder - if set, this object will be saved with the number of rows
	 */
	public function __construct($name, $title = null, SS_List $dataList = null, $selectionlist = null, DataObject $widthholder = null) {
		$this->selectionlist = $selectionlist;
		$this->widthholder = $widthholder;

		// set the default tile type
		$conf = new GridFieldConfig_RecordEditor();
		$conf->removeComponentsByType(new GridFieldDetailForm());
		$conf->addComponent(new TileFieldDetailForm('DetailForm', Tile::class));

		parent::__construct($name, $title, $dataList, $conf);

		// style and react js
		Requirements::css('otago/tiles: css/TileField.css');
		Requirements::javascript('otago/tiles: client/dist/js/bundle.js');
		Requirements::css('symbiote/silverstripe-gridfieldextensions:css/GridFieldExtensions.css');
		Requirements::javascript('symbiote/silverstripe-gridfieldextensions:javascript/GridFieldExtensions.js');
	}

	/**
	 * handles a delete request via ajax
	 * @param HTTPRequest $request
	 * @return HTTP
	 * @throws \SilverStripe\Control\HTTPResponse_Exception
	 */
	public function delete(HTTPRequest $request) {
		$tileid = $request->latestParam('Action');
		$mytile = Tile::get()->byID($tileid);
		if ($mytile) {
			$mytile->delete();
			return \SilverStripe\Control\HTTPResponse::create('Tile with ID ' . $tileid . ' has been deleted');
		}
		throw new \SilverStripe\Control\HTTPResponse_Exception('failed to find Tile', 400 );
	}

	/**
	 * override the gridfield render method. we want to render using TileField.ss
	 * @param type $properties
	 * @return type
	 */
	public function FieldHolder($properties = array()) {
		return FormField::FieldHolder($properties);
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
		$tmp = $obj->renderWith('TileField');
		return $tmp;
	}

	/**
	 * will return the link that is used to create new tile objects
	 * @return string url
	 */
	public function getAddURL() {
		return Controller::join_links($this->Link(), 'item', 'new');
	}

	/**
	 * will return the link that is used to edit tile objects
	 * @return string url
	 */
	public function getEditURL() {
		return Controller::join_links($this->Link(), 'item', 'ID', 'edit');
	}

	/**
	 * will return the link that is used to remove tile objects
	 * @return string url
	 */
	public function getDeleteURL() {
		return Controller::join_links($this->Link(), 'delete', 'ID');
	}

	/**
	 * we intercept the request to figure out what tile class we're using. we then
	 * inform the parent gridfield through a custom component (TileFieldDetailForm)
	 * @param HTTPRequest $request
	 * @return html
	 */
	public function handleRequest(HTTPRequest $request) {
		if ($request->requestVar('TileType')) {
			$record = Injector::inst()->create(str_replace('_', '\\', $request->requestVar('TileType')));
			$this->setModelClass($record->ClassName);

			// replace TileFieldDetailForm
			$config = $this->getConfig();
			$config->removeComponentsByType(new TileFieldDetailForm());
			$config->addComponent(new TileFieldDetailForm('', $record->ClassName));
			$this->setConfig($config);
		}

		return parent::handleRequest($request);
	}

	/**
	 * get file types
	 * @return \ArrayList
	 */
	public function getTileTypes() {
		if ($this->selectionlist) {
			return $this->selectionlist;
		}
		$Arraylist = ArrayList::create();

		$dataClasses = ClassInfo::subclassesFor('OP\Tile');

		foreach ($dataClasses as $key => $item) {
			if ($key == "Tile") {
				continue;
			}
			$do = DataObject::create();
			$do->Name = $item;
			$do->NiceName = $item::create()->singular_name();

			$Arraylist->push($do);
		}

		return $Arraylist;
	}

	/**
	 * tiles we're allowed to create
	 * @return json
	 */
	public function getTileTypesJson() {
		$retarray = array();
		foreach ($this->getTileTypes() as $item) {
			$retarray[] = array(
				'title' => $item->Name,
				'name' => $item->NiceName,
			);
		}

		return json_encode($retarray);
	}
	
	/**
	 * return the number of rows 
	 * @return int
	 */
	public function getRows () {
		if ($this->widthholder){
			return $this->widthholder->Rows ?: $this->defaultrows;
		}
		return $this->defaultrows;
	}

	/**
	 * a list of items inside this area. mapped to react-grid-layout
	 * @return json
	 */
	public function getDataListJson() {
		$retarray = array();
		foreach ($this->list as $item) {
			if ($item instanceof DataObject && $item->hasMethod('generateRawArray')) {
				$retarray[] = $item->generateRawArray();
			}
		}

		return json_encode($retarray);
	}

	/**
	 * saving the grid structure via clicking 'save'
	 * @param type $value
	 * @param type $data
	 * @return $this
	 */
	public function setValue($value, $data = null) {
		parent::setValue($value, $data);

		if (is_array($data) && array_key_exists ($this->name, $data) && array_key_exists ('GridLayout', $data[$this->name])) {
			if ( isset($data[$this->name]['GridLayout'])) {
				$updatedtiles = $data[$this->name]['GridLayout'];
				foreach ($updatedtiles as $id => $arraydata) {
					$tile = Tile::get()->byID($id);
					if ($tile) {
						$tile->writeRawArray($arraydata);
					}
				}
			}
			
			// write the number of rows to the parent object
			if ( isset($data[$this->name]['Rows'])) {
				$rows = $data[$this->name]['Rows'];
				if(is_numeric($rows)) {
					if ($this->widthholder){
						$this->widthholder->Rows = $rows;
						$this->widthholder->write();
					}
				}
			}
		}
		
		return $this;
	}
	
	public function RowsEnabled() {
		return $this->widthholder instanceof DataObject;
	}

}
