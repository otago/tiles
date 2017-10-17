<?php


/**
* The old paginationTile two functions: one for a pesudo 'page' and a link. 
* this splits out the two tiles to make things easier and more explict.
*/
class FixOldTileTask extends BuildTask {
	public function run($request) {
		$paginationTiles = PaginationTile::get();
		
		foreach($paginationTiles as $tile) {
			if($tile->TreeID || $tile->URL) {
				debug::Show('fixing' . $tile->ID);
				$linktile = LinkTile::create();
				$linktile->Row = $tile->Row;
				$linktile->Col = $tile-> Col;
				$linktile->Sort = $tile-> Sort;
				$linktile->Size = $tile-> Size;
				$linktile->Name = $tile-> Name;
				$linktile->ParentID = $tile-> ParentID;
				$linktile->Disabled = $tile-> Disabled;
				$linktile->CanViewType = $tile-> CanViewType;
				$linktile->CanEditType = $tile-> CanEditType;
				$linktile->Version = $tile-> Version;
				$linktile->ParentClassName = $tile-> ParentClassName;
				$linktile->Title = $tile-> Title;
				$linktile->URL = $tile-> URL;
				$linktile->ImageID = $tile-> ImageID;
				$linktile->TreeID = $tile-> TreeID;
				$linktile->write();
				$tile->delete();
			}
		}
	}
}
