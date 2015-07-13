<?php

 $options['host'] = "localhost";
 $options['port'] = 5984;

 $couch = new CouchSimple($options); // See if we can make a connection
 $resp = $couch->send("GET", "/");
 var_dump($resp); // response: string(46) "{"couchdb": "Welcome", "version": "0.7.0a553"}"

 // Get a list of all databases in CouchDb
 $resp = $couch->send("GET", "/_all_dbs");
 var_dump($resp); // string(17) "["test_suite_db"]"

 // Create a new database "test"
 $resp = $couch->send("PUT", "/test");
 var_dump($resp); // string(12) "{"ok":true}"

 // Get all documents in that database
 $resp = $couch->send("GET", "/test/_all_docs");
 var_dump($resp); // string(27) "{"total_rows":0,"rows":[]}"

 // Create a new document in the database test with the id 123 and some data
 $resp = $couch->send("PUT", "/test/123", '{"_id":"123","data":"Foo"}');
 var_dump($resp); // string(42) "{"ok":true,"id":"123","rev":"2039697587"}"

 // Get all documents in test again, seing doc 123 there
 $resp = $couch->send("GET", "/test/_all_docs");
 var_dump($resp); // string(91) "{"total_rows":1,"offset":0,"rows":[{"id":"123","key":"123","value":{"rev":"2039697587"}}]}"

 // Get back document with the id 123
 $resp = $couch->send("GET", "/test/123");
 var_dump($resp); // string(47) "{"_id":"123","_rev":"2039697587","data":"Foo"}"

 // Delete our "test" database
 $resp = $couch->send("DELETE", "/test/");
 var_dump($resp); // string(12) "{"ok":true}"

 class CouchSimple {
    function CouchSimple($options) {
       foreach($options AS $key => $value) {
          $this->$key = $value;
       }
    }

   function send($method, $url, $post_data = NULL) {
      $s = fsockopen($this->host, $this->port, $errno, $errstr);
      if(!$s) {
         echo "$errno: $errstr\n";
         return false;
      }

      $request = "$method $url HTTP/1.0\r\nHost: $this->host\r\n";

      if ($this->user) {
         $request .= "Authorization: Basic ".base64_encode("$this->user:$this->pass")."\r\n";
      }

      if($post_data) {
         $request .= "Content-Length: ".strlen($post_data)."\r\n\r\n";
         $request .= "$post_data\r\n";
      }
      else {
         $request .= "\r\n";
      }

      fwrite($s, $request);
      $response = "";

      while(!feof($s)) {
         $response .= fgets($s);
      }

      list($this->headers, $this->body) = explode("\r\n\r\n", $response);
      return $this->body;
   }
}
?>