<!DOCTYPE html>
<html>
<head>
	<title>Search results for <?php echo $_GET['query']; ?></title>
	<meta charset="utf-8"></meta>
	<link rel="stylesheet" href="search.css">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<form action="?" method="get" class="top">
	<img src="csearch.svg">
	<input type="text" name="query" list="query_list" autocomplete="off" id="query_input" value="<?php echo $_GET['query']; ?>">
	<input type="submit" value="">
</form>
<p>Search results for <?php echo $_GET['query']; ?></p>
<?php
// Typically more memory and more time is required than for normal php scripts
ini_set('memory_limit','1000M');
ini_set('max_execution_time', 120);

// Settings for Search Alg
define('MAX_RESULTS',25);
define('PERFECT_MATCH',2);
define('STANDARD_DEDUCTION_NPM',0.2);
define('DESCRIPTION_LENGTH',200);
define('TITLE_LENGTH',60);

$index;
$i;
$sindex;

// Word correction
$search_query = trim($_GET['query']);
if($search_query != '') {
	$words = explode(' ',strtolower($search_query));
	$wordlist = explode(',',file_get_contents('german3.txt'));
	$lower = array_map('strtolower',$wordlist);
	// Using for instead of foreach to increase speed/efficiency
	$count = (int)count($words);
	$word_count = (int)count($wordlist);
	for($i = 0; $i < $count; ++$i) {
		if(!array_search(strtolower($words[$i]),$lower)) {
			$needs_correction = true;
			for($index = 0; $index < $word_count; ++$index) {
				$similarity[$words[$i]][$wordlist[$index]] = levenshtein($words[$i],$wordlist[$index],1,1,2);
			}
			$recommended[] = array_search(min($similarity[$words[$i]]),$similarity[$words[$i]]);
		} else $recommended[] = $words[$i];
	}
	if($needs_correction) {
		echo '<p>Meintest du: <a href="?query=',implode('+',$recommended),'">',implode(' ',$recommended),'</a></p>';
	}

	// Getting results
	$sres = json_decode(file_get_contents('keywords.json'),true);
	$sres_count = (int)count($sres);
	$sres_keys = array_keys($sres);
	for($i = 0; $i < $count; ++$i) {
		for($index = 0; $index < $sres_count; ++$index) {
			$keywords = $sres[$sres_keys[$index]][2];
			$flipped = array_keys($keywords);
			$sindex[$sres_keys[$index]] = 0;
			if(array_search($words[$i],$flipped) !== false) $sindex[$sres_keys[$index]] += PERFECT_MATCH * $keywords[$words[$i]];
			else {
				$kc = (int)count($flipped);
				for($ki = 0; $ki < $kc; ++$ki) {
					 $sindex[$sres_keys[$index]] += ((strlen($flipped[$ki]) - levenshtein($words[$i],$flipped[$ki])) * $keywords[$flipped[$ki]]) / STANDARD_DEDUCTION_NPM - STANDARD_DEDUCTION_NPM;
				}
			}
		}	
	}
	arsort($sindex);
	$rc = 0;
	$sindex = array_slice($sindex,(isset($_GET['start']) ? $_GET['start'] : 0));
	if(max($sindex) > 0) {
		foreach($sindex AS $site=>$e) {
			if($e > 0) {
				if($rc <= (isset($_GET['start']) ? $_GET['start'] : 0) + MAX_RESULTS) echo '<div class="result"><a href="',$site,'"><div class="title">',substr($sres[$site][0],0,TITLE_LENGTH),'</div><div class="url">',$site,'</div><div class="description">',substr($sres[$site][1],0,DESCRIPTION_LENGTH),'</div></a></div>';
				++$rc;
			}
		}
	}
	else echo '<p style="font-weight:bold">No results found for this search.</p>';
} else echo '<p>No input.</p>';
?>
<script src="suggest.js" async></script>
<div class="pages">
	<?php if($rc > ((isset($_GET['start']) ? $_GET['start'] : 0) + MAX_RESULTS)) { ?>
	<a href="?<?php echo http_build_query($_GET); ?>&start=<?php echo (isset($_GET['start']) ? $_GET['start'] : 0) + MAX_RESULTS; ?>">Next</a>
	<?php } ?>
</div>
<div class="foot">
	<div class="speed"><?php echo $rc; ?> results out of <?php echo $sres_count; ?> indexed sites found in <?php echo microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']; ?>ms</div>
</div>
</body>
</html>