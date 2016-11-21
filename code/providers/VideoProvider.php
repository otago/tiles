<?php
/**
 * Abstract class for VideoProvider
 * @see {@link VideoTile}
 */
abstract class VideoProvider {
    
    /**
     * The name of the video provider
     * @var string
     */
    public static $name;
    
    /**
     * Determine if a URL matches the video provider
     * @param string $url Video URL to check
     * @return bool TRUE if the URL matches the provider
     */
    public static function is_provider($url) { return false; }
    
    /**
     * Connects to the video provider's API and obtain information on the
     * given URL
     * @param string $url Video URL to get information for
     * @return {@link VideoInfo} Video information
     */
    public static function get_video_info($url) { return new VideoInfo(); }
    
    /**
     * Fetch data via curl
     * @param string $url Source URL
     * @return mixed binary data
     */
    public static function fetch($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        
        // allow self signed certs in dev mode
		if (Director::isDev()) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '1');
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, '0');
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $get = curl_exec($ch);
        
        curl_close($ch);
        
        return $get;
    }
}