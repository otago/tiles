<?php
class VimeoVideoProvider extends VideoProvider {
    
    /**
     * The name of the video provider
     * @var string
     */
    public static $name = "Vimeo";
    
    /**
     * Determine if a URL matches the video provider
     * @param string $url Video URL to check
     * @return bool TRUE if the URL matches the provider
     */
    public static function is_provider($url) {
        if (strpos($url, 'vimeo.com'))
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
        $id = explode('vimeo.com/', $url);
        $model->VideoID = $id[1];
        
        $apiURL = "http://vimeo.com/api/v2/video/" . $model->VideoID . ".json";

        $get = static::fetch($apiURL);
                
        $decode = json_decode($get, TRUE);
                
        $model->URL = "//player.vimeo.com/video/" . $model->VideoID;
        $model->Title = $decode[0]['title'];
        $model->ThumbURL = $decode[0]['thumbnail_large'];

        return $model;
    }
}