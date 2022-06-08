<?php
header('Content-Type: application/json');
$search_query = $_POST['query'];
$word = strtolower(trim(substr($search_query,strripos($search_query,' '))));
$remaining = substr($search_query,0,strripos($search_query,' '));

ini_set('memory_limit','1000M');
ini_set('max_execution_time', 120);
$wordlist = explode(',',file_get_contents('german3.txt'));
// Using for instead of foreach to increase speed
$word_count = (int)count($wordlist);
$wordlen = strlen($word);
$similarity = array();
for($index = 0; $index < $word_count; ++$index) {
	if(substr(strtolower($wordlist[$index]),0,$wordlen) == $word) $similarity[] = $remaining.' '.$wordlist[$index];
}
asort($similarity);
$recommended = array_slice($similarity,0,10);

echo json_encode($recommended);
?>