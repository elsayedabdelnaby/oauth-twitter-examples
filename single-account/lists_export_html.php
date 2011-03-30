<?php
// OAuth Examples for Twitter
// by Scott Wilcox <scott@dor.ky> http://dor.ky @dordotky
// http://dor.ky/code/oauth-examples
// https://github.com/dordotky/oauth-twitter-examples
//
// This will output the users that a list is following into a HTML file.
//	
// First, require the excellent twitter-async library by Jaisen Mathai. You can read
// more about this library at https://github.com/jmathai/twitter-async
require "../library/EpiCurl.php";
require "../library/EpiOAuth.php";
require "../library/EpiTwitter.php";

// You need to replace the values in this file with those found in your application 
// settings. Sign in to the Twitter developer site at https://dev.twitter.com/apps and
// make a note of "Consumer Key" and "Consumer Secret" being careful not to include
// any extra spaces or text.
//
// Now click the 'My Access Token' link on the right hand menu and collect the
// "Access Token (oauth_token)" and "Access Token Secret (oauth_token_secret)"
// tokens. You're now reading to use on of the other scripts in this repository.
define("CONSUMER_KEY","K8kq7COAxSZkgbwcsdyWDQ");
define("CONSUMER_SECRET","TQpyYbJrSHUHQHWKOIWKvMQ6um8CsgPgQAUVZ8RI");
define("OAUTH_TOKEN","194052389-K5sxAwEH5bjNiOlsmU7wtB7HqWIFdhGA9vGM79kO");
define("OAUTH_TOKEN_SECRET","XVPkIns0m4darfPxqoWWVho3UxCieAOdoZ5VhLZhuN4");

// The name of the user and slug that we're going to be fetching followers
// for, this either needs to be either your own list of a public (or protected
// if you've got permission to view it). In the format username/slug 
$list = "david_elks/journalists";

// The filename that you want to save the list to
$filename = "david_elks_journalists.html";

// Place all interaction within a try/catch block
try {
	// Create a new object
	$twitterObj = new EpiTwitter(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);

	// The array which will contain our followers data
	$members = array(); $cursor = "-1";
	
	// Now actually fetch the data
	while ($cursor != "0") {
		$i++; echo "Run: $i // Cursor: ".$cursor."\n";
			
		// Push out our request to the API
		$f = $twitterObj->get("/".$list."/members.json?cursor=".$cursor);			
		if ($f->headers["X-RateLimit-Remaining"] == "0") {
			// Always respect rate limiting
			sleep((int)$f->headers["X-RateLimit-Reset"]-time());
		}

		// Get the cursor for the next run
		$cursor = (string)$f["next_cursor_str"];
		
		// Check for ratelimiting to see whether we need to sleep

		// Place all users into our array
		$response = json_decode($f->responseText);
		foreach ($response->users as $user) {
			$members[] = $user;
		}
	}
		
	// Process our array of data and then output an HTML table from here. You
	// could do a number of other things here, such as storing in a database,
	// output images or more.
	echo "Total in members array: ".number_format(count($members))." people\n";

	// Create a basic page then generate the HTML to fill a table in this example
	// with all of the followers	
	$page = "<html><body>List: $list<br /><table style=\"width: 700px\"><tr>"; $row = 0;
	foreach ($members as $member) {
		$row++;
		$page .= "
			<td>".$member->screen_name."</td>
			<td>".$member->name."</td>
			<td><img style=\"width: 48px\" src=\"".$member->profile_image_url."\" alt=\" \" /></td>";
		if ($row > 1) { $row = 0; $page.= "</tr><tr>";}

	}
	$page .= "</table></body></html>";
	
	// Write the followers HTML to a file
	file_put_contents($filename,$page);

} catch (Exception $error) {
	// Something failed, this will output the error message from the API
	echo $error->getMessage()."\n";
}
?>