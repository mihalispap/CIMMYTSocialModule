<?php
session_start();

//$firsttime=true;

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

function process_and_upload($dir_name, $filename, $f)
{
	$title="";
	$description="";
	$tags="";
	$cid="";

	//global $firsttime;

	$toupload=false;

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
						$title.=$xml->dcvalue[$i];
					}
					if($xml->dcvalue[$i]->attributes()['element']=='description'
						&&
						$xml->dcvalue[$i]->attributes()['qualifier']=='abstract')
					{
						$description.=$xml->dcvalue[$i];
					}
					if($xml->dcvalue[$i]->attributes()['element']=='subject'
						&&
						$xml->dcvalue[$i]->attributes()['qualifier']=='tags')
					{
						$tags.=" ".$xml->dcvalue[$i];
					}
					if($xml->dcvalue[$i]->attributes()['element']=='identifier'
                                                &&
                                                $xml->dcvalue[$i]->attributes()['qualifier']=='uri')
					{
						$cid=$xml->dcvalue[$i];
						//break;
					}
					//print_r($xml->dcvalue[$i]->attributes());
				}
			}

			if($file_info['filename']=="contents")
			{
				//echo "Reading:".$dir_name."/"."contents";
				$handle = @fopen($dir_name."/"."contents", "r");
				if ($handle)
				{
					while (!feof($handle))
					{
						$buffer = fgetss($handle, 4096);

						$values=explode("\t",$buffer);

						if($values[0]==$filename)
						{
							//print_r($values);
							$description.="\n".$values[2];
						}

						//echo $buffer;
					}
					fclose($handle);
				}
			}

			if($file_info['extension']=="xml" && $file_info['filename']=="metadata_export")
                        {
                                $xml=simplexml_load_file($dir_name."/"."metadata_export.xml") or die("Error: Cannot create object");

				//print_r($xml->dcvalue);
				//exit;

				if($xml->dcvalue[0]==1)
					$toupload=true;
                        }

		}
		closedir($handle_inner);
	}

	echo "I am going to upload:".$filename;
	//return;

	$hash=$filename.$title.$description.$tags;
	$hash=md5($hash);

	//$descr_md5=md5($description);

	$handle = @fopen("conf"."/"."uploads", "r");
	if ($handle)
	{
		$line_counter=-1;
		$lines_to_delete=array();
		while (!feof($handle))
		{
			$buffer = fgetss($handle, 4096);
			$line_counter++;
			$values=explode("\t",$buffer);

			$values[count($values)-1]=str_replace("\n","",$values[count($values)-1]);

			if($values[2]==$cid)
			{
				if($values[0]==$hash)
				{
					fclose($handle);
					echo "Already uploaded!";
					return;
				}

				echo "Comparing:".$values[3]."|".$filename;
				if($values[3]==$filename)
				{
					echo "There is an update here!".$values[1]."\n";
					$f->photos_delete($values[1]);

					$lines_to_delete[]=$line_counter;
				}
				/*else
				{
					fclose($handle);
					echo "There is an update! Need delete and repost!";
					return;
				}*/
			}

		}
		fclose($handle);
	}

	print_r($lines_to_delete);

	for($i=0;$i<count($lines_to_delete);$i++)
		remove_line("conf"."/"."uploads",$lines_to_delete[$i]);

	//exit;

	if($toupload==true)
		$ret=$f->sync_upload($dir_name."/".$filename,$title,$description,$tags);

	echo "RETURNED:".$ret;

	if(isset($ret) && is_numeric($ret))
	{
		$fp = fopen("conf"."/"."uploads", 'a');
		fwrite($fp, $hash."\t".$ret."\t".$cid."\t".$filename."\n");
		fclose($fp);
	}

	echo "TITLE @END:".$title;
	echo "Description @END:".$description;
	echo "TAGS @END:".$tags;
}



    /* Last updated with phpFlickr 1.4
     *
     * If you need your app to always login with the same user (to see your private
     * photos or photosets, for example), you can use this file to login and get a
     * token assigned so that you can hard code the token to be used.  To use this
     * use the phpFlickr::setToken() function whenever you create an instance of
     * the class.
     */

	$frob="72157670662225191-08b1a81cca9f0c80-144022562";
	$frob="72157670660712761-1f30756e5258f9d3";

	//$frob=file_get_contents('the_key.txt');

	$handle=@fopen('the_key.txt','r');

	if($handle)
	{
		$frob=fgetss($handle,4096);
		fclose($handle);
	}
	else
	{
		echo "Did not find the_key.txt file! Unable to proceed!";
		exit;
	}
	//echo 'my frob:'.$frob;
	//$frob="72157670660712761-1f30756e5258f9d3";

	$frob=str_replace("\n","",$frob);

	$api_key = "68e9a4df375a157a22a8d79e4e9a43bb";
    	$api_secret = "3b4e45208b0d7aaf";

    //echo "TEST";

	// Start the session since phpFlickr uses it but does not start it itself

	$conf = array(
	  'token' => $frob,
	  'key' => $api_key,
	  'secret' => $api_secret
	);
	require_once("phpFlickr.php");
	$f = new phpFlickr($conf['key'], $conf['secret']);
	$f->setToken($conf['token']);

	echo "i am a test";

	//change this to the permissions you will need
	$f->auth("write");

	echo "asdasd";

	echo "something";

	$path = "images";

	echo "something else";

	if ($handle = opendir($path)) {
		echo "test";
		while (false !== ($file = readdir($handle))) {
			if ('.' === $file) continue;
			if ('..' === $file) continue;

			echo $file.'<br>';

			//print_r(pathinfo($file));

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
						//print_r($file_info);
						if($file_info['extension']=="jpg" && strpos($file_info['basename'], ".jpg.jpg")===false)
						{
							process_and_upload($path."/".$file, $file_inner,$f);
							//exit;
						}
							//echo "ISFILE:".$file_inner."<br>";
					}

					closedir($handle_inner);
				}
			}

			continue;
			// do something with the file
			$f->sync_upload($path."/".$file);
			//break;
		}
		closedir($handle);
	}

	//($photo, $title = null, $description = null, $tags = null, $is_public = null, $is_friend = null, $is_family = null)

	//$f->async_upload("sample.jpg");

	echo "My Frob:".$frob;


	echo "I am out..";
	?>
