<?php


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


	$path="videos";
	$extensions=array("avi","mpg","vob","mp4","m2ts","mov","3gp","mkv");

	if ($handle = opendir($path)) {
		while (false !== ($file = readdir($handle))) {
			if ('.' === $file) continue;
			if ('..' === $file) continue;

			$file_info=pathinfo($file);

			if(!isset($file_info['extension']))
			{
				if ($handle_inner = opendir($path."/".$file))
				{
					//echo "I am in";
					while (false !== ($file_inner = readdir($handle_inner)))
					{
						if ('.' === $file_inner) continue;
						if ('..' === $file_inner) continue;

						$file_info=pathinfo($file_inner);
						if(!isset($file_info['extension'])) continue;

						$preg_m=preg_grep( "/".$file_info['extension']."/i" , $extensions );

						//print_r($preg_m);

						//echo "|".$preg_m[1]."|";

						//print_r($file_info);
						if(!empty($preg_m) && isset($file_info['extension']))
						{
							//echo "will do:".$file_inner."<br>";
							//print_r($file_info);

							//if($file_inner=="Reaper.mp4")
							//	continue;

							process_and_upload($path."/".$file, $file_inner,$client);
						}
						$preg_m=array();
							//echo "ISFILE:".$file_inner."<br>";
					}
					closedir($handle_inner);
				}
			}

		}
		closedir($handle);
	}

exit;

