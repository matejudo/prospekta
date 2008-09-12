<?php

$xml_data = array();
$xml_album = array();
$xml_images = array();

class ImportsController extends AppController {
	// Models needed for this controller
	var $uses = array('Album', 'Image', 'Gallery', 'Tag');
    var $name = 'Imports';
	
	////
	// Attempt to import the data and files
	////
	function vandelay() {
		$this->checkSession();
		$this->verifyAjax();
		if ($this->data) {
			global $xml_data;
			if (function_exists('set_time_limit')) {
				set_time_limit(0);
			}
			$account = $this->Director->fetchAccount();
			$folder = IMPORTS . DS . $this->data['Import']['folder'];
			$xml_file = $folder . DS . 'images.xml';
			
			$xml_parser = xml_parser_create(); 
			xml_set_element_handler($xml_parser, a($this, '_startTag'), a($this, '_endTag')); 
			xml_set_character_data_handler($xml_parser, a($this, '_contents')); 

			$fp = fopen($xml_file, "r"); 
			$data = fread($fp, filesize($xml_file));
			$data = preg_replace_callback('/"([^"]*)"/', array(&$this, '_encodeForXML'), $data);
					
			if(!(xml_parse($xml_parser, $data, feof($fp)))){ 
			    die("Error on line " . xml_get_current_line_number($xml_parser));
			} 

			xml_parser_free($xml_parser); 

			fclose($fp);
			
			$this->data['Gallery']['name'] = $this->data['Import']['folder'];
			$this->Gallery->save($this->data);
			$gallery_id = $this->Gallery->getLastInsertId();
			
			$order = 1;
			
			foreach($xml_data as $album) {
				$album_id = 0;
				$path = ''; $lg = ''; $tn = ''; $director = ''; $local_path = '';
				
				$node_count = count($album);
				$a = $album[0];
				$data = array();
				$local_path = isset($a['LGPATH']) ? $a['LGPATH'] : '';
				$local_path = $this->_trimSlashes($local_path);
				
				$local_tn_path = isset($a['TNPATH']) ? $a['TNPATH'] : '';
				$local_tn_path = $this->_trimSlashes($local_tn_path);
				
				if (is_dir($folder . DS . $local_path)) {
					$data['Album']['active'] = 1;
					$data['Album']['name'] = isset($a['TITLE']) ? $a['TITLE'] : 'No name';
					$data['Album']['description'] = isset($a['DESCRIPTION']) ? $a['DESCRIPTION'] : '';
					//if (!empty($local_tn_path)) { $data['Album']['tn'] = 1; };
					$this->Album->create();
					$this->Album->save($data);
					$album_id = $this->Album->getLastInsertId();
					$path = "album-$album_id";
					$this->Album->id = $album_id;
					$album_data = $this->Album->read();
					
					if ($this->Director->makeDir(ALBUMS . DS . $path) && $this->Director->createAlbumDirs($path)) {
						$this->Album->saveField('path', $path);
						$lg = ALBUMS . DS . $path . DS . 'lg';
						$director = ALBUMS . DS . $path . DS . 'director';
						
						// Images
						for ($i = 1; $i < $node_count; $i++) {
							$data = array();
							$img = $album[$i];
							$original_src = $img['SRC'];
							$original_src = $this->_trimSlashes($original_src);
							$file = $folder . DS . $local_path . DS . $original_src;
							if (file_exists($file)) {
								$src = basename($file);
								$data['Image']['aid'] = $album_id;
								$data['Image']['src'] = $src;
								$data['Image']['title'] = isset($img['TITLE']) ? $img['TITLE'] : '';
								$data['Image']['caption'] = isset($img['CAPTION']) ? $img['CAPTION'] : '';
								$data['Image']['link'] = isset($img['LINK']) ? $img['LINK'] : '';
								$data['Image']['pause'] = isset($img['PAUSE']) ? $img['PAUSE'] : 0;
								$data['Image']['seq'] = $i;
								$this->Image->create();
								$this->Image->save($data);
								copy($file, $lg . DS . $src);
								
								if (file_exists($thumb)) {
									copy($thumb, $tn . DS . $src);
								}
							}
						}
						// Manage aTn
						if (isset($a['TN'])) {
							$atn = $this->_trimSlashes($a['TN']);
							$atn = $folder . DS . $atn;
							$file = basename($atn);
							$this->Album->saveField('aTn', $file);
							if (!file_exists($lg . DS . $file)) {
								$name = $path . '.' . $this->Director->returnExt($file);
								copy($atn, $lg . DS . $file);
								$data['Image']['aid'] = $album_id;
								$data['Image']['src'] = $file;
								$data['Image']['active'] = 0;
								$data['Image']['seq'] = 999;
								$this->Image->create();
								$this->Image->save($data);
							} else {
								
							}
						}
						
						// Audio
						if (isset($a['AUDIO'])) {
							$audio_file = $this->_trimSlashes($a['AUDIO']);
							$file = $folder . DS . $audio_file;
							copy($file, AUDIO . DS . basename($file));
							$this->Album->saveField('audioFile', basename($file));
							if (isset($a['AUDIOCAPTION'])) {
								$this->Album->saveField('audioCap', $a['AUDIOCAPTION']);
							}
						}
						
						$tag['Tag']['did'] = $gallery_id;
						$tag['Tag']['aid'] = $album_id;
						$tag['Tag']['display'] = $order;
						$this->Gallery->Tag->create();
						$this->Gallery->Tag->save($tag);
						$order++;
					}
				}
			}
			@rename($xml_file, $xml_file . '.done');
			$this->redirect('/galleries/refresh');
		}
	}
	
