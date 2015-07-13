<?php
include_once 'conf.php';
include_once 'TwitterAPIExchange.php';

if ($_SERVER["REQUEST_METHOD"]=="GET") {
	$username=$_GET['q'];


	if (!in_array('memcache', get_loaded_extensions())){
		$data=get_tweets($username, $settings);
	}
	else{
		//if this url is stored in memcache, get it and return
		$memcache=new Memcache();
		$memcache->connect('localhost',11211) or die ("Could not connect memcached server");
		$data=$memcache->get($username);
		if (!$data) {
			//if not retrive data from couchdb and store in memcache
			$data=get_tweets($username, $settings);
			$memcache->set($username, $data,false,$cache_time_twitts);
		}
	}
	echo  $data;

}
function get_tweets($username,$settings){
	$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
	$getfield = '?screen_name='.$username."&count=100";
	$requestMethod = 'GET';

	$twitter = new TwitterAPIExchange ( $settings );
	return  $twitter->setGetfield ( $getfield )->buildOauth ( $url, $requestMethod )->performRequest ();
}