function process_and_upload($dir_name, $filename, $client)
{

$key = file_get_contents('the_key.txt');

$application_name = 'CIMMYT Sharer';
$client_secret = 'Q09XGvcZ2EvCPaPYZVxlXG_0';
$client_secret = 'rGWCw5DIEkCkVOzTcKgc2VMC';
$client_id = '385927352495-np27hme7d2br3gsjamsr7j8e55jv4tbn.apps.googleusercontent.com';
$client_id = '385927352495-5kcc607vt85vdmmvi638shic2f1q4hsk.apps.googleusercontent.com';
$scope = array('https://www.googleapis.com/auth/youtube.upload', 'https://www.googleapis.com/auth/youtube', 
	'https://www.googleapis.com/auth/youtubepartner');

	$videoPath = $dir_name."/".$filename;
	//$videoPath = "sample.mp4";
	$videoCategory = "28";

	$videoTags = array();
	$videoTitle = "";
	$videoDescription = "";

	if ($handle_inner = opendir($dir_name))
	{
		//echo "I am in";
		while (false !== ($file_inner = readdir($handle_inner)))
		{
			if ('.' === $file_inner) continue;
			if ('..' === $file_inner) continue;

			$file_info=pathinfo($file_inner);

			if($file_info['extension']=="xml" && $file_info['filename']=="dublin_core")
			{
				$xml=simplexml_load_file($dir_name."/"."dublin_core.xml") or die("Error: Cannot create object");

				for($i=0;$i<count($xml->dcvalue);$i++)
				{
					if($xml->dcvalue[$i]->attributes()['element']=='title')
					{
						$videoTitle.=$xml->dcvalue[$i];
					}
					if($xml->dcvalue[$i]->attributes()['element']=='description'
						&&
						$xml->dcvalue[$i]->attributes()['qualifier']=='abstract')
					{
						$videoDescription.=$xml->dcvalue[$i];
					}
					if($xml->dcvalue[$i]->attributes()['element']=='subject'
						&&
						$xml->dcvalue[$i]->attributes()['qualifier']=='tags')
					{
						$tags=(string)$xml->dcvalue[$i];
						array_push($videoTags,$tags);
					}
					if($xml->dcvalue[$i]->attributes()['element']=='subject'
						&&
						$xml->dcvalue[$i]->attributes()['qualifier']=='keywords')
					{
						$tags=(string)$xml->dcvalue[$i];
						array_push($videoTags,$tags);
					}
				}
			}

		}
		closedir($handle_inner);
	}

	$hash=$filename.$title.$description.$tags;
	$hash=md5($hash);

	$handle = @fopen("conf"."/"."uploads", "r");
	if ($handle)
	{
		while (!feof($handle))
		{
			$buffer = fgetss($handle, 4096);

			$values=explode("\t",$buffer);

			if($values[0]==$hash)
			{
				return;
			}

		}
		fclose($handle);
	}

	echo "I will upload:".$filename.", with:";
	echo "TITLE @END:".$videoTitle;
	echo "Description @END:".$videoDescription;
	echo "TAGS @END:";
	print_r($videoTags);

	try{
		// Client init
		/*$client = new Google_Client();
		$client->setApplicationName($application_name);
		$client->setClientId($client_id);
		$client->setAccessType('offline');
		$client->setAccessToken($key);
		$client->setScopes($scope);
		$client->setClientSecret($client_secret);*/

		if ($client->getAccessToken()) {

			print_r($client);

			/**
			 * Check to see if our access token has expired. If so, get a new one and save it to file for future use.
			 */
			if($client->isAccessTokenExpired()) {
				$newToken = json_decode($client->getAccessToken());
				$client->refreshToken($newToken->refresh_token);
				file_put_contents('the_key.txt', $client->getAccessToken());
			}

			$youtube = new Google_Service_YouTube($client);

			// Create a snipet with title, description, tags and category id
			$snippet = new Google_Service_YouTube_VideoSnippet();
			$snippet->setTitle($videoTitle);
			$snippet->setDescription($videoDescription);
			$snippet->setCategoryId($videoCategory);
			$snippet->setTags($videoTags);

			// Create a video status with privacy status. Options are "public", "private" and "unlisted".
			$status = new Google_Service_YouTube_VideoStatus();
			$status->setPrivacyStatus('private');

			// Create a YouTube video with snippet and status
			$video = new Google_Service_YouTube_Video();
			$video->setSnippet($snippet);
			$video->setStatus($status);

			// Size of each chunk of data in bytes. Setting it higher leads faster upload (less chunks,
			// for reliable connections). Setting it lower leads better recovery (fine-grained chunks)
			$chunkSizeBytes = 1 * 1024 * 1024;

			// Setting the defer flag to true tells the client to return a request which can be called
			// with ->execute(); instead of making the API call immediately.
			$client->setDefer(true);

			// Create a request for the API's videos.insert method to create and upload the video.
			$insertRequest = $youtube->videos->insert("status,snippet", $video);

			// Create a MediaFileUpload object for resumable uploads.
			$media = new Google_Http_MediaFileUpload(
				$client,
				$insertRequest,
				'video/*',
				null,
				true,
				$chunkSizeBytes
			);
			$media->setFileSize(filesize($videoPath));


			// Read the media file and upload it chunk by chunk.
			$status = false;
			$handle = fopen($videoPath, "rb");
			while (!$status && !feof($handle)) {
				$chunk = fread($handle, $chunkSizeBytes);
				$status = $media->nextChunk($chunk);
			}

			fclose($handle);

			/**
			 * Video has successfully been upload, now lets perform some cleanup functions for this video
			 */
			if ($status->status['uploadStatus'] == 'uploaded') {
				// Actions to perform for a successful upload
				$uploaded_video_id = $status['id'];
				echo "UID:".$uploaded_video_id;

				if(isset($uploaded_video_id))
				{
					$fp = fopen("conf"."/"."uploads", 'a');
					fwrite($fp, $hash."\t".$uploaded_video_id."\n");
					fclose($fp);
				}
			}

			// If you want to make other calls after the file upload, set setDefer back to false
			$client->setDefer(true);

		} else{
			// @TODO Log error
			echo 'Problems creating the client';
		}

	} catch(Google_Service_Exception $e) {
		print "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage();
		print "Stack trace is ".$e->getTraceAsString();
	}catch (Exception $e) {
		print "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage();
		print "Stack trace is ".$e->getTraceAsString();
	}
}
?>
