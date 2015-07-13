<?php
include_once 'conf.php';

function get_couchdb_data($url) {
	if (! in_array ( 'memcache', get_loaded_extensions () )) {
		$data = file_get_contents ( $url );
	} else {
		// if this url is stored in memcache, get it and return
		$memcache = new Memcache ();

		if ($memcache->connect ( 'localhost', 11211 )) {
			$key = md5 ( $url );
			$data = $memcache->get ( $key );
			if ($data){
				return $data;
			}
			else {
				// retrive data from couchdb
				$data = file_get_contents ( $url );
				$memcache->set ( $key, $data, MEMCACHE_COMPRESSED, 10 );
			}
		} else {
			$data = file_get_contents ( $url );
		}
	}
	return $data;
}
function top_subs_food($sub_arr) {
	$meat = [ ];
	$veg = [ ];
	foreach ( $sub_arr as $k ) {
		$t = explode ( ",", $k->Key );
		$subname = $t [0];
		$type = $t [1];

		if (trim ( $type ) == "Meat&Seafood") {
			$meat [$subname] = $k->Value;
		} else {
			$veg [$subname] = $k->Value;
		}
	}
	arsort ( $meat );
	arsort ( $veg );
	$a = json_encode ( [
			"Meat&Seafood" => array_slice ( $meat, 0, 5 ),
			"Fruit&Vegetable" => array_slice ( $veg, 0, 5 )
	] );
	return $a;
}
function top_subs_att($sub_arr) {
	$subs = [ ];
	foreach ( $sub_arr as $k ) {
		$t = explode ( ":", $k->Key );
		$subname = $t [0];
		$att = $t [1];
		if (trim ( $att ) == "Positive") {
			$subs [$subname] = $k->Value;
		}
	}
	arsort ( $subs );
	return json_encode ( array_slice ( $subs, 0, 5 ) );
}
function top_words($words) {
	$topwords = [ ];
	foreach ( $words as $k ) {
		$topwords [$k->Key] = $k->Value;
	}
	arsort ( $topwords );
	$j = '"KeyValue":[';
	$topwords = array_slice ( $topwords, 0, 10 );
	foreach ( $topwords as $k => $v ) {
		$j .= '{"Key":' . '"' . $k . '","Value":' . $v . '},';
	}
	$j = substr ( $j, 0, - 1 );
	$j .= "]";
	return $j;
	// return json_encode(array_slice($topwords, 0,10));
}

