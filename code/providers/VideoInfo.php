<?php
/**
 * Data object for video information
 * @see {@link VideoProvider}
 */
class VideoInfo extends DataObject {
    
    public static $db = array(
        'VideoID' => 'Varchar(10)',
        'Title' => 'Text',
        'URL' => 'Text',
        'ThumbURL' => 'Text',
        'ProviderClass' => 'Varchar(50)'
    );
    
    /**
     * Don't create this data object in the database
     */
    public function requireTable() { 
        DB::dontRequireTable($this->class); 
    }
    
    /**
     * Download the highest resoultion thumbnail from the video provider
     * @return {@link Image} Image object
     */
    public function downloadThumb() {
        if($this->ThumbURL) {
            $folder = Folder::find_or_make('tiles/video'); 
            $file = $this->VideoID . '.jpg';
            
            //Fetch the image
            $data = VideoProvider::fetch($this->ThumbURL);
                
            //Write the image to disk
            $f = fopen($folder->getFullPath() . $file, "wb"); 
			fwrite($f, $data); 
			fclose($f);

            //Add the image to SilverStripe
            $image = new Image();
            $image->Filename = $folder->getRelativePath() . $file;
            $image->Title = $file;
            $image->ParentID = $folder->ID;

            return $image;
        }
        
        return null;
    }  
}
