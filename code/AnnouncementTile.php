<?php


/**
 * just some content
 */
class AnnouncementTile extends Tile {
    protected static $allowed_sizes = array('1x1');
    
	protected static $singular_name = "Announcement tile";
    
    private static $db = array(
        'StartDate' => 'Date',
		'EndDate' => 'Date',
        'Type' => 'Text'
	);
	
	public function getCMSFields() {
        $fields = parent::getCMSFields();
        
        $typeArray = array('icon-Exclamation'=>'Exclamation', 'icon-InfoSign'=>'Information', 'icon-Question'=>'Question');
        
        $fields->addFieldToTab('Root.Main', new DropdownField('Type','Type',$typeArray), 'Content');
        
        $startDate = new DateField('StartDate', 'Start Date', $this->StartDate); 
        $startDate->setConfig('showcalendar', true);
        
        $endDate = new DateField('EndDate', 'End Date', $this->EndDate);
        $endDate->setConfig('showcalendar', true);
        
        $fields->addFieldsToTab('Root.Main', array($startDate,$endDate),'Content');
        
		return $fields;
	}
    
    public function ShowTile(){
        $today = date('Y-m-d');
        $todayDate=date('Y-m-d', strtotime($today));
        
        $announcementDateBegin = date('Y-m-d', strtotime($this->StartDate));
        $announcementDateEnd = date('Y-m-d', strtotime($this->EndDate));
        
        if(($todayDate >= $announcementDateBegin) && ($todayDate <= $announcementDateEnd)) {
            return true;
        } else {
            $this->Disabled = true;
            $this->write();
            return false;
        }
        
    }

    public function Preview() {
		return $this->Content;
	}
}