if ($_SERVER ["REQUEST_METHOD"] == "GET") {
	// do
	$target_db_view = $side_navbar_items [$_GET ["s"]];
	$server = "http://" . $couchdb_server . ":" . $couchdb_port;

	$c1_total_row = 0;
	$c2_total_row = 0;

	foreach ( $couchdb_slaves as $slave ) {
		$url1 = "http://" . $slave . ":" . $couchdb_port . "/" . $couchdb_city1 . "/_all_docs?limit=1";
		$url2 = "http://" . $slave . ":" . $couchdb_port . "/" . $couchdb_city2 . "/_all_docs?limit=1";

		$c1_total_row += (json_decode ( get_couchdb_data ( $url1 ) )->total_rows);
		$c2_total_row += (json_decode ( get_couchdb_data ( $url2 ) )->total_rows);
	}

	switch ($_GET ["s"]) {
		case "Friends follower" :
		case "World Cup" :
		case "Active day" :
		case "User created year" :
		case "Language" :
		case "Popular sports" :
		case "Terminal" :
		case "Transport satisfication" :
			$url_city1 = $server . "/" . $couchdb_city1 . "/" . $target_db_view [0] [0] . "_1";
			$result_city1 = trim ( get_couchdb_data ( $url_city1 ) );
			$url_city2 = $server . "/" . $couchdb_city2 . "/" . $target_db_view [0] [0] . "_1";
			$result_city2 = trim ( get_couchdb_data ( $url_city2 ) );
			if ($result_city1 && $result_city2) {
				$result = '{"state":1, "data":[' . '{"city":"' . $couchdb_city1 . '", "total_t":' . $c1_total_row . ',"lv":1,' . substr ( $result_city1, 1, - 1 ) . '}' . ',' . '{"city":"' . $couchdb_city2 . '","total_t":' . $c2_total_row . ',"lv":1,' . substr ( $result_city2, 1, - 1 ) . '}' . "]}";
			} else {
				$result = '{"state":-1, "err":"Cannot find data in DB. This may due to updating cooperation on DB."}';
			}

			break;
		case "User location" :
			$url_city1 = $server . "/" . $couchdb_city1 . "/" . $target_db_view [0] [0];
			$result_city1 = trim ( get_couchdb_data ( $url_city1 ) );
			$url_city2 = $server . "/" . $couchdb_city2 . "/" . $target_db_view [0] [0];
			$result_city2 = trim ( get_couchdb_data ( $url_city2 ) );
			if ($result_city1 && $result_city2) {
				$result = '{"state":1, "data":[' . '{"city":"' . $couchdb_city1 . '","total_t":' . $c1_total_row . ',"lv":1,' . substr ( $result_city1, 1, - 1 ) . '}' . ',' . '{"city":"' . $couchdb_city2 . '","total_t":' . $c2_total_row . ',"lv":1,' . substr ( $result_city2, 1, - 1 ) . '}' . "]}";
			} else {
				$result = '{"state":-1, "err":"Cannot find data in DB. This may due to updating cooperation on DB."}';
			}

			break;

		case "Eat habits" :
			$url_city1 = $server . "/" . $couchdb_city1 . "/" . $target_db_view [0] [0] . "_1";
			$result_city1 = trim ( get_couchdb_data ( $url_city1 ) );
			$url_city2 = $server . "/" . $couchdb_city2 . "/" . $target_db_view [0] [0] . "_1";
			$result_city2 = trim ( get_couchdb_data ( $url_city2 ) );

			$url_city1_1 = $server . "/" . $couchdb_city1 . "/" . $target_db_view [0] [1] . "_2";
			$result_city1_1 = trim ( get_couchdb_data ( $url_city1_1 ) );
			$url_city2_2 = $server . "/" . $couchdb_city2 . "/" . $target_db_view [0] [1] . "_2";
			$result_city2_2 = trim ( get_couchdb_data ( $url_city2_2 ) );

			// sort to get top subs of meat and veg
			$json_city1 = json_decode ( $result_city1_1 );
			$json_city2 = json_decode ( $result_city2_2 );

			$result_top_sub_1 = top_subs_food ( $json_city1->KeyValue );
			$result_top_sub_2 = top_subs_food ( $json_city2->KeyValue );

			if ($result_city1 && $result_city2 || $url_city2_2 || $url_city1_1) {
				$result = '{"state":1, "data":[' . '{"city":"' . $couchdb_city1 . '","total_t":' . $c1_total_row . ',"lv":1,' . substr ( $result_city1, 1, - 1 ) . '}' . ',' . '{"city":"' . $couchdb_city2 . '","total_t":' . $c2_total_row . ',"lv":1,' . substr ( $result_city2, 1, - 1 ) . '}' . ',' . '{"city":"' . $couchdb_city1 . '","lv":2, "data":' . $result_top_sub_1 . '}' . ',' . '{"city":"' . $couchdb_city2 . '","lv":2, "data":' . $result_top_sub_2 . '}' . "]}";
			} else {
				$result = '{"state":-1, "err":"Cannot find data in DB. This may due to updating cooperation on DB."}';
			}
			break;

		case "Life attitude" :
			$url_city1 = $server . "/" . $couchdb_city1 . "/" . $target_db_view [0] [0] . "_1";
			$result_city1 = trim ( get_couchdb_data ( $url_city1 ) );
			$url_city2 = $server . "/" . $couchdb_city2 . "/" . $target_db_view [0] [0] . "_1";
			$result_city2 = trim ( get_couchdb_data ( $url_city2 ) );

			$url_city1_1 = $server . "/" . $couchdb_city1 . "/" . $target_db_view [0] [1] . "_1";
			$result_city1_1 = trim ( get_couchdb_data ( $url_city1_1 ) );
			$url_city2_2 = $server . "/" . $couchdb_city2 . "/" . $target_db_view [0] [1] . "_1";
			$result_city2_2 = trim ( get_couchdb_data ( $url_city2_2 ) );

			// sort to get top subs
			$json_city1 = json_decode ( $result_city1_1 );
			$json_city2 = json_decode ( $result_city2_2 );

			$result_top_sub_1 = top_subs_att ( $json_city1->KeyValue );
			$result_top_sub_2 = top_subs_att ( $json_city2->KeyValue );

			if ($result_city1 || $result_city2 || $url_city2_2 || $url_city1_1) {
				$result = '{"state":1, "data":[' . '{"city":"' . $couchdb_city1 . '","total_t":' . $c1_total_row . ',"lv":1,' . substr ( $result_city1, 1, - 1 ) . '}' . ',' . '{"city":"' . $couchdb_city2 . '","total_t":' . $c2_total_row . ',"lv":1,' . substr ( $result_city2, 1, - 1 ) . '}' . ',' . '{"city":"' . $couchdb_city1 . '","lv":2, "data":' . $result_top_sub_1 . '}' . ',' . '{"city":"' . $couchdb_city2 . '","lv":2, "data":' . $result_top_sub_2 . '}' . "]}";
			} else {
				$result = '{"state":-1, "err":"Cannot find data in DB. This may due to updating cooperation on DB."}';
			}
			break;

		case "Hot words" :
			$url_city1 = $server . "/" . $couchdb_city1 . "/" . $target_db_view [0] [0] . "_1";
			$result_city1 = trim ( get_couchdb_data ( $url_city1 ) );
			$url_city2 = $server . "/" . $couchdb_city2 . "/" . $target_db_view [0] [0] . "_1";
			$result_city2 = trim ( get_couchdb_data ( $url_city2 ) );

			$json_city1 = json_decode ( $result_city1 );
			$json_city2 = json_decode ( $result_city2 );

			$result_top_sub_1 = top_words ( $json_city1->KeyValue );
			$result_top_sub_2 = top_words ( $json_city2->KeyValue );

			if ($result_city1 && $result_city2) {
				$result = '{"state":1, "data":[' . '{"city":"' . $couchdb_city1 . '","total_t":' . $c1_total_row . ',"lv":1,' . $result_top_sub_1 . '}' . ',' . '{"city":"' . $couchdb_city2 . '","total_t":' . $c2_total_row . ',"lv":1,' . $result_top_sub_2 . '}' . "]}";
			} else {
				$result = '{"state":-1, "err":"Cannot find data in DB. This may due to updating cooperation on DB."}';
			}
			break;

		default :
			$result = '{"state":-1, "err":"This request is not found, or cannot be handled currently."}';
			break;
	}

	echo $result;
} else {
	echo '{"state":-1, "err":"This request method is not allowed."}'; // "{'state':-1, 'err':'this request method is not allowed'}";
}
?>