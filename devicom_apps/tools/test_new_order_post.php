<?php

	//Creates XML string and XML document from the DOM representation
	$dom = new DomDocument('1.0');

	$root = $dom->appendChild($dom->createElement('Root'));
	$root->appendChild($dom->createTextNode("test"));
	
	// Make the output pretty
	$dom->formatOutput = true;
	
	// Save the XML string
	$xml = $dom->saveXML();

	$url = 'http://74.212.242.42/Net4/NewOrder/Receiver.aspx';

	$context = stream_context_create(array( 
	    'http' => array( 
	    'method'  => 'POST', 
	    'header'  => "Content-Type: text/xml", 
	    'content' => $xml
	))); 

//	print_r($xml);
//exit;
	// Send post
	$response = file_get_contents($url, false, $context);

echo $response;
	    

    

 

function realTime($time = null, $isAssociative = false){

    $offsetInHours = +8;
    $offsetInSeconds = $offsetInHours * 60 * 60;
   
    if (is_null($time)) {
	$time = time();
    }

    $pstTime = $time + $offsetInSeconds;

    $explodedTime = explode(',', gmdate('s,i,H,d,m,Y,w,z,I', $pstTime));
    
    if (!$isAssociative) {
	return $explodedTime;
    }
    return array_combine(array('tm_sec','tm_min','tm_hour','tm_mday','tm_mon','tm_year','tm_wday','tm_yday','tm_isdst'), $explodedTime);
}