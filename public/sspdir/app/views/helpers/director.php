<?php

class DirectorHelper extends AppHelper {
	var $helpers = array('Html');
	////
	// Return background for album thumb display in interface
	////
	function albumBg($value, $path) {
		$bg = '#242424';
		if (empty($value)) {
 			$value = DIR_HOST . '/app/webroot/img/default_album.gif';
		} else {
			$value = $this->p($value, $path, 48, 37, 1, 70, 0, 50, 50);
		}
		$bg .= ' url(' . str_replace(' ', '%20', $value) . ') no-repeat center;';
		return $bg;
	}
	
	////
	// Truncate long stuff
	////
	function trunc($str, $len) {
		if (strlen($str) < $len) {
			return $str;
		} else {
			return substr($str, 0, $len) . '...';
		}
	}
	
	////
	// Style CSS writer
	////
	function css($file) {
		return '<link rel="stylesheet" type="text/css" href="' . DIR_HOST . $file . '" />';
	}
	
	function _date($format, $date) {
		setlocale(LC_TIME, explode(',', __('en_US', true)));
		if (strpos($date, '-') !== false) {
			$date = strtotime($date);
		}
		return r('  ', ' ', strftime($format, $date));
	}
	
	function p() {
		$args = func_get_args();
		$args = join(',', $args);
		$crypt = convert($args);
		return DIR_HOST . '/p.php?a=' . $crypt;
	}

	function getVidThumb($src, $path, $w = 84, $h = 63, $sq = 1, $q = 70, $sh = 1, $tail = false) {
		$pos = strrpos($src, '.');
		$clean = substr($src, 0, $pos);
		$custom = glob(ALBUMS . DS . $path . DS . 'lg' . DS . '___tn___' . $clean . '.*');
		if (!empty($custom)) {
			$filename = $this->p(basename($custom[0]), $path, $w, $h, $sq, $q, $sh, 50, 50);
			if ($tail) { $filename .= '||v'; }
		}else if (isVideo($src)) {
			$ext = strtolower(substr($src, $pos+1, strlen($src)));
			$filename = DIR_HOST . '/app/webroot/img/default_' . $ext . '.gif';
		} else if (isSwf($src)) {
			$filename = DIR_HOST . '/app/webroot/img/default_swf.gif';
		}
		return $filename;
	}
	
	////
	// Avatar fetch
	////
	function avatar($user_id, $w = 48, $h = 48, $extra_styles = null) {
		$out = '<img src="' . DIR_HOST . '/';
		$avs = glob(AVATARS . DS . $user_id . '.*');
		if (!empty($avs)) {
			$out .= 'albums/avatars/' . basename($avs[0]) . '?' . filemtime(AVATARS . DS . basename($avs[0]));
		} else {
			$out .= 'app/webroot/img/default_avatar.gif';
		}
		$out .= '" width="' . $w . '" height="' . $h . '" alt="Avatar" class="gray-edge"';
		if (!is_null($extra_styles)) {
			$out .= ' style="' . $extra_styles . '"';
		}
		$out .= ' />';
		return $out;
	}
	
	////
	// Dialogue wrappers
	////
	function preDialogue($id, $show = false) {
		return '<div id="' . $id . '" class="dialogue-wrap"' . ($show ? '' : 'style="display:none;"') . '><div class="dialogue"><div class="dialogue-content"><div class="wrap">';
	}
	
	function postDialogue() {
		return '</div></div></div></div>';
	}
	
	function generateLinkTemplateDesc($type) {
		if (empty($type)) {
			return __('No links populated', true);
		} else {
			return __('Custom links', true);
		}
 	}
	
	////
	// Cache buster
	////
	function randomStr() {
		return substr(md5(uniqid(microtime())), 0, 6);
	}
	
	////
	// Clean up string for XML rendering
	////
	function encodeForXML($str, $br = false) {
		$str = html_entity_decode(stripslashes($str));
		if ($br) { $str = nl2br($str); }
		$str = $this->ent2ncr(htmlentities($str, ENT_QUOTES, 'UTF-8'));
		if ($br) { $str = eregi_replace("\n|\r", '', $str); }
		return $str;
	}
	
	function recentList($imgs, $max, $w, $h, $album = false) {
		$o = '<ol class="photo-thumbs-list">';
		if ($album):
			$row = 3;
		else:
			$row = 4;
		endif;
		
		$i = $total = 1;
		foreach($imgs as $img):
			$o .= '<li' . ($i == $row ? ' class="end"' : '') . '>';
			$o .= '<span class="preview-me" onclick="Preview.bury();location.href=\'' . $this->Html->url("/albums/edit/{$img['Album']['id']}/content/{$img['Image']['id']}") . '\'"><span>';
					
			if (isNotImg($img['Image']['src'])):
				$o .= $this->getVidThumb($img['Image']['src'], $img['Album']['path'], 250, 250, 0, 70, 1);
			else:
				$arr = unserialize($img['Image']['anchor']);
				if (empty($arr)) {
					$x = $y = 50;
				} else {
					$x = $arr['x'];
					$y = $arr['y'];
				}
				$o .= $this->p($img['Image']['src'], $img['Album']['path'], 250, 250, 0, 70, 1, $x, $y);
			endif;
		
			$o .= '||' . $img['Image']['src'] . '||' . $this->_date(__('%m/%d/%Y', true), $img['Image']['created']) . '||';
			if ($album):
				$o .= '_';
			else:
				$o .= $img['Album']['name'];
			endif;
			$o .= '||';
				
			if (isNotImg($img['Image']['src'])):
				$o .= $this->getVidThumb($img['Image']['src'], $img['Album']['path'], 84, 63, 1, 70, 1, true);
			else:
			 	$o .= $this->p($img['Image']['src'], $img['Album']['path'], 84, 63, 1, 70, 1, $x, $y);
			endif;
		
			$o .= '</span>';
			$o .= $this->Html->image('loading_ei.gif', array('width' => $w, 'height' => $h, 'alt' => 'Image'));
			$o .= $this->Html->image('vid_overlay.gif', array('width' => 15, 'height' => 15, 'class' => 'video-overlay', 'style' => 'display:none;'));
			$o .= '</span></li>';

			if ($i < $row) { $i++; } else { $i = 1; }
			$total++;
		endforeach;
		
		if ($total <= $max):
			for ($j = $total; $j <= $max; $j++):
				$o .= '<li' . ($i == $row ? ' class="end"' : '') . '>';
				$o .= $this->Html->image('default_image.gif', array('width' => $w, 'height' => $h, 'alt' => 'Image'));
				$o .= '</li>';
			$i < $row ? $i++ : $i = 1; endfor;			
		endif;
		$o .= '</ol>';
		return $o;
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

	function ent2ncr($text) {
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