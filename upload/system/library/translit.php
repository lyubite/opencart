<?php

function transliterate($textcyr = null, $textlat = null) {
	$cyr = array(
	'а', 'б', 'в', 'г', 'д', 'е', 'ё',  'ж',  'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х',  'ц',  'ч',  'ш',  'щ',    'ы', 'ъ', 'ь', 'э', 'ю',  'я',
	'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё',  'Ж',  'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х',  'Ц',  'Ч',  'Ш',  'Щ',    'Ы', 'Ъ', 'Ь', 'Э', 'Ю',  'Я',
	' ', '&amp;');
	$lat = array(
	'a', 'b', 'v', 'g', 'd', 'e', 'yo', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'kh', 'ts', 'ch', 'sh', 'shch', 'i', '',  '',  'e', 'yu', 'ya',
	'A', 'B', 'V', 'G', 'D', 'E', 'Zh', 'Yo', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'Kh', 'Ts', 'Ch', 'Sh', 'Shch', 'I', '',  '',  'E', 'Yu', 'Ya',
	'_', 'and');
	if($textcyr) return str_replace($cyr, $lat, $textcyr);
	else if($textlat) return str_replace($lat, $cyr, $textlat);
	else return null;
}

function seo_keyword($text) {
	return strtolower( transliterate($text, null) );
}
?>
