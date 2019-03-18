<?php

namespace OP\Elements;

use SilverStripe\ORM\ArrayList;
use DNADesign\Elemental\Models\BaseElement;
use OP\Fields\TileField;
use OP\Models\Tile;

class TileElement extends BaseElement {

    private static $singular_name = 'Tile';
    private static $plural_name = 'Tiles';
    private static $description = 'creates a grid of tiles';
    private static $db = [
        'Rows' => 'Int'
    ];
    private static $has_many = [
        'Tiles' => Tile::class
    ];
    private static $owns = [
        'Tiles'
    ];
    private static $table_name = 'TileElement';

    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Rows', 'Tiles']);

        $tilefield = TileField::create('Tiles', 'Tiles', $this->Tiles(), null, $this);
        if (!$this->ID) {
            $tilefield->setDisabled(true);
            $tilefield->setDescription('Please save to begin editing');
        }
        $fields->addFieldToTab('Root.Main', $tilefield);
        return $fields;
    }

    public function getType() {
        return 'Tiles';
    }
    
    public function SortedTiles () {
        $retarray = [];
        
        foreach ($this->Tiles() as $tile) {
            $sort = ($tile->Row * $this->Rows) + $tile->Col;
            $tile->Sort = $sort;
            $retarray[$sort] = $tile;
        }
        
        return ArrayList::create($retarray)->Sort('Sort');
    }

}
