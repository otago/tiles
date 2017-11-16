# Tiling system for SilverStripe CMS

Allows you to create a grid of items inside the CMS. Has the ability to:

1. drag & reorder them inside the CMS
2. easily create your own tiles
3. delete and edit tiles easily

![display of what the tiles look like inside SilverStripe](images/1.png)

# Install 

```
$composer require otago/tiles
```

# Usage

```
class MyPage extends Page {

	static $has_many = array(
		'Tiles' => 'Tile'
	);

	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Main', TileField::create('Tiles', 'Tiles'));
		return $fields;
	}
}
```

## specifying types of tiles in field

You can limit the CMS dropdown to a limited number of tiles. This is handy when you've have a page where you only want a certain type of tile. This is done by passing in the $dataList parameter: 

```
		$tile = DataObject::create(array('Name'=>'StaffHubResourceTile', 'NiceName' => StaffHubResourceTile::functionGetNiceName()));
		
		$fields->addFieldToTab('Root.Main', TileField::create('Tiles', 'Tiles', ArrayList::create(array($tile))));
```
