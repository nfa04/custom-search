<?php
ini_set('display_errors',1);
//error_reporting(E_ALL);

define('SITE',$_GET['s']);
define('RECURSIVE_MODE',(bool)$_GET['r']);
define('CRAWL_EXTERNAL',(isset($_GET['ce']) ? true : false));
define('POINTS',array(
'title' => 5,
'meta_keywords' => 3,
'meta_description' => 1.5,
'text' => 1,
'headings' => array(
	'h1' => 2,
	'h2' => 1.5,
	'h3' => 1,
	'h4' => 0.75,
	'h5' => 0.5,
	'h6' => 0.25
	)
));
define('FREQUENCY_IN_TEXT_NEEDED',4);

function innerHTML($element) {
    $doc = $element->ownerDocument;
    $html = '';
    foreach ($element->childNodes as $node) {
        $html .= $doc->saveHTML($node);
    }
    return $html;
}
function basepath($url) {
	$pu = parse_url($url);
	return $pu['scheme'].'://'.$pu['host'].preg_replace('-/.+\..+-','',$pu['path']);
}
$keywords = json_decode(file_get_contents('keywords.json',true),true);
function crawl($site,$blacklist = array(),$recursive_mode = RECURSIVE_MODE) {
	global $keywords;
	if(!in_array($site,$blacklist)) {
		echo 'Crawling ',$site,'<br>';
		
		$pageKeywords = (isset($keywords[$site][2]) ? $keywords[$site][2] : array());
		$page = file_get_contents($site);
		$document = new DOMDocument();
		$document->loadHTML($page);
		$arr = array();
		
		// Get the title
		$arr[0] = innerHTML($document->getElementsByTagName('title')[0]);
		$words = explode(' ',$arr[0]);
		foreach($words AS $word) {
			if(!isset($pageKeywords[strtolower($word)])) $pageKeywords[strtolower($word)] = 0;
			$pageKeywords[strtolower($word)] += POINTS['title'];
		}
		
		// Get the keywords and the description
		$metaTags = $document->getElementsByTagName('meta');
		foreach($metaTags AS $meta) {
			if($meta->getAttribute('name') == 'keywords') {
				$k = explode(',',$meta->getAttribute('content'));
				foreach($k AS $kw) {
					if(!isset($pageKeywords[strtolower($kw)])) $pageKeywords[strtolower($kw)] = 0;
					$pageKeywords[strtolower($kw)] += POINTS['meta_keywords'];
				}
			}
			else if($meta->getAttribute('name') == 'description') {
				$arr[1] = $meta->getAttribute('content');
				$words = explode(' ',innerHTML($meta));
				foreach($words AS $word) {
					if(!isset($pageKeywords[strtolower($word)])) $pageKeywords[strtolower($word)] = 0;
					$pageKeywords[strtolower($word)] += POINTS['meta_description'];
				}
			}
		}
		
		// Get keywords from titles
		for ($i = 1; $i <= 6; $i++) {
			$ttype = 'h'.$i;
			$titles = $document->getElementsByTagName($ttype);
			foreach($titles AS $title) {
				$words = explode(' ',innerHTML($title));
				foreach($words AS $word) {
					if(!isset($pageKeywords[strtolower($word)])) $pageKeywords[strtolower($word)] = 0;
					$pageKeywords[strtolower($word)] += POINTS['headings'][$ttype];
				}
			}
		}
		
		// Get important words from text
		// Get text only by removing tags from source
		$text = strip_tags(str_replace('>','> ',$page));
		$text_words = array_count_values(explode(' ',$text));
		foreach($text_words AS $word=>$frequency) {
			if(!isset($pageKeywords[strtolower($word)])) $pageKeywords[strtolower($word)] = 0;
			if($frequency >= FREQUENCY_IN_TEXT_NEEDED) $pageKeywords[strtolower($word)] += POINTS['text'];
		}
		
		// Crawl other sites
		$a = $document->getElementsByTagName('a');
		foreach($a AS $link) {
			$url = $link->getAttribute('href');
			$blacklist[] = $site;
			if(strpos($url,'://') === false AND strpos($url,'mailto:') === false AND !preg_match('/[Jj]avascript:/',$url) AND RECURSIVE_MODE AND $recursive_mode AND $link->getAttribute('rel') != 'nofollow') crawl((preg_match('/http[s]?:\/\//',$url) ? $url : basepath($site).(strpos($url,'/') === false ? '/'.$url : $url)),array(),false);
		}
		
		$arr[2] = $pageKeywords;
		$keywords[$site] = $arr;
		
		file_put_contents('keywords.json',json_encode($keywords));
	}
}
crawl(SITE,array());

?>