<?php
$side_navbar_items=[
"Friends follower"=>[["follower_friend_cmp"],[1]],
"Active day"=>[["get_activiness_day"],[1]],
"User created year" =>[["get_create_year"],[1]],
"Language" =>[["get_different_languages"],[1]],//has two level, but use l1
"Eat habits"=>[["get_eat_habits","get_eat_habits_sub"],[2,2]],
"Hot words" =>[["get_highly_used_words"],[1]],
"World Cup" =>[["get_interest_worldcup"],[1]],
"Life attitude" => [["get_positive_negative","get_positive_negative_sub"],[3,1]],
"Popular sports" => [["get_public_interest_sports"],[1]],
"Transport satisfication" => [["get_transport_satisfaction"],[1]],
"User location" => [["last_1000_tweets_geo_location"],[1]],
"Terminal"=>[["get_twitter_terminals"],[1]]
];

// $couchdb_server="115.146.84.130";
$couchdb_server="115.146.86.86";
$couchdb_port="5984";
$couchdb_city1="mel";
$couchdb_city2="la";//todo: change to another city
$couchdb_slaves=["115.146.86.115","115.146.86.41","115.146.85.188","115.146.84.130"];


$settings = array (
		'consumer_key' => 'fTCzKjaOfqkfACOoITboNTrov',
		'consumer_secret' => 'Uic9avWrbJJCdrPyGX6gGlSfB9Aq6Z6ODZToK9XRNwnXhzIdz8',
		'oauth_access_token' => '2473975850-sVvc0nvIkZ4vN7lTVBneDutk4ZTvX3YD7wy5x2S',
		'oauth_access_token_secret' => 'qgXjriZCyyymJR6JmA8QXPJWR1OrFqCCbj8ISiDmT07Ml'
);

$cache_time_local_data=1;
$cache_time_twitts=5;
?>