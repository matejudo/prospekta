<?php

class SiteController extends AppController {
	// Models needed for this controller
	var $uses = array('Album', 'Gallery', 'Slideshow', 'User');
	// Helpers
	var $helpers = array('Html', 'Javascript', 'Ajax');
    var $name = 'Site';
	// Data action does not need sessions
	var $disableSessions = array('data', 'old_index', 'translate_js');

	////
	// Application Snapshot
	////
	function index() {
		$this->checkSession();
		$this->pageTitle = __('Snapshot', true);


		// Find albums
		$this->set('all_albums', $this->Album->findAll(null, null, 'name', null, 1, -1));

		// Get galleries
		$this->Gallery->unbindModel(array('hasMany' => a('Tags')));

		// Recent modified albums
		$recent = $this->Album->findAll(null, null, 'Album.modified DESC', 5, 1, -1);
		$this->set('albums', $recent);

		// Recent modified galleries
		$recent = $this->Gallery->findAll(null, null, 'Gallery.modified DESC', 5, 1, -1);
		$this->set('galleries', $recent);

		// User stats
		$this->set('image_count', $this->Album->Image->findCount(aa('Image.created_by', $this->Session->read('User.id'))));
		$last_visit = $this->Cookie->read('LastVisit');
		if ($last_visit) {
			if ((time() - $last_visit) > 86400) {
				$this->set('last_visit', intval($last_visit));
			}
		}
		$this->Cookie->write('LastVisit', time(), true, '+1 year');
		$this->set('writable', ($this->Director->setPerms(ALBUMS) && $this->Director->setPerms(AUDIO)));


		$this->set('recent_images', $this->Album->Image->findAll(array('not' => array('Image.src' => 'NULL'), aa('Image.active', 1)), null, 'Image.created DESC', 24));
	}

	function old_index() {
		$this->redirect('/snapshot'); exit;
	}

	////
	// XML output
	////
	function data($gid = 'no', $album = 0, $specs = null) {
		$this->set('controller', $this);
		if (is_null($specs) || empty($specs)) {
			$this->pageTitle = __('Error', true);
			$this->set('account', $this->Director->fetchAccount());
			$this->render('xml_error', 'simple');
			exit;
		}
		if (function_exists('set_time_limit')) {
			set_time_limit(0);
		}

		// Start building path to cache file
		$path_to_cache = XML_CACHE . DS . 'images';

		// Decide whether to serve a gallery, individual album, or full feed
		if ($album != 0) {
			$id = $album;
			$path_to_cache .= '_album_' . $id;
			$albums = $this->Album->findAll(aa("Album.id", explode(',', $id)), null, "FIELD(id, $id)");
		} else {
			$id = $gid;
			$path_to_cache .= '_gallery_' . $id;
			loadModel('Tag');
			$this->Tag =& new Tag();
			$albums = $this->Tag->findAll("did = $id", null, 'display');
		}

		$sp = explode('_', $specs);
		$w = $sp[0]; $h = $sp[1]; $s = $sp[2]; $q = $sp[3]; $sh = $sp[4];
		$tw = $sp[5]; $th = $sp[6]; $ts = $sp[7]; $tq = $sp[8]; $tsh = $sp[9];
		$pw = $sp[10]; $ph = $sp[11]; $ps = $sp[12];
		$path_to_cache .= "_{$w}_{$h}_{$s}_{$q}_{$sh}_{$tw}_{$th}_{$ts}_{$tq}_{$tsh}_{$pw}_{$ph}_{$ps}";

		$this->set('specs', $sp);
		$this->set('albums', $albums);
		// Finish up xml_cache path
		$path_to_cache .= '.xml';
		$this->set('path_to_cache', $path_to_cache);

		// Render w/o layout
		$this->render('data', 'ajax');
	}
}

?>