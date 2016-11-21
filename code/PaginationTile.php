<?php

/**
 * This tile when placed can be linked to a page that contains a left/ right button
 */
class PaginationTile extends Tile {

	protected static $singular_name = "A page that can be placed in serries";
	protected static $allowed_sizes = array(
		'1x1'
	);
	private static $db = array(
		'Title' => 'Text', // text in the content field
		'PageContent' => 'HTMLText',
		'URL' => 'Text'
	);
	private static $has_one = array(
		'Image' => 'Image',
		'PageBanner' => 'Image',
		'Tree' => 'SiteTree'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		//$fields->removeByName('Content');

		$fields->removeByName('TreeID');
		$fields->fieldByName('Root.Main.URL')->setDescription('For external links');
		$fields->fieldByName('Root.Main.PageContent')
				->setDescription('Ensure either an image or page content is set for to create its own page');
		$tree = new TreeDropdownField("TreeID", "Local page to link", "SiteTree");
		$tree->setDescription('Select the same item twice to clear');
		$fields->addFieldToTab('Root.Main', $tree);
		
		$imageupload = new UploadField('Image', 'Upload Image');
		$fields->addFieldToTab('Root.Main', $imageupload);
		$imageupload->setAllowedFileCategories('image');
		$imageupload->setFolderName('tiles/photo');
		$imageupload->setOverwriteWarning(false);
		$imageupload->setAllowedMaxFileNumber(1);
		$imageupload->setDescription('Image that will be displayed on the tile. Required for its own page. 948 pixels wide');
		$imageupload->setFolderName('widgets');

		$pagebanner = new UploadField('PageBanner', 'Upload Page Banner');
		$fields->addFieldToTab('Root.Main', $pagebanner);
		$pagebanner->setAllowedFileCategories('image');
		$pagebanner->setFolderName('tiles/photo');
		$pagebanner->setOverwriteWarning(false);
		$pagebanner->setAllowedMaxFileNumber(1);
		$pagebanner->setDescription('Image displayed on the tile page');
		$pagebanner->setFolderName('widgets');

		
		
		return $fields;
	}

	public function Preview() {
		return $this->PageContent;
	}

	public function getLink() {
		if ($this->ParentObject() && $this->ParentObject()->Page()) {
			if (trim(strip_tags($this->PageContent))) {
				return Controller::join_links(Controller::curr()->Link(), 'tile', '?id=' . $this->ID);
			}
		}

		$tid = $this->TreeID ? : $this->treeid;
		if ($tid && SiteTree::get()->byID($tid)) {
			return Controller::join_links(Director::absoluteBaseURL(), SiteTree::get()->byID($tid)->Link());
		}
		return $this->URL ? : $this->url;
	}
	
	/**
	 * Build a page content up if navigating to the tile page
	 * @return HTML
	 * @throws SS_HTTPResponse_Exception
	 */
	public function getContent() {
		if($this->BypassContent) {
			$items = PaginationTile::get()->filter(array('ParentClassName' => $this->ParentClassName, 'ParentID'=>$this->ParentID, 'Name'=>$this->Name))->toArray();
			
			$selecteditem = null;
			$previtem = null;
			$nextitem = null;
			
			foreach ($items as $key => $item) {
				if((!$item->pagecontent && !$item->PageContent) && !$item->ImageID) {
					unset($items[$key]);
				}
				if($item->URL) {
					unset($items[$key]);
				}
			}
			foreach ($items as $item) {
				if ($selecteditem && !$nextitem) {
					$nextitem = $item;
				}
				if ($item->ID == $this->BypassID) {
					$selecteditem = $item;
				}
				if (!$selecteditem) {
					$previtem = $item;
				}
			}
			if (!$selecteditem) {
				throw new SS_HTTPResponse_Exception('Please set Image or PageContent to create Page.');
			} else {
				$bannerimage = $selecteditem->PageBanner();
				$prev = $previtem ? "tile?id=" . $previtem->ID : "";
				$next = $nextitem ? "tile?id=" . $nextitem->ID : "";
				$cr = '<span class="icon-ChevronRight" aria-hidden="true"></span>';
				$cl = '<span class="icon-ChevronLeft" aria-hidden="true"></span>';

				$links = '<a class="widgettiles__prev" href="' . Director::get_current_page()->Link() . $prev . '">' . ($prev ? $cl : "") . '</a>';
				$mlinks = '<a class="widgettiles__next" href="' . Director::get_current_page()->Link() . $next . '">' . ($next ? $cr : "") . '</a>';
				return '<div class="widgettiles">' . $links . $mlinks . '</div><div class="widgettiles__img">' . ($bannerimage ? $bannerimage->ScaleMaxWidth(948)->getTag() : '') . "</div>" . $selecteditem->PageContent;
			}
		}
		return $this->getField('Content');
	}
	
	public function Menu($Pos) {
		$st = ModelAsController::controller_for(Page::create());
		return $st->getMenu($Pos);
	}

	public function SearchForm() {
		$st = ModelAsController::controller_for(Page::create());
		return $st->SearchForm();
	}

	public function SubNavigationItems() {
		return $this->Controllers[0]->SubNavigationItems();
	}

	public function CustomBreadcrumbs($maxDepth = 20, $unlinked = false, $stopAtPageType = false, $showHidden = false) {
		return $this->Controllers[0]->Breadcrumbs();
	}
	public function SubNavigation() {
		if ($this->SubNavigationItems()->count() === 0) {
			return 'Hide navigation';
		}
		return '';
	}
	

}
