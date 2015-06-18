<?php
#   Funktionen - AvatarCrop by Lord|Schirmer
#   Support: www.ilch.de

defined ('main') or die ('no direct access');

# file extension
function check_extension($type) {
	$file_types = array(
		'image/pjpeg' => 'jpg',
		'image/jpeg'  => 'jpg',
		'image/jpeg'  => 'jpeg',
		'image/gif'   => 'gif',
		'image/X-PNG' => 'png',
		'image/PNG'   => 'png',
		'image/png'   => 'png',
		'image/x-png' => 'png',
		'image/JPG'   => 'jpg',
		'image/GIF'   => 'gif'
	);
	if (!array_key_exists($type, $file_types)) {
		return false;
	} else {
		return true;
	}
}

# crop image function
function crop_image ($imgPath, $cropPath, $newWidth, $newHeight, $xPos, $yPos, $wPos, $hPos) {

	# check gd lib
	if (!extension_loaded('gd') && !extension_loaded('gd2')) {
		trigger_error("GD Library wird nicht unterstützt!", E_USER_WARNING);
		return false;
	}
	
	# check image format
	$imgInfo = getimagesize($imgPath);
	switch ($imgInfo[2]) {
		case 1: $oldImg = imagecreatefromgif($imgPath); break;
		case 2: $oldImg = imagecreatefromjpeg($imgPath); break;
		case 3: $oldImg = imagecreatefrompng($imgPath); break;
		default: trigger_error('Nicht unterstützter Dateityp!', E_USER_WARNING); break;
	}
	
	$newImg = imagecreatetruecolor($newWidth, $newHeight);
	
	# transparenz gif
	if($imgInfo[2] == 1) {
		$transIndex = imagecolortransparent($oldImg);
		if ($transIndex >= 0) {
			$transColor = imagecolorsforindex($oldImg, $transIndex);
			$transIndex = imagecolorallocate($newImg, $transColor['red'], $transColor['green'], $transColor['blue']);
			imagefill($newImg, 0, 0, $transIndex);				
			imagecolortransparent($newImg, $transIndex);
		}

	# transparenz png
	} elseif ($imgInfo[2] == 3) {
		imagealphablending($newImg, false);
		imagesavealpha($newImg, true);
		$transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
		imagefilledrectangle($newImg, $xPos, $yPos, $wPos, $hPos, $transparent);
	}

	# create image
	imagecopyresampled($newImg, $oldImg, 0, 0, $xPos, $yPos, $newWidth, $newHeight, $wPos, $hPos);
 
	switch ($imgInfo[2]) {
		case 1: imagegif($newImg, $cropPath); break;
		case 2: imagejpeg($newImg, $cropPath, 100); break;
		case 3: imagepng($newImg, $cropPath, 0); break;
		default: trigger_error('Änderung gescheitert!', E_USER_WARNING); break;
	}

	return (TRUE);
}
?>