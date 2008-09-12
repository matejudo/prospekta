<?php

class AlbumsController extends AppController {
    var $name = 'Albums';
	var $uses = array('Album', 'Image', 'Tag', 'User', 'Gallery');
	var $helpers = array('Html', 'Javascript', 'Ajax');
	
	var $non_ajax_actions = array('index', 'edit', 'process', 'do_process', 'refresh_generate_pane', 'reorder', '_list');
	
	// Only logged in users should see this controller's actions
 	function beforeFilter() {
		// Protect ajax actions
		if (!in_array($this->action, $this->non_ajax_actions)) {
			$this->verifyAjax();
		}
		// Check session
		$this->checkSession();
	}
	
	////
	// Albums listing
	////
	function index() {
		$this->_dataForListing();
	}
	
	////
	// Create album
	////
	function create() {
		if ($this->Album->save($this->data)) {
			// Make directories and set path
			$this->Album->id = $this->Album->getLastInsertId();
			$path = 'album-' . $this->Album->id;
			if ($this->Director->makeDir(ALBUMS . DS . $path) &&
				$this->Director->createAlbumDirs($path))
			{
				// Directories were created successfully, go ahead with new album and redirection
				$this->Album->saveField('path', $path);
				
				if (isset($this->data['quick'])) {
					$this->set('all_albums', $this->Album->findAll(null, null, 'name', null, 1, -1));
				} elseif (isset($this->data['dash'])) {
					$recent = $this->Album->findAll(null, null, 'Album.modified DESC', 5, 1, -1);
					$this->set('albums', $recent);
				}
				
				// Render redirect via JS
				$this->set('new_id', $this->Album->id);
				$this->set('tab', 'upload');
				$this->render('after_create', 'ajax');
			} else {
				// Directory creation failed, we have a permission problem. Delete the album and notify user
				$this->Album->delete();
				$this->render('creation_failure', 'ajax');
			}
		}	
	}
	
	////
	// Album edit pane
	////
	function edit($id, $tab = 'summary', $part_id = 0) {
		$this->pageTitle = __('Albums', true);
		$this->Album->id = $id;
		$this->Album->recursive = 2;
		$this->data = $this->Album->read();
		
		switch($tab) {
			case('summary'):
				$this->set('recent_images', $this->Image->findAll(aa('aid', $id, 'Image.active', 1), null, 'Image.created DESC', 9));
				$this->set('updated_by', $this->User->find(aa('id', $this->data['Album']['updated_by'])));
				$this->set('created_by', $this->User->find(aa('id', $this->data['Album']['created_by'])));
				break;
				
			case('options'):
				$this->set("title_check", $this->Image->findAll("aid = $id AND title IS NOT NULL AND title <> ''"));
				$this->set("link_check", $this->Image->findAll("aid = $id AND link IS NOT NULL AND link <> ''"));
				$this->set("captions_check", $this->Image->findAll("aid = $id AND caption IS NOT NULL AND caption <> ''"));
				$templates_folder = new Folder(PLUGS . DS . 'links');
				$link_templates = $templates_folder->ls(true, false);
				$this->set('link_templates', $link_templates[1]);
				$custom_templates_folder = new Folder(CUSTOM_PLUGS . DS . 'links');
				$custom_link_templates = $custom_templates_folder->ls(true, false);
				$this->set('custom_link_templates', $custom_link_templates[0]);
				break;
				
			case('content'):
				$this->set('images', $this->data['Image']);
				$this->set('selected_id', $part_id);
				if (function_exists('imagerotate') || $this->Kodak->gdVersion() >= 3) {
					$rotate = true;
				} else {
					$rotate = false;
				}
				$this->set('rotate', $rotate);
				break;
			
			case('audio'):
				$this->set('mp3s', $this->Director->directory(AUDIO, 'mp3,MP3'));
				break;
				
			case('upload'):
				$this->set('writable', $this->Director->setAlbumPerms($this->data['Album']['path']));
				$this->set('other_writable', $this->Director->setOtherPerms());
				// Check if any new files have been uploaded via FTP
				$files = $this->Director->directory(ALBUMS . DS . $this->data['Album']['path'] . DS . 'lg', 'accepted');
				if (count($files) > count($this->data['Image'])) {
					set_time_limit(0);
					$noobs = array();
					foreach($files as $file) {
						if (strpos($file, '___tn___') === false) {
							$img = $this->Image->find(aa('src', $file, 'aid', $id));
							if (empty($img)) {
								$new['Image']['aid'] = $id;
								$new['Image']['src'] = $file;
								$this->Image->create();
								if ($this->Image->save($new)) {
									$noobs[] = $file;
								}
							}
						}
					}
					$this->Album->reorder($id);
					$this->Album->cacheQueries = false;
					$this->data = $this->Album->read();
					$this->set('noobs', $noobs);
				}
				break;
		}
		
		$this->Album->recursive = -1;
		$this->set('all_albums', $this->Album->findAll(null, null, 'name'));
		$this->Album->recursive = -1;
		$this->set('other_albums', $this->Album->findAll("id <> $id", null, 'name'));
		$this->set('album', $this->data);
		$this->set('tab', $tab);
		$this->set('thumbs', !empty($this->data['Album']['thumb_specs']));
	}
	
