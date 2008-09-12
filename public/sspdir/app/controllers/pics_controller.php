<?php

class PicsController extends AppController {
	// Models needed for this controller
	var $uses = array('Album', 'Image');
    var $name = 'Images';
	
	// Only logged in users should see this controller's actions
 	function beforeFilter() {
		// Protect ajax actions
		$this->verifyAjax();
		$this->checkSession();
	}
	
	////
	// Edit an image
	////
	function edit($id) {
		$this->Image->id = $id;
		$this->data = $this->Image->read();
		$this->set('i', $this->data['Image']);
		$this->set('a', $this->data['Album']);
		$rel_path = str_replace('/index.php?', '', $this->base) . '/albums/' . $this->data['Album']['path'] . '/lg/' . $this->data['Image']['src'];
		$full_path = ALBUMS . DS . $this->data['Album']['path'] . DS . 'lg' . DS . $this->data['Image']['src'];
		$full_path = ensureOriginal($full_path, $this->data['Album']['id']);
		if (strpos($full_path, 'http://') !== false) {
			$rel_path = $full_path;
		}
		$this->set('is_album_thumb', $this->data['Image']['src'] == $this->data['Album']['aTn']);
		$this->set('rel_path', $rel_path);
		$this->set('full_path', $full_path);
		$this->set('tn_path', ALBUMS . DS . $this->data['Album']['path'] . DS . 'tn' . DS . $this->data['Image']['src']);
		$this->render('edit', 'ajax');
	}
	
	////
	// Update image properties
	////
	function update($id) {
		$this->Image->id = $id;
		$image = $this->Image->read();
		$is_thumb = $image['Album']['aTn'] == $image['Image']['src'];
		
		// If they made a change re: album preview
		if ($this->params['form']['album-thumb'] > 0) {
			$thumb = $image['Image']['src'];
			$this->data['Image']['active'] = $active = $this->params['form']['album-thumb'] - 1;
			$thumb_id = $image['Image']['id'];
		} elseif ($is_thumb && $this->params['form']['album-thumb'] == 0) {
			$thumb = '';
			$thumb_id = 0;
			$active = $image['Image']['active'];
		}
		
		$this->set('thumb', $thumb_id);
		$this->set('active', $active);
		$this->set('id', $image['Image']['id']);
		
		$this->Image->save($this->data);
		
		if (isset($thumb)) {
			$this->Album->id = $image['Album']['id'];
			$data['Album']['aTn'] = $thumb;
			$this->Album->save($data);
		}
		
	}
	
	function anchor($id) {
		$this->Image->id = $id;
		$this->Image->saveField('anchor', serialize($this->data));
		$image = $this->Image->read(null, $id);
		$this->_clearCache($image['Image']['src'], ALBUMS . DS . $image['Album']['path']);
		$this->set('image', $image);
		$this->render('anchor', 'ajax');
		exit;
	}
	
	////
	// Delete an image
	////
	function delete() {
		$ids = explode(',', $this->data['Image']['id']);
		$this->Image->coldSave = true;
		$album_id = null;
		foreach($ids as $id) {
			$this->Image->id = $id;
			$image = $this->Image->read();
			$album_path = $image['Album']['path'];
			$albums = $this->Album->findAll(aa('path', $album_path));
			if (is_null($album_id)) {
				$album_id = $image['Album']['id'];
			}
			// Delete the image from the DB
			$this->Image->del();
		
			// Delete it from the filesystem if no other albums use this path
			if (count($albums) == 1) {
				$path = ALBUMS . DS . $album_path . DS;
				@unlink($path . 'director' . DS . $image['Image']['src']);
				@unlink($path . 'lg' . DS . $image['Image']['src']);
				if (isVideo($image['Image']['src'])) {
					$src = $image['Image']['src'];
					$partial = r($this->Director->returnExt($src), '', $src);
					$caches = glob($path . '*' . DS . '___tn___' . $partial . '*');
					if (!empty($caches)) {
						foreach($caches as $cache) {
							@unlink($cache);
						}
					}
				}
			}
			if ($image['Image']['src'] == $image['Album']['aTn']) {
				$this->Album->id = $image['Album']['id'];
				$this->Album->saveField('aTn', '');
			}
			$this->_clearCache($image['Image']['src'], $path);
		}
		$this->Image->coldSave = true;
		$this->Album->id = $album_id;
		$data['Album']['modified'] = date('Y-m-d H:i:s');
		$this->Album->save($data);
		exit;
	}
	
	////
	// Copies an image from one album to the other
	////
	function copy() {
		$ids = explode(',', $this->data['copy']['id']);
		foreach($ids as $id) {
			$image = $this->Image->read(null, $id);
			$path = ALBUMS . DS . $image['Album']['path'];
			$lg = $path . DS . 'lg' . DS . $image['Image']['src'];
			$source = ensureOriginal($lg, $image['Album']['id']);
		
			$album = $this->Album->read(null, $this->data['target']['id']);
			$target_path = ALBUMS . DS . $album['Album']['path'] . DS . 'lg';
			copy($source, $target_path . DS . $image['Image']['src']);
			
			if (isNotImg($image['Image']['src'])) {
				$customs = glob($path . DS . 'lg' . DS . '___tn___' . r($this->Director->returnExt($image['Image']['src']), '', $image['Image']['src']) . '*');
				if (!empty($customs)) {
					copy($customs[0], $target_path . DS . basename($customs[0]));
				}
			}
			
			$check = $this->Image->findAll("aid = $id AND src = '{$image['Image']['src']}'");

			if (empty($check)) {
				$noob['Image']['src'] = $image['Image']['src'];
				$noob['Image']['aid'] = $album['Album']['id'];
				$noob['Image']['title'] = $image['Image']['title'];
				$noob['Image']['caption'] = $image['Image']['caption'];
				$noob['Image']['created'] = $noob['Image']['modified'] = date('Y-m-d H:i:s');
				$this->Image->create();
				$this->Image->save($noob);
				$image_id = $this->Image->getLastInsertId();
			} else {
				$image_id = $check['Image']['id'];
			}
		
			$this->Director->postProcessTemplates($album, $image_id);
		}
		exit;
	}
	
