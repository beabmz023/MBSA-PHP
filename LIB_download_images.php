<?php


include_once("LIB_parse.php");               # include parse library
include_once("LIB_http.php");                # include curl library
include_once("LIB_resolve_addresses.php");   # include address resolution library


function download_binary_file($file, $ref)
	{
	# Open a PHP/CURL session
	$s = curl_init();
	
	# Configure the CURL command
	curl_setopt($s, CURLOPT_URL, $file); // Define target site
	curl_setopt($s, CURLOPT_RETURNTRANSFER, TRUE);     // Return in string
	curl_setopt($s, CURLOPT_BINARYTRANSFER, 1);        // Indicate binary transfer
	curl_setopt($s, CURLOPT_REFERER, $ref);            // Referer value
	curl_setopt($s, CURLOPT_SSL_VERIFYPEER, FALSE);    // No certificate
	curl_setopt($s, CURLOPT_FOLLOWLOCATION, TRUE);     // Follow redirects
	curl_setopt($s, CURLOPT_MAXREDIRS, 4);             // Limit redirections to four
	
	# Execute the CURL command (Send contents of target web page to string)
	$downloaded_page = curl_exec($s);//將目標網頁內容送進字串
	
	# Close PHP/CURL session
	curl_close($s);
	
	# return file
	return $downloaded_page;
	}

function mkpath($path)
	{
	# Make the slashes are all single and lean the right way
	$path=preg_replace('/(\/){2,}|(\\\){1,}/','/',$path); 

	# Make an array of all the directories in path
	$dirs=explode("/",$path);

	# Verify that each directory in path exist. Create it if it doesn't
	$path="";
	foreach ($dirs as $element)
		{
		$path.=$element."/";
		if(!is_dir($path))      // Directory verified here
			mkdir($path);       // Created if it doesn't exist
		 }
	}
   

function download_images_for_page($target)
	{
		
	echo "target = $target\n";
	
	# Download the web page
	$web_page = http_get($target, $referer="");
	
	# Update the target in case there was a redirection
	$target = $web_page['STATUS']['url'];
	
	# Strip file name off target for use as page base
	$page_base=get_base_page_address($target);
	
	# Identify the directory where iamges are to be saved
	$save_image_directory = "saved_images_".str_replace("http://", "", $page_base);
	
	# Parse the image tags  解析圖片標籤
	$img_tag_array = parse_array($web_page['FILE'], "<img",">"); 
	
	if(count($img_tag_array)==0)
		{
		echo "No images found at $target\n";
		exit;
		}
 
 	
	# Echo the image source attribute from each image tag
	for($xx=0; $xx<count($img_tag_array); $xx++)
		{
		$image_path = get_attribute($img_tag_array[$xx],  $attribute="src");
		$image_path1 = get_attribute($img_tag_array[$xx],  $attribute="height");
		
		?>
		<style>/*CSS語法*/
	
		h1{text-align:center;} /*置中*/
																		
		</style>
		<h1><table  border="1">
		<tr><td>
		<?php echo " image: ".$image_path;//顯示鏈結  
		 	  echo "height: ".$image_path1;
		?>
		</td></tr></table></h1>
		<?php
		// " image: ".$image_path;     //不顯示
		$image_url = resolve_address($image_path, $page_base);
	   	?>
			<h1>
			<table  border="1">
			<h1><tr><td>    <img src = <?php echo $image_url ?> >   </td></tr></h1>     <!-- php鏈結由html顯示圖片 -->
			</table>
			</h1> 
			<?php
		
		if(get_base_domain_address($image_url) == get_base_domain_address($image_url))
			{
			# Make image storage directory for image, if one doesn't exist
			$directory = substr($image_path, 0, strrpos($image_path, "/"));
			$directory = str_replace(":", "-", $directory );
			$image_path = str_replace(":", "-", $image_path );
			echo "~~~~~~".$image_path."~~~";
			clearstatcache(); // clear cache to get accurate directory status
			if(!is_dir($save_image_directory."/".$directory))
				mkpath($save_image_directory."/".$directory);
			
			# Download the image, report image size
			$this_image_file =  download_binary_file($image_url, $ref="");
		    echo " size: ".strlen($this_image_file);  //顯示
			// " size: ".strlen($this_image_file);
			# Save the image 保存圖片
			if(stristr($image_url, ".jpg") || stristr($image_url, ".gif") || stristr($image_url, ".png") ||stristr($image_url, ".jpeg"))
				{
				
				$fp = fopen($save_image_directory."/".$image_path, "w");
				fputs($fp, $this_image_file);
				fclose($fp);

				echo "\n";
				}
			}

		else
			{
			echo "\nSkipping off-domain image.\n";
			}
		}
	}

?>