	////
	// Update album
	////
	function update($id, $refer = '') {
		$this->Album->id = $id;
		if ($this->Album->save($this->data)) {
			$album = $this->Album->read();
			$this->set('album', $album);
		}
	}
	
	////
	// Delete an album
	////
	function delete() {
		$album = $this->Album->find($this->data);
		$albums = $this->Album->findAll("path = '{$album['Album']['path']}'");
		
		// Delete the album from the DB
		if ($this->Album->del($album['Album']['id'], true)) {
			if (!empty($album['Album']['path'])) {
				// Remove the directory only if no other albums use it
				if (count($albums) == 1) {
					$dir = ALBUMS . DS . $album['Album']['path'];
					$this->Director->rmdirr($dir);
				}
			}
			$this->_dataForListing();
			$this->render('list', 'ajax');
		}
	}
	
	////
	// Toggles albums active and inactive
	////
	function toggle($id) {
		$this->Album->id = $id;
		$album = $this->Album->read();
		if ($this->Album->save($this->data)) {
			if ($this->data['Album']['active']) {
				if (!$album['Album']['active']) {
					$main = $this->Gallery->find(aa('main', 1));
					$tag['Tag']['did'] = $main['Gallery']['id'];
					$tag['Tag']['aid'] = $id;
					$this->Gallery->Tag->save($tag);
				}	
			} else {
				$tag_table = $this->Tag->tablePrefix . $this->Tag->table;
				$this->Tag->query("DELETE FROM $tag_table WHERE aid = $id");
			}
		}
		$this->Album->recursive = 2;
		$album = $this->Album->read();
		$this->set('album', $album);
		$this->set('updated_by', $this->User->find(aa('id', $album['Album']['updated_by'])));
		$this->set('created_by', $this->User->find(aa('id', $album['Album']['created_by'])));
	}
	
	////
	// Reset order type and refresh the image order as needed
	////
	function order_type($id) {
		$this->Album->id = $id;
		$this->Album->save($this->data);
		$this->Album->cacheQueries = false;
		if ($this->Album->reorder($id)) {
			$this->data = $this->Album->read();
			$this->set('images', $this->data['Image']);
			$this->set('album', $this->data);
			$this->set('tab', 'images');
			if (function_exists('imagerotate') || $this->Kodak->gdVersion() >= 3) {
				$rotate = true;
			} else {
				$rotate = false;
			}
			$this->set('rotate', $rotate);
			$this->Album->recursive = -1;
			$this->set('all_albums', $this->Album->findAll(null, null, 'name'));
			$this->Album->recursive = -1;
			$this->set('other_albums', $this->Album->findAll("id <> $id", null, 'name'));
			$this->render('order_type', 'ajax');
		}
	}
	
	////
	// Reorder album after image upload
	////
	function reorder($id) {
		if ($this->Album->reorder($id)) {
			$this->redirect("/albums/edit/$id/content");
			exit;
		}
	}
	
	function _dataForListing() {
		if (isset($_COOKIE['album_sort'])) {
			$arr = explode('__', $_COOKIE['album_sort']);
			$this->set('sort_on', $arr[0]);
			$this->set('sort_str', "sortfirst{$arr[1]}");
		} else {
			$this->set('sort_on', '');
		}
		$this->set('writable', $this->Director->setPerms(ALBUMS));
		$this->set('albums', $this->Album->findAll(null, null, 'Album.name', null, 1, -1));
	}
}

?>