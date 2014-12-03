<?php // $Id$
/**
 * Get a contacts details
 */
	
	require_once '/chroot/home/rxkicksc/rxkicks.com/html/devicom_apps/lib/ConstantContact/class.cc.php';
	
	// Set your Constant Contact account username and password below
	$cc = new cc('username', 'password');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Example Script - get_contact()</title>
</head>
<body>
<h3>Example Script - get_contact()</h3>
<p><a href="index.php">View all example scripts</a></p>

<?php
	// all you need to pass here is the unique contact ID
	// you can obtain this using the query_contacts() method
	$contact = $cc->get_contact(0);
	
	if($contact):
		echo $contact['Name'] . ' - ' . $contact['EmailAddress'];
	else:
		// if an error occurs we can debug it any various ways
		
		// show a simple error to the user
		echo "Operation failed: " . $cc->http_get_response_code_error($cc->http_response_code);
		
		// or output the last http request and response strings, for debugging only
		$cc->show_last_connection();
		
		// usually the http_response_body will contain a more descriptive error to help debug
		if($cc->http_response_body):
			echo '<p>' . $cc->http_response_body . '</p>';
		endif;
	endif;
?>

</body>
</html>