<html>


<head>
<title>LinkChecker</title>
</head>

<body>

<!-- Form to accept URL -->
	<form id="url" method="post" action="LinkChecker.php">
		<input id="url" type="text" name="url" size="100"></input>
		<input name="button" type="submit" value="Check!"/> 
	</form>

<?php

if (isset($_POST['url'])) {
	
	echo "<P>Scanning URL '" . $_POST['url'] . "'...<P>";
	
	$page_contents = scanURL($_POST['url']);
	$valid_urls = resolveLinks($page_contents);
	checkLinks($valid_urls);
	
}

# ============================================= F U N C T I O N S =====================================================

function scanURL($url) {
	
	return $contents = file_get_contents($url);
	
}

function resolveLinks($page_content) {
	
	preg_match_all("/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/", $page_content, $output);
	
	$num_urls = count($output[0]);
	
	for ($u = 0; $u < $num_urls; $u++) {
		if (substr($output[0][$u],0,3) == "htt" || substr($output[0][$u],0,3) == "www") { # Only handle URLs starting htt(p) or www
			if (substr($output[0][$u],0,3) == "www") { # if www, prefix with http://
				$output[0][$u] = "http://" . $output[0][$u];
			}
		} else { # throw it away
			unset($output[0][$u]);
		}
		
	}
	
	$output = array_values(array_filter($output[0]));
	natsort($output);
	
	return $output;
	
}

function checkLinks($array) {
	
	$num_urls = count($array);
	
	echo "Located " . $num_urls . " URLs on this page;<p>";

	echo "<table cellpadding=2 cellspacing=3 border=1 width=100%>";
	echo "<tr><td>URL<td>HTTP Code</tr>";
	
	for ($u = 0; $u < $num_urls; $u++) {

		if (strlen($array[$u]) < 100) {
			echo "<tr><td>" . $array[$u];	
		} else {
			echo "<tr><td><abbr title='" . $array[$u] . "'>" . substr($array[$u], 0, 100) . "...</abbr>";	
		}
		$return_info = requestURL($array[$u]);
		$http_code = $return_info['http_code'];
		echo "<td width=100px align=center><a href='https://httpstatuses.com/" . $http_code . "' target='_new'>" . $http_code . "</a>";
		echo "</tr>";

	}

	echo "</table>";
	
}

function requestURL($url) {

	// Get cURL resource
	$curl = curl_init();
	
	// Set some options - we are passing in a useragent too here
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => $url,
		#CURLOPT_USERAGENT => 'Codular Sample cURL Request'
	));
	
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	
	$info = curl_getinfo($curl);
	
	// Close request to clear up some resources
	curl_close($curl);
	
	return $info;
	
}


?>

</body>

</html>