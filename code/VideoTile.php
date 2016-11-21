<?php
/**
 * Video tile
 */
class VideoTile extends Tile {
	private static $db = array(
        'Title' => 'Text',
        'SourceURL' => 'Text',
		'EmbedURL' => 'Text',
        'ViewOptions' => "Enum('OpenInNewTab, OpenInTile, OpenInLightbox')"
	);
    
    private static $has_one  = array(
        'Thumb' => 'Image'    
    );
	
	protected static $allowed_sizes = array('1x1', '2x2');
	
	protected static $singular_name = "Video tile";
    
    private static $video_scrapers = array(
        'YoutubeVideoProvider',
        'VimeoVideoProvider'
    );
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('Content');
        $fields->removeByName('Color');
        $fields->removeByName('EmbedURL');
        
        if($this->Title) {
            $fields->makeFieldReadonly('Title');  
        } else {
            $fields->removeByName('Title');
        }
        
        $fields->addFieldToTab('Root.Main', $sourceURL = new TextField('SourceURL', 'Source URL', $this->SourceURL));
        
        $sourceURL->setDescription("
            Example links: <br/>
            Youtube: <a href='http://www.youtube.com/watch?v=DgVmXfcVGxI' target='_blank'>
                http://www.youtube.com/watch?v=DgVmXfcVGxI</a> </br>
            Vimeo: <a href='http://vimeo.com/52657916' target='_blank'>http://vimeo.com/52657916</a>
        ");
        
        $fields->addFieldToTab('Root.Main', new CheckboxField('ScrapeImage', 'Scrape video thumb from source URL'));
        
        $viewOptionsLabels = array();
        $viewOptionsLabels['OpenInNewTab'] = 'Open in new tab';
        $viewOptionsLabels['OpenInTile'] = 'Open in tile';
        $viewOptionsLabels['OpenInLightbox'] = 'Open in lightbox';
        
        $fields->addFieldToTab('Root.Main', $viewOptions = new DropdownField('ViewOptions', 'View Options', 
            $viewOptionsLabels, $this->ViewOptions));
        
        //Show video preview if valid video exists
        if($this->EmbedURL) {
            $fields->addFieldToTab('Root.Main', new LiteralField('VideoPreview', $this->getVideoHTMLCode()));
        }
        
        
        $fields->addFieldToTab('Root.Main', $thumb = new UploadField('Thumb', 'Thumb'));
        $thumb->setAllowedFileCategories('image');
        $thumb->setFolderName('tiles/video');
        $thumb->setOverwriteWarning(false);
        $thumb->setAllowedMaxFileNumber(1);
        
		return $fields;
	}
	
	public function Preview() {
		//return $this->ThumbID ? $this->Thumb() : "";
        return "<img height='" . ($this->getSizey() * 210) . "' src='" . $this->Thumb()->Link() . "' />";
	}
    
    private function getVideoHTMLCode() {
        return "
            <div class='field' id='VideoPreview'>
	            <label for='Form_ItemEditForm_VideoPreview' class='left'>Video Preview</label>
	            <div class='middleColumn'>
		            <iframe id='Form_ItemEditForm_VideoPreview' width='560' height='315' src='" . $this->EmbedURL . "
                    ' frameborder='0' allowfullscreen></iframe>
	            </div>	
            </div>
            ";
    }
    
    public function onBeforeWrite() {
        parent::onBeforeWrite();
        
        $provider = $this->ProviderClass;
        
        if(!empty($provider)) {
            $info = $provider::get_video_info($this->getField('SourceURL'));
            
            if(get_class($info) !== 'VideoInfo') {
                user_error(get_class($provider) . "::get_video_info does not return a VideoInfo object", E_USER_ERROR);    
            }
            
            $this->Title = $info->Title;
            $this->EmbedURL = $info->URL; 
            
            if(Controller::curr()->getRequest()->requestVar('ScrapeImage')) {
                $thumb = $info->downloadThumb();
                
                if(!is_null($thumb)) {
                    $thumb->Title = $info->Title;
                    $thumb->write();
                    
                    $this->ThumbID = $thumb->ID;
                }
            }
        }
    }
    
    /**
     * Validates the video tile data object
     * @return A {@link ValidationResult} object
     */
    public function validate() {
        $result = parent::validate();
        
        if(!$this::$video_scrapers || !is_array($this::$video_scrapers)) {
            user_error("Expected array of video providers in VideoTile::\$video_providers", E_USER_ERROR);
        }
        
        if($this->SourceURL && $this->isChanged('SourceURL')) {
            $valid = false;
            
            //Check that the SourceURL is a valid URL
            if(!filter_var($this->getField('SourceURL'), FILTER_VALIDATE_URL)) {
                $result->error('The source URL is not a valid link');
            }
            
            //Check if the SourceURL matches a provider
            foreach($this::$video_scrapers as $provider) {
                
                //Check that the video providers are valid
                if(!is_subclass_of($provider, 'VideoProvider')) {
                    user_error(get_class($provider) . " does not extend from VideoProvider", E_USER_ERROR);
                }
                
                //If we have found a match
                if($provider::is_provider($this->getField('SourceURL'))) {
                    $this->setField('ProviderClass', $provider);
                
                    $valid = true;
                }
            }
            
            if(!$valid) {
                $result->error('The source URL does not link to a supported video provider');
            }
        }
        
        return $result;
    }
}

