<?php

$vairiable = '1z2vd7my';



$responcetype = '.json';

$oauth = '?auth=xAQ5jjGOBytdS6dGGUQQyVZQpQseN2M2OxDM8vel';

$baseurl = 'https://kimono-a1458.firebaseio.com//kimono/api/';

$curl = new \Curl\Curl();

$version = '/latest';
$params = $api_key;

$FullURL = $baseurl.$vairiable.$version.$responcetype.$oauth;




$response = file_get_contents($FullURL);
$results = json_decode($response, TRUE);


$size = sizeof($response);

print_r($results);
exit;

?>



<!---->
<!--<script src="https://www.gstatic.com/firebasejs/3.3.0/firebase.js"></script>-->
<!--<script>-->
<!--    // Initialize Firebase-->
<!--    var config = {-->
<!--        apiKey: "AIzaSyBdQyk4J4meknDmDo6ZiDZOePsCO4OeZZY",-->
<!--        authDomain: "kimono-a1458.firebaseapp.com",-->
<!--        databaseURL: "https://kimono-a1458.firebaseio.com",-->
<!--        storageBucket: "kimono-a1458.appspot.com",-->
<!--    };-->
<!--    firebase.initializeApp(config);-->
<!--</script>-->
