<?php

class Image extends AppModel {
    var $name = 'Image';
	var $coldSave = false;
	var $belongsTo = array('Album' =>
                           array('className'  => 'Album',
                                 'foreignKey' => 'aid'
                           )
                     );

	////
	// callbacks to clear the cache
	////
	function afterSave() {
		if (!$this->coldSave) {
			$this->popCache();
		}
		return true;
	}
	
	function beforeDelete() {
		if (!$this->coldSave) {
			@$this->popCache(false);
		}
		return true;
	}
	
	function popCache($save = true) {
		$id = $this->id;
		$image = $this->read();
		$album_id = $image['Album']['id'];
		$this->cacheQueries = false;
		$count = $this->findCount(aa('aid', $album_id));
		if (!$save) { $count -= 1; }
		$this->Album->id = $album_id;
		if ($this->Album->read()) {
			$data['Album']['images_count'] = $count;
			$data['Album']['modified'] = date('Y-m-d H:i:s');
			$this->Album->save($data);
		}
	}
	
	function formTitle($title) {
		$image = $this->read();
		$set_to = str_replace('[img_name]', $image['Image']['src'], $title);
		$this->saveField('title', $set_to);
	}
	
	function formCaption($caption) {
		$image = $this->read();
		$set_to = str_replace('[img_name]', $image['Image']['src'], $caption);
		if (strpos($set_to, '[iptc_') !== false) {
			$path = ALBUMS . DS . $image['Album']['path'] . DS . 'lg' . DS . $image['Image']['src'];
			$path = ensureOriginal($path, $image['Album']['id']);
			$iptc = $this->iptc($path);
			$set_to = str_replace('[iptc_caption]', $iptc['caption'], $set_to);
		}
		$this->saveField('caption', $set_to);
	}
	
	function formLink($template, $album) {
		$i = $this->read();
		$src = $i['Image']['src'];
		
		if (isNotImg($src)) {
			return;
		}
		
		$arr = explode('__~~__', $template);
		$template = $arr[0];
		$target = $arr[1];
		
		$file_path = ALBUMS . DS . $album['Album']['path'];
		$path = DIR_HOST . '/albums/' . $album['Album']['path'];
		
		
		if (file_exists($file_path . DS . 'hr' . DS . $src)) {
			$source = $path . '/hr/' . $src;
			$specs = getimagesize($file_path . DS . 'hr' . DS . $src);
		} else {
			$source = $path . '/lg/' . $src;
			$specs = getimagesize($file_path . DS . 'lg' . DS . $src);
		}
		
		$path = DIR_HOST . '/albums/' . $album['Album']['path'];
		$template = r('[full_hr_url]', $source, $template);
		$template = r('[img_w]', $specs[0], $template);
		$template = r('[img_h]', $specs[1], $template);
		$template = r('[img_src]', $src, $template);	
		$data['Image']['link'] = $template;
		$data['Image']['target'] = $target;
		$this->save($data);
	}
	
	////
	// Parse IPTC data into readable array
	////
	function iptc($file) {
		$size = getimagesize($file, $info);
		$array = array();
		if (isset($info["APP13"])) { 
		    $iptc = iptcparse($info["APP13"]);
			if (is_array($iptc)) {
				$array = array( 
			        'caption' => $iptc["2#120"][0], 
			    	'graphic_name' => $iptc["2#005"][0], 
			        'urgency' => $iptc["2#010"][0],    
			    	'category' => $iptc["2#015"][0],    
					'supp_categories' => $iptc["2#020"][0], 
					'spec_instr' => $iptc["2#040"][0], 
					'creation_date' => $iptc["2#055"][0], 
					'photog' => $iptc["2#080"][0], 
					'credit_byline_title' => $iptc["2#085"][0], 
					'city' => $iptc["2#090"][0], 
					'state' => $iptc["2#095"][0], 
					'country' => $iptc["2#101"][0], 
					'otr' => $iptc["2#103"][0], 
					'headline' => $iptc["2#105"][0], 
					'source' => $iptc["2#110"][0], 
					'photo_source' => $iptc["2#115"][0], 
				);
			}
		}
		return $array;
	}
}

?>