	////
	// Rotate image
	////
	function rotate() {
		$ids = explode(',', $this->data['rotate']['id']);
		$degree = $this->data['rotate']['deg'];
		$images = $this->Image->findAll(aa('Image.id', $ids));
		foreach($images as $image) {
			// Paths
			$path = ALBUMS . DS . $image['Album']['path'];

			$lg_local = $path . DS . 'lg' . DS . $image['Image']['src'];
		
			$lg_original = ensureOriginal($lg_local, $image['Album']['id']);
			
			$this->Kodak->rotate($lg_original, $lg_local, $degree);
			
			$this->_clearCache($image['Image']['src'], $path);
		}
		$this->set('images', $images);		
	}
	
	////
	// Updates image order
	////
	function order() {
		// On really large albums, this might take a while
		if (function_exists('set_time_limit')) {
			set_time_limit(0);
		}
		$order = $this->params['form']['image-view'];		
		$this->Image->coldSave = true;
		$album_id = null;
		while (list($key, $val) = each($order)) {
			$key++;
			$this->Image->id = $val;
			$this->Image->saveField('seq', $key);
			if (is_null($album_id)) {
				$img = $this->Image->read(null, $val);
				$album_id = $img['Album']['id'];
			}
		}
		$this->Image->coldSave = false;
		$this->Album->id = $album_id;
		$data['Album']['modified'] = date('Y-m-d H:i:s');
		$this->Album->save($data);
	}
	
	////
	// Set titles on all images in an album
	////
	function titles($id) {
		// On really large albums, this might take a while
		if (function_exists('set_time_limit')) {
			set_time_limit(0);
		}
		
		$this->Image->coldSave = true;
		if ($this->data['regen']) {
			$images = $this->Album->returnImages($id);
			foreach ($images as $i) {
				$this->Image->id = $i['id'];
				$this->Image->formTitle($this->data['Album']['title_template']);
			}
		}
		$this->Image->coldSave = false;
		
		$this->Album->id = $id;
		$this->Album->save($this->data);
		exit();
	}
	
	////
	// Set captions on all images in an album
	////
	function captions($id) {
		// On really large albums, this might take a while
		if (function_exists('set_time_limit')) {
			set_time_limit(0);
		}
		
		$this->Image->coldSave = true;
		if ($this->data['regen']) {
			$images = $this->Album->returnImages($id);
			foreach ($images as $i)	{
				$this->Image->id = $i['id'];
				$this->Image->formCaption($this->data['Album']['caption_template']);
			}
		}
		$this->Image->coldSave = false;
		
		$this->Album->id = $id;
		$this->Album->save($this->data);
		exit();
	}
	
	////
	// Set links on all images in an album
	////
	function links($id)	{
		// On really large albums, this might take a while
		if (function_exists('set_time_limit')) {
			set_time_limit(0);
		}
		
		$action = urldecode($this->data['Album']['link_template']);
		$this->Album->id = $id;
		
		$this->Image->coldSave = true;
		if ($this->data['regen']) {
			$images = $this->Album->returnImages($id);
			$album = $this->Album->read();
			
			foreach($images as $i) {
				$this->Image->id = $i['id'];
				$this->Image->formLink($action, $album);
			}
		}
		$this->Image->coldSave = false;
		
		$this->Album->save($this->data);
		$this->set('type', $action);
		$this->render('links', 'ajax');
	}
	
	function toggle() {
		$new_val = $this->data['value'];
		$ids = $this->data['ids'];
		$ids = explode(',', $ids);
		foreach($ids as $id) {
			$this->Image->id = $id;
			$data['Image']['active'] = $new_val;
			$this->Image->save($data);
		}
		exit;
	}
	
	function assign_thumb($id) {
		$source = $this->Image->read(null, $this->data['source']);
		$tgt = $this->Image->read(null, $id);
		$path = ALBUMS . DS . $source['Album']['path'] . DS . 'lg';
		$path_glob = ALBUMS . DS . $source['Album']['path'] . DS . '*';
		$base = $this->Director->returnExt($tgt['Image']['src']);
		$ext = $this->Director->returnExt($source['Image']['src']);
		$new = '___tn___' . r('.' . $base, '.' . $ext, $tgt['Image']['src']);
		$old = '___tn___' . r('.' . $base, '.', $tgt['Image']['src']) . '*';
		$leaving = glob($path_glob . DS . $old );
		foreach($leaving as $l) {
			unlink($l);
		}
		copy($path . DS . $source['Image']['src'], $path . DS . $new);
		exit;
	}
	
	////
	// Clear caches for a give image
	////
	function _clearCache($str, $path) {
		$caches = glob($path . DS . 'cache' . DS . $str . '*');
		if (!empty($caches)) {
			foreach($caches as $cache) {
				@unlink($cache);
			}
		}		
	}
}

?>