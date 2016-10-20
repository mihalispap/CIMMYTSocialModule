<?php

function remove_line($filename, $line_no)
{
        $fp=fopen($filename,"r");
        $fpt=fopen("temp.tmp","w");

        $lineC=-1;
        while (!feof($fp))
        {
                $lineC++;
                $buffer = fgetss($fp, 4096);
                if($lineC!=$line_no)
                        fwrite($fpt,$buffer);
        }
        fclose($fpt);
        fclose($fp);

        unlink($filename);
        rename("temp.tmp",$filename);
}



//set_include_path($_SERVER['DOCUMENT_ROOT'] . '/path-to-your-director/');

//include_once __DIR__ . '/vendor/autoload.php';

include_once './vendor/autoload.php';

//require_once 'Google/Client.php';
//require_once 'Google/Service/YouTube.php';

$key = file_get_contents('the_key.txt');

$application_name = 'CIMMYT Sharer';
$client_secret = 'Q09XGvcZ2EvCPaPYZVxlXG_0';
$client_secret = 'rGWCw5DIEkCkVOzTcKgc2VMC';
$client_id = '385927352495-np27hme7d2br3gsjamsr7j8e55jv4tbn.apps.googleusercontent.com';
$client_id = '385927352495-5kcc607vt85vdmmvi638shic2f1q4hsk.apps.googleusercontent.com';
$scope = array('https://www.googleapis.com/auth/youtube.upload', 'https://www.googleapis.com/auth/youtube', 
        'https://www.googleapis.com/auth/youtubepartner');

		$client = new Google_Client();
                $client->setApplicationName($application_name);
                $client->setClientId($client_id);
                $client->setAccessType('offline');
                $client->setAccessToken($key);
                $client->setScopes($scope);
                $client->setClientSecret($client_secret);


$key = file_get_contents('the_key.txt');

$application_name = 'CIMMYT Sharer';
$client_secret = 'Q09XGvcZ2EvCPaPYZVxlXG_0';
$client_secret = 'rGWCw5DIEkCkVOzTcKgc2VMC';
$client_id = '385927352495-np27hme7d2br3gsjamsr7j8e55jv4tbn.apps.googleusercontent.com';
$client_id = '385927352495-5kcc607vt85vdmmvi638shic2f1q4hsk.apps.googleusercontent.com';
$scope = array('https://www.googleapis.com/auth/youtube.upload', 'https://www.googleapis.com/auth/youtube', 
	'https://www.googleapis.com/auth/youtubepartner');

	$cid="";

		if ($client->getAccessToken()) {

			//print_r($client);

			/**
			 * Check to see if our access token has expired. If so, get a new one and save it to file for future use.
			 */
			if($client->isAccessTokenExpired()) {
				//$newToken = $client->getAccessToken();//json_decode($client->getAccessToken());

				$fp=fopen('./refresh_token.txt','r');
				$refresh_token=fgetss($fp,4096);
				fclose($fp);
				echo "|".$refresh_token."|";
				$refresh_token=str_replace("\n","",$refresh_token);

				$client->refreshToken($refresh_token);
				file_put_contents('the_key.txt', $client->getAccessToken());
			}

			$youtube = new Google_Service_YouTube($client);


				//$uploaded_video_id='Xh_mkR2rJnQ';
				$handle=fopen("conf/newest","r");

				if($handle===FALSE)
					exit;
				while(!feof($handle))
				{
					$buff=fgetss($handle,4096);
					$uploaded_video_id=str_replace("\n","",$buff);

					echo "UID:".$uploaded_video_id;

					if(isset($uploaded_video_id))
					{
						try
						{

							$playlistId='PLNPvx8wq3BRi9PMsJUd-_Op7Gbk9lT32o';

							// 5. Add a video to the playlist. First, define the resource being added
    							// to the playlist by setting its video ID and kind.
    							$resourceId = new Google_Service_YouTube_ResourceId();
    							$resourceId->setVideoId($uploaded_video_id);
    							$resourceId->setKind('youtube#video');

							//echo "so far so good";
							//print_r($resourceId);

    							// Then define a snippet for the playlist item. Set the playlist item's
    							// title if you want to display a different value than the title of the
    							// video being added. Add the resource ID and the playlist ID retrieved
    							// in step 4 to the snippet as well.
    							$playlistItemSnippet = new Google_Service_YouTube_PlaylistItemSnippet();
    							//$playlistItemSnippet->setTitle('First video in the test playlist');
    							$playlistItemSnippet->setPlaylistId($playlistId);
    							$playlistItemSnippet->setResourceId($resourceId);

							//echo "so far so good2";
							//print_r($playlistItemSnippet);

    							// Finally, create a playlistItem resource and add the snippet to the
    							// resource, then call the playlistItems.insert method to add the playlist
    							// item.
    							$playlistItem = new Google_Service_YouTube_PlaylistItem();
    							$playlistItem->setSnippet($playlistItemSnippet);
    							$playlistItemResponse = $youtube->playlistItems->insert(
        							'snippet,contentDetails', $playlistItem, array());

							//echo "so far so good3";
							//print_r($playlistItemResponse);

						} catch (Google_Service_Exception $e) {
    							//$htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
        						print_r($e);
  						} catch (Google_Exception $e){
    							//$htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
        						print_r($e);
  						}
					}
				}
			}

			fclose($handle);
			unlink("conf/newest");
?>

