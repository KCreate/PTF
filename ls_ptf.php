<?php
/*
	Return the correct html closing tag
*/
function closingTag($tag) {
	$length = strlen($tag); //Lenght of the tag
	$substring = substr($tag, 1, $length-1); //<h1> becomes h1>

	//Make it a closing tag
	return "</".$substring;
}

/*
	Get the contents of two given tags inside a string
*/
function contentsOfTag($tag, $value) {
	$a = true;
	$firstRun = true;
	$parsedString = "";
	$contentsArray = [];

	while ($a) {
		//Used to parse multiple tags per text
		if ($firstRun) {
			$string = " ".$value;
			$firstRun = false;
		} else {
			$string = " ".$parsedString;
		}

		//Search for the string
		$startpos = strpos($string, $tag);

		//If the startpos is zero, the tag doesn't exist anywhere in the text
		//I've added an additional whitespace at the beginning of the text to make sure we catch tags that are at the beginning of the document
		if ($startpos != 0) {
			//Move startpos to the beginning of the content of the tag
			$startpos += strlen($tag);

			//Optional closing tag
			$closingTag = [false, $closingTag = closingTag($tag)];

			//Searches for the next tag, and returns the distance between it and startpos
			if (strpos($string, $tag, $startpos) - $startpos > 0) {
				$length = strpos($string, $tag, $startpos) - $startpos;
			} elseif (strpos($string, $closingTag[1], $startpos) - $startpos > 0) {
				//Closing tag found
				$length = strpos($string, $closingTag[1], $startpos) - $startpos;
				$closingTag[0] = true;
			}

			//Get's the content of the tag
			$tagcontent = substr($string, $startpos, $length);

			//Trims the content, to remove unwanted whitespaces
			$tagcontentTrimmed = trim($tagcontent);

			//Add the tag content to the array
			array_push($contentsArray, $tagcontentTrimmed);

			//Remove the tag and the content to search for more tags of the same type
			if ($closingTag[0]) {
				$deformatedTagString = $tag.$tagcontent.$closingTag[1];
			} else {
				$deformatedTagString = $tag.$tagcontent.$tag;
			}

			$parsedString = str_replace($deformatedTagString, "___", $string);
		} else {
			$a = false;
		}
	}

	//Return NULL if the contentsarray is empty.
	if (count($contentsArray) != 0) {
		return $contentsArray;
	} else {
		return false;
	}
}

/*
	Parses the given string with all the given tagging rules
	If $data is an array, every child will be parsed
*/
function parse($data) {
	if (is_array($data)) {
		$array = [];
		foreach ($data as $key => $value) {
			if (str_contains('#hl#', $value)) {
				$value = parseTag('#hl#', $value, '<span class="highlight">', '</span>');
			}
			if (str_contains('#qo#', $value)) {
				$value = parseTag('#qo#', $value, '<blockquote>', '</blockquote>');
			}
			if (str_contains('#a#', $value)) {
				$value = parseTag('#a#', $value);
			}
			if (str_contains('#img#', $value)) {
				$value = parseTag('#img#', $value);
			}
			if (str_contains('#h#', $value)) {
				$value = parseTag('#h#', $value, '<h1>', '</h1>');
			}

			//Replace tags
			$value = str_replace('#nl#', '<br/>', $value);
			$value = str_replace('#np#', '<br/><br/>', $value);
			$array[$key] = $value;
		}
		return $array;
	} else {
		//Call to self with the text as an array
		return parse(["data"=>$data], $source);
	}
}

/*
	If a prefix or suffix is given, return the parsed string based on that
	If the tag has its own procedure (like the #img# tag) use that
*/
function parseTag($tag, $string, $prefix = 'undefined', $suffix = 'undefined') {
	//Check if string contains tag at least one time
	if (str_contains($tag, $string)) {
		//Get the contents of the tag
		$tagContents = contentsOfTag($tag, $string);

		//Check if contentsOfTag returned an Error
		if (isset($tagContents['Error'])) {
			return false;
		}

		//Replace
		foreach($tagContents as $tagcontent) {
			$search = $tag.$tagcontent.$tag;

			//Diferent behaviour for image and link tags
			if ($tag == "#img#") {
				//Check if the file exists
				$replace = '<img src="'.trim($tagcontent).'"/>';
			} else if ($tag == "#a#") {
				//Explode and trim the tagcontent
				$tagelements = [];
				foreach(explode(' - ', $tagcontent) as $tagelement) {
					array_push($tagelements, trim($tagelement));
				}

				//Different behaviour if only one element is given
				$elementcount = count($tagelements);
				if ($elementcount > 1) {
					$replace = '<a href="'.$tagelements[0].'">'.$tagelements[1].'</a>';
				} else {
					$replace = '<a href="'.$tagelements[0].'">Link</a>';
				}
			} else {
				//Default behaviour
				if ($prefix === 'undefined') {
					$replace = trim($tagcontent).$suffix;
				} elseif ($suffix === 'undefined') {
					$replace = $prefix.trim($tagcontent);
				} elseif ($prefix === 'undefined' && $suffix === 'undefined') {
					$replace = trim($tagcontent);
				} else {
					$replace = $prefix.trim($tagcontent).$suffix;
				}
			}

			//Replace
			$string = str_replace($search, $replace, $string);
		}
	}

	//Return
	return $string;
}

/*
	Returns true if $text contains the string $search
*/
function str_contains($search, $text) {
	//Return false if $text doesn't contain $search
	$strpos = strpos($text, $search);
	return ($strpos !== false ? true : false);
}
?>
