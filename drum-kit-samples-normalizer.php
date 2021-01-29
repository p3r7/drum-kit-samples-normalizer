<?php

// https://stackoverflow.com/questions/336605/how-can-i-find-the-largest-common-substring-between-two-strings-in-php


// -------------------------------------------------------------------------
// INPUT PARAMS

$optsDef = [
	'dir:',
	];

$options = getopt('', $optsDef);

$dir = @$options['dir'];
if (! isset($dir)) {
	$dir = getcwd();
}


// -------------------------------------------------------------------------
// KIT ELEMENTS DEFINITION

// http://2hmivxeex38xwini1gzpzavh.wpengine.netdna-cdn.com/wp-content/uploads/2016/02/roland808-03_big_carmengalan-1208x718.jpg

$kitElementsAliases = [
	// drums
	'bass_drum' => [
		'kick',
		'kick_drum',
		'bd', // 808 notation
		],
	'snare_drum' => [
		'snare',
		'snr',
		'sd', // 808 notation
		],
	'snare_rimshot' => [
		'rim',
		],
	'tom_tom_low' => [
		'low_tom',
		'lt', // 808 notation
		],
	'tom_tom_medium' => [
		'mid_tom',
		'middle_tom',
		'medium_tom',
		'mt', // 808 notation
		],
	'tom_tom_high' => [
		'hi_tom',
		'high_tom',
		'ht', // 808 notation
		],
	'tom_tom' => [
		'tom'
		],
	'conga_low' => [
		'lc', // 808 notation
		],
	'conga_medium' => [
		'mc', // 808 notation
		],
	'conga_high' => [
		//'hc', // 808 notation, conflicting w/ claves_high
		],
	'conga' => [],
	'brush' => [],

	// idiophones: cymbals
	'cymbal' => [
		'cym',
		'cy',
		'cymbal',
		],
	'ride_cymbal' => [
		'ride'
		],
	'high_hat' => [
		'hh',
		],
	'high_hat_closed' => [
		'hhc',
		'ch', // 808 notation
		],
	'high_hat_open' => [
		'hho',
		'oh', // 808 notation
		],
	'crash_cymbal' => [
		],
	'accent_cymbal' => [
		'accent'
		],

	// idiophones: others
	'cowbel' => [
		'cow',
		'cb', // 808 notation
		],
	'woodblock' => [
		'wood',
		'woodenblock',
		'woodeblock',
		],
	'temple_woodblock' => [
		'temple',
		'templeblock',
		],
	'hand_claves' => [
		'clave',
		'claves',
		'hc' // 808 notation
		],
	'triangle' => [
		],
	'gong' => [
		],

	// others
	'clap' => [
		'cp' // 808 notation
		],
	'maracas' => [
		'ma' // 808 notation
		],

	];


$kitElementsChosenName = [
	'bass_drum' => 'kick',
	'snare_drum' => 'snare',
	'snare_rimshot' => 'rim',
	'tom_tom' => 'tom',
	'tom_tom_low' => 'tomlow',
	'tom_tom_medium' => 'tommid',
	'tom_tom_high' => 'tomhi',
	'conga' => 'conga',
	'conga_low' => 'congalow',
	'conga_medium' => 'congamid',
	'conga_high' => 'congahi',
	'brush' => 'brush',

	// idiophones: cymbals
	'cymbal' => 'cymbal',
	'high_hat' => 'hh',
	'high_hat_closed' => 'hhclosed',
	'high_hat_open' => 'hhopen',
	'ride_cymbal' => 'ride',
	'crash_cymbal' => 'crash',
	'accent_cymbal' => 'accent',

	// idiophones: others
	'cowbel' => 'cow',
	'woodblock' => 'wood',
	'temple_woodblock' => 'temple',
	'hand_claves' => 'clave',
	'triangle' => 'tri',
	'gong' => 'gong',

	// others
	'clap' => 'clap',
	'maracas' => 'maracas',
	];


// -------------------------------------------------------------------------
// GET LIST OF FILES

$files = array_diff(scandir($dir), ['.', '..']);

$wavFiles = preg_grep("/^.*\.wav$/", $files);

if (empty($wavFiles)) {
	echo "no wav files in selected folder $dir\n";
	exit(1);
}


// -------------------------------------------------------------------------
// DETERMINE COMMON *FIX

$commonStarFix = longest_common_substring($wavFiles);


// -------------------------------------------------------------------------
// DETERMINE NORMALIZED FILE NAMES

$unhandledDrumKitFiles = [];
$filenameConvertions = [];

foreach ($wavFiles as $inFilename) {

	// to lower string
	$inFilename = strtolower($inFilename);

	// remove *fix
	$outFilename = str_replace($commonStarFix, '', $inFilename);

	// normalize drum kit name
	$drumKitType = null;
	$drumKitAlias = null;
	foreach ($kitElementsAliases as $kitType => $aliases) {
		if (isset($drumKitType))
			break;
		$aliases []= $kitType;
		foreach ($aliases as $alias) {
			if (strpos($outFilename, $alias) !== false) {
				$drumKitAlias = $alias;
				$drumKitType = $kitType;
				break;
			}
		}
	}

	if (isset($drumKitType)) {
		$outFilename = str_replace($alias, $kitElementsChosenName[$drumKitType], $outFilename);
		$filenameConvertions[$inFilename] = $outFilename;
	} else {
		$unhandledDrumKitFiles []= $inFilename;
	}


	// remove remaining spaces, - and _'s
}


// -------------------------------------------------------------------------
// RENAME FILES

//*
echo "unhandled files:\n";
foreach ($unhandledDrumKitFiles as $f)
	echo " - $f\n";

echo "\n";

foreach ($filenameConvertions as $in => $out)
	echo "$in => $out\n";
//*/

// =========================================================================

// stolen from https://gist.github.com/chrisbloom7/1021218
function longest_common_substring ($words) {
	$words = array_map('strtolower', array_map('trim', $words));
	$sort_by_strlen = create_function('$a, $b', 'if (strlen($a) == strlen($b)) { return strcmp($a, $b); } return (strlen($a) < strlen($b)) ? -1 : 1;');
	usort($words, $sort_by_strlen);
	// We have to assume that each string has something in common with the first
	// string (post sort), we just need to figure out what the longest common
	// string is. If any string DOES NOT have something in common with the first
	// string, return false.
	$longest_common_substring = array();
	$shortest_string = str_split(array_shift($words));
	while (sizeof($shortest_string)) {
		array_unshift($longest_common_substring, '');
		foreach ($shortest_string as $ci => $char) {
			foreach ($words as $wi => $word) {
				if (!strstr($word, $longest_common_substring[0] . $char)) {
					// No match
					break 2;
				} // if
			} // foreach
			// we found the current char in each word, so add it to the first longest_common_substring element,
			// then start checking again using the next char as well
			$longest_common_substring[0].= $char;
		} // foreach
		// We've finished looping through the entire shortest_string.
		// Remove the first char and start all over. Do this until there are no more
		// chars to search on.
		array_shift($shortest_string);
	}
	// If we made it here then we've run through everything
	usort($longest_common_substring, $sort_by_strlen);
	return array_pop($longest_common_substring);
}