	////
	// Private functions to parse the XML
	////
	function _contents($parser, $data) { 
		// Not needed, all data is contained in attributes
	} 

	function _startTag($parser, $tag, $attr) { 
		global $xml_album, $xml_images;
		switch($tag) {
			case 'ALBUM':
				$xml_album[] = $attr;
				break;
			case 'IMG':
				$xml_images[] = $attr;
				break;
		}	
	} 

	function _endTag($parser, $tag){ 
		if ($tag == 'ALBUM') {
			global $xml_album, $xml_images, $xml_data;
			$xml_data[] = array_merge($xml_album, $xml_images);
			$xml_album = array(); $xml_images = array();
		}
	}
	
	function _trimSlashes($string) {
		if (strpos($string, '/') === 0) {
			$string = substr_replace($string, '', 0, 1);
		}
		$len = strlen($string);
		if (strrpos($string, '/') == ($len-1)) {
			$string = substr_replace($string, '', $len-1, $len);
		}
		return str_replace('/', DS, $string);
	}
	
	function _encodeForXML($matches) {
		$str = $matches[1];
		$str = html_entity_decode(stripslashes($str));
		$str = $this->_ent2ncr(htmlentities($str, ENT_NOQUOTES, 'UTF-8'));
		return '"' . $str . '"';
	}
	
	////
	// ent2ncr: Change entities to numeric entities for Flash
	////
	
	/*
	MODIFIED FROM THE FOLLOWING WORDPRESS PLUGIN

	Plugin Name: Entity2NCR
	Plugin URI: http://guff.szub.net/entity2ncr
	Description: Converts &raquo; and the like to their numeric equivalents.
	Version: 0.2
	Author: Kaf Oseo
	Author URI: http://szub.net

    Copyright (c) 2005, Kaf Oseo (http://szub.net)
    Entity2NCR is released under the GPL license
    http://www.gnu.org/licenses/gpl.txt

    This is a WordPress plugin (http://wordpress.org).

    WordPress is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License,
    or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
    General Public License for more details.

    For a copy of the GNU General Public License, write to:

    Free Software Foundation, Inc.
    59 Temple Place, Suite 330
    Boston, MA  02111-1307
    USA

    You can also view a copy of the HTML version of the GNU General
    Public License at http://www.gnu.org/copyleft/gpl.html

	*/

