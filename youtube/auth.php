<?php

// Call set_include_path() as needed to point to your client library.
//set_include_path($_SERVER['DOCUMENT_ROOT'] . '/directory/to/google/api/');
//require_once 'Google/Client.php';
//require_once 'Google/Service/YouTube.php';

include_once /*__DIR__ .*/ './vendor/autoload.php';

session_start();

/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */

//http://localhost/oauth2callback.php?state=1173903424&code=4/irRhN3zl4_7buch2V7cITtTSiWsxdLHq1reyAJ4NtnM#

$OAUTH2_CLIENT_ID = 'XXXXXXX.apps.googleusercontent.com';
$OAUTH2_CLIENT_ID = '385927352495-np27hme7d2br3gsjamsr7j8e55jv4tbn.apps.googleusercontent.com';
$OAUTH2_CLIENT_ID = '385927352495-5kcc607vt85vdmmvi638shic2f1q4hsk.apps.googleusercontent.com';
$OAUTH2_CLIENT_SECRET = 'XXXXXXXXXX';
$OAUTH2_CLIENT_SECRET = 'Q09XGvcZ2EvCPaPYZVxlXG_0';
$OAUTH2_CLIENT_SECRET = 'rGWCw5DIEkCkVOzTcKgc2VMC';
$REDIRECT = 'http://dev.agroknow.com/cimmyt/CIMMYTSocialModule/youtube/auth.php';
$APPNAME = "XXXXXXXXX";
$APPNAME = "CIMMYT Sharer";


$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setScopes('https://www.googleapis.com/auth/youtube');
$client->setRedirectUri($REDIRECT);
$client->setApplicationName($APPNAME);
$client->setAccessType('offline');


// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);

if (isset($_GET['code'])) {
    if (strval($_SESSION['state']) !== strval($_GET['state'])) {
        die('The session state did not match.');
    }

 //print_r($client);
 //print_r($_SESSION);


	$client->authenticate($_GET['code']);
    	$_SESSION['token'] = $client->getAccessToken();

	$towrite="{\"access_token\":\"".$_SESSION['token']['access_token']."\",\"token_type\":\"".$_SESSION['token']['token_type']."\",\"expires_in\":".$_SESSION['token']['expires_in'].",\"created\":".$_SESSION['token']['created']."}";

	//echo "To authorise the app, please copy this:".$towrite." at the_key.txt of your youtube root installation folder!";

		$fp = fopen('./the_key.txt', 'w');
                fwrite($fp, $towrite);
                fclose($fp);

		if(isset($_SESSION['refresh_token']))
		{
			//1/9d45WODQV6WXXrbK1WMLWYHDRUwEDi8XmukNglFfq7U

			$fp=fopen('./refresh_token.txt','w');
			fwrite($fp,$towrite);
			fclose($fp);
		}

}

if (isset($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
    //echo '<code>' . $_SESSION['token'] . '</code>';
}

 //print_r($client);

// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {
    try {
        // Call the channels.list method to retrieve information about the
        // currently authenticated user's channel.
        $channelsResponse = $youtube->channels->listChannels('contentDetails', array(
            'mine' => 'true',
        ));

        $htmlBody = '';
        foreach ($channelsResponse['items'] as $channel) {
            // Extract the unique playlist ID that identifies the list of videos
            // uploaded to the channel, and then call the playlistItems.list method
            // to retrieve that list.
            $uploadsListId = $channel['contentDetails']['relatedPlaylists']['uploads'];

            $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet', array(
                'playlistId' => $uploadsListId,
                'maxResults' => 50
            ));

            $htmlBody .= "<h3>Videos in list $uploadsListId</h3><ul>";
            foreach ($playlistItemsResponse['items'] as $playlistItem) {
                $htmlBody .= sprintf('<li>%s (%s)</li>', $playlistItem['snippet']['title'],
                    $playlistItem['snippet']['resourceId']['videoId']);
            }
            $htmlBody .= '</ul>';
        }
    } catch (Google_ServiceException $e) {
        $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
            htmlspecialchars($e->getMessage()));
    } catch (Google_Exception $e) {
        $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
            htmlspecialchars($e->getMessage()));
    }

    $_SESSION['token'] = $client->getAccessToken();
} else {
    $state = mt_rand();
    $client->setState($state);
    $_SESSION['state'] = $state;

    $authUrl = $client->createAuthUrl();
    $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorise access</a> before proceeding.<p>
END;
}
?>

<!doctype html>
<html>
<head>
    <title>My Uploads</title>
</head>
<body>
<?php echo $htmlBody?>
</body>
</html>
