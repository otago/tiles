<?php
class YoutubeVideoProvider extends VideoProvider {
    
    /**
     * The name of the video provider
     * @var string
     */
    public static $name = "Youtube";
    
    /**
     * Determine if a URL matches the video provider
     * @param string $url Video URL to check
     * @return bool TRUE if the URL matches the provider
     */
    public static function is_provider($url) {
        if (strpos($url, 'youtube.com'))
            return true;
        
        return false;
    }
    
    /**
     * Connects to the video provider's API and obtain information on the
     * given URL
     * @param string $url Video URL to get information for
     * @return
     */
    public static function get_video_info($url) {
        //Setup return array
        $model = new VideoInfo();
        
        //Find video ID
        $id = parse_url($url);
        parse_str($id['query'], $output);
        $model->VideoID = $output['v'];
        
        $apiURL = "https://gdata.youtube.com/feeds/api/videos/" . $model->VideoID . "?v=2&alt=json";

        $get = static::fetch($apiURL);
                
        $decode = json_decode($get, TRUE);
                
        //Set embed url
        $model->URL = "//www.youtube.com/embed/" . $model->VideoID;
                
        //Clean up special characters before saving title
        $model->Title = $decode['entry']['title']['$t'];
                
        //Find the largest thumbnail available
        $largestImage = null;
                
        foreach($decode['entry']['media$group']['media$thumbnail'] as $value)
        {
            //On first loop, always add $value so we can begin comparisons
            if(is_null($largestImage))
                $largestImage = $value;
            else
            {
                //Check if current image is larger than $largestImage
                if($value['width'] > $largestImage['width'])
                    $largestImage = $value;
            }
        }
                
        $model->ThumbURL = $largestImage['url'];  

        return $model;
    }
}