	function _ent2ncr($text) {
	    $to_ncr = array(
	        '&quot;' => '&#34;',
	        '&amp;' => '&#38;',
	        '&frasl;' => '&#47;',
	        '&lt;' => '&#60;',
	        '&gt;' => '&#62;',
	        '|' => '&#124;',
	        '&nbsp;' => '&#160;',
	        '&iexcl;' => '&#161;',
	        '&cent;' => '&#162;',
	        '&pound;' => '&#163;',
	        '&curren;' => '&#164;',
	        '&yen;' => '&#165;',
	        '&brvbar;' => '&#166;',
	        '&brkbar;' => '&#166;',
	        '&sect;' => '&#167;',
	        '&uml;' => '&#168;',
	        '&die;' => '&#168;',
	        '&copy;' => '&#169;',
	        '&ordf;' => '&#170;',
	        '&laquo;' => '&#171;',
	        '&not;' => '&#172;',
	        '&shy;' => '&#173;',
	        '&reg;' => '&#174;',
	        '&macr;' => '&#175;',
	        '&hibar;' => '&#175;',
	        '&deg;' => '&#176;',
	        '&plusmn;' => '&#177;',
	        '&sup2;' => '&#178;',
	        '&sup3;' => '&#179;',
	        '&acute;' => '&#180;',
	        '&micro;' => '&#181;',
	        '&para;' => '&#182;',
	        '&middot;' => '&#183;',
	        '&cedil;' => '&#184;',
	        '&sup1;' => '&#185;',
	        '&ordm;' => '&#186;',
	        '&raquo;' => '&#187;',
	        '&frac14;' => '&#188;',
	        '&frac12;' => '&#189;',
	        '&frac34;' => '&#190;',
	        '&iquest;' => '&#191;',
	        '&Agrave;' => '&#192;',
	        '&Aacute;' => '&#193;',
	        '&Acirc;' => '&#194;',
	        '&Atilde;' => '&#195;',
	        '&Auml;' => '&#196;',
	        '&Aring;' => '&#197;',
	        '&AElig;' => '&#198;',
	        '&Ccedil;' => '&#199;',
	        '&Egrave;' => '&#200;',
	        '&Eacute;' => '&#201;',
	        '&Ecirc;' => '&#202;',
	        '&Euml;' => '&#203;',
	        '&Igrave;' => '&#204;',
	        '&Iacute;' => '&#205;',
	        '&Icirc;' => '&#206;',
	        '&Iuml;' => '&#207;',
	        '&ETH;' => '&#208;',
	        '&Ntilde;' => '&#209;',
	        '&Ograve;' => '&#210;',
	        '&Oacute;' => '&#211;',
	        '&Ocirc;' => '&#212;',
	        '&Otilde;' => '&#213;',
	        '&Ouml;' => '&#214;',
	        '&times;' => '&#215;',
	        '&Oslash;' => '&#216;',
	        '&Ugrave;' => '&#217;',
	        '&Uacute;' => '&#218;',
	        '&Ucirc;' => '&#219;',
	        '&Uuml;' => '&#220;',
	        '&Yacute;' => '&#221;',
	        '&THORN;' => '&#222;',
	        '&szlig;' => '&#223;',
	        '&agrave;' => '&#224;',
	        '&aacute;' => '&#225;',
	        '&acirc;' => '&#226;',
	        '&atilde;' => '&#227;',
	        '&auml;' => '&#228;',
	        '&aring;' => '&#229;',
	        '&aelig;' => '&#230;',
	        '&ccedil;' => '&#231;',
	        '&egrave;' => '&#232;',
	        '&eacute;' => '&#233;',
	        '&ecirc;' => '&#234;',
	        '&euml;' => '&#235;',
	        '&igrave;' => '&#236;',
	        '&iacute;' => '&#237;',
	        '&icirc;' => '&#238;',
	        '&iuml;' => '&#239;',
	        '&eth;' => '&#240;',
	        '&ntilde;' => '&#241;',
	        '&ograve;' => '&#242;',
	        '&oacute;' => '&#243;',
	        '&ocirc;' => '&#244;',
	        '&otilde;' => '&#245;',
	        '&ouml;' => '&#246;',
	        '&divide;' => '&#247;',
	        '&oslash;' => '&#248;',
	        '&ugrave;' => '&#249;',
	        '&uacute;' => '&#250;',
	        '&ucirc;' => '&#251;',
	        '&uuml;' => '&#252;',
	        '&yacute;' => '&#253;',
	        '&thorn;' => '&#254;',
	        '&yuml;' => '&#255;',
	        '&OElig;' => '&#338;',
	        '&oelig;' => '&#339;',
	        '&Scaron;' => '&#352;',
	        '&scaron;' => '&#353;',
	        '&Yuml;' => '&#376;',
	        '&fnof;' => '&#402;',
	        '&circ;' => '&#710;',
	        '&tilde;' => '&#732;',
	        '&Alpha;' => '&#913;',
	        '&Beta;' => '&#914;',
	        '&Gamma;' => '&#915;',
	        '&Delta;' => '&#916;',
	        '&Epsilon;' => '&#917;',
	        '&Zeta;' => '&#918;',
	        '&Eta;' => '&#919;',
	        '&Theta;' => '&#920;',
	        '&Iota;' => '&#921;',
	        '&Kappa;' => '&#922;',
	        '&Lambda;' => '&#923;',
	        '&Mu;' => '&#924;',
	        '&Nu;' => '&#925;',
	        '&Xi;' => '&#926;',
	        '&Omicron;' => '&#927;',
	        '&Pi;' => '&#928;',
	        '&Rho;' => '&#929;',
	        '&Sigma;' => '&#931;',
	        '&Tau;' => '&#932;',
	        '&Upsilon;' => '&#933;',
	        '&Phi;' => '&#934;',
	        '&Chi;' => '&#935;',
	        '&Psi;' => '&#936;',
	        '&Omega;' => '&#937;',
	        '&alpha;' => '&#945;',
	        '&beta;' => '&#946;',
	        '&gamma;' => '&#947;',
	        '&delta;' => '&#948;',
	        '&epsilon;' => '&#949;',
	        '&zeta;' => '&#950;',
	        '&eta;' => '&#951;',
	        '&theta;' => '&#952;',
	        '&iota;' => '&#953;',
	        '&kappa;' => '&#954;',
	        '&lambda;' => '&#955;',
	        '&mu;' => '&#956;',
	        '&nu;' => '&#957;',
	        '&xi;' => '&#958;',
	        '&omicron;' => '&#959;',
	        '&pi;' => '&#960;',
	        '&rho;' => '&#961;',
	        '&sigmaf;' => '&#962;',
	        '&sigma;' => '&#963;',
	        '&tau;' => '&#964;',
	        '&upsilon;' => '&#965;',
	        '&phi;' => '&#966;',
	        '&chi;' => '&#967;',
	        '&psi;' => '&#968;',
	        '&omega;' => '&#969;',
	        '&thetasym;' => '&#977;',
	        '&upsih;' => '&#978;',
	        '&piv;' => '&#982;',
	        '&ensp;' => '&#8194;',
	        '&emsp;' => '&#8195;',
	        '&thinsp;' => '&#8201;',
	        '&zwnj;' => '&#8204;',
	        '&zwj;' => '&#8205;',
	        '&lrm;' => '&#8206;',
	        '&rlm;' => '&#8207;',
	        '&ndash;' => '&#8211;',
	        '&mdash;' => '&#8212;',
	        '&lsquo;' => '&#8216;',
	        '&rsquo;' => '&#8217;',
	        '&sbquo;' => '&#8218;',
	        '&ldquo;' => '&#8220;',
	        '&rdquo;' => '&#8221;',
	        '&bdquo;' => '&#8222;',
	        '&dagger;' => '&#8224;',
	        '&Dagger;' => '&#8225;',
	        '&bull;' => '&#8226;',
	        '&hellip;' => '&#8230;',
	        '&permil;' => '&#8240;',
	        '&prime;' => '&#8242;',
	        '&Prime;' => '&#8243;',
	        '&lsaquo;' => '&#8249;',
	        '&rsaquo;' => '&#8250;',
	        '&oline;' => '&#8254;',
	        '&frasl;' => '&#8260;',
	        '&euro;' => '&#8364;',
	        '&image;' => '&#8465;',
	        '&weierp;' => '&#8472;',
	        '&real;' => '&#8476;',
	        '&trade;' => '&#8482;',
	        '&alefsym;' => '&#8501;',
	        '&larr;' => '&#8592;',
	        '&uarr;' => '&#8593;',
	        '&rarr;' => '&#8594;',
	        '&darr;' => '&#8595;',
	        '&harr;' => '&#8596;',
	        '&crarr;' => '&#8629;',
	        '&lArr;' => '&#8656;',
	        '&uArr;' => '&#8657;',
	        '&rArr;' => '&#8658;',
	        '&dArr;' => '&#8659;',
	        '&hArr;' => '&#8660;',
	        '&forall;' => '&#8704;',
	        '&part;' => '&#8706;',
	        '&exist;' => '&#8707;',
	        '&empty;' => '&#8709;',
	        '&nabla;' => '&#8711;',
	        '&isin;' => '&#8712;',
	        '&notin;' => '&#8713;',
	        '&ni;' => '&#8715;',
	        '&prod;' => '&#8719;',
	        '&sum;' => '&#8721;',
	        '&minus;' => '&#8722;',
	        '&lowast;' => '&#8727;',
	        '&radic;' => '&#8730;',
	        '&prop;' => '&#8733;',
	        '&infin;' => '&#8734;',
	        '&ang;' => '&#8736;',
	        '&and;' => '&#8743;',
	        '&or;' => '&#8744;',
	        '&cap;' => '&#8745;',
	        '&cup;' => '&#8746;',
	        '&int;' => '&#8747;',
	        '&there4;' => '&#8756;',
	        '&sim;' => '&#8764;',
	        '&cong;' => '&#8773;',
	        '&asymp;' => '&#8776;',
	        '&ne;' => '&#8800;',
	        '&equiv;' => '&#8801;',
	        '&le;' => '&#8804;',
	        '&ge;' => '&#8805;',
	        '&sub;' => '&#8834;',
	        '&sup;' => '&#8835;',
	        '&nsub;' => '&#8836;',
	        '&sube;' => '&#8838;',
	        '&supe;' => '&#8839;',
	        '&oplus;' => '&#8853;',
	        '&otimes;' => '&#8855;',
	        '&perp;' => '&#8869;',
	        '&sdot;' => '&#8901;',
	        '&lceil;' => '&#8968;',
	        '&rceil;' => '&#8969;',
	        '&lfloor;' => '&#8970;',
	        '&rfloor;' => '&#8971;',
	        '&lang;' => '&#9001;',
	        '&rang;' => '&#9002;',
	        '&loz;' => '&#9674;',
	        '&spades;' => '&#9824;',
	        '&clubs;' => '&#9827;',
	        '&hearts;' => '&#9829;',
	        '&diams;' => '&#9830;'
	    );
	        foreach ($to_ncr as $entity => $ncr)
	        $text = str_replace($entity, $ncr, $text);
	    return $text;
	}
}

?>