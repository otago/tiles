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

