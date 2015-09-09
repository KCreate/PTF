## PTF
****
Stands for PHP-Tag-Formatting
Easily implementable
Fast (i guess?)
See an example here: http://leonardschuetz.ch/projects/PTF/source.php

## Installation
Installation is actually pretty ease. Just download the ls_ptf.php file and include it into your project.

## Usage
To format all the tags inside a string just write:
```
$a = "Click this #a#http://awesomesite.com - link#a#";
$b = parse($a);
// $b will be "Click this <a href="http://awesomesite.com">link</a>"
```

## Customization
Change the definitions in the parse() function to choose which, and how the tags are being outputted.
For example if you want #github#PTF#github# to result in this: <span class="github">PTF</span>
You'd write:
```
if (str_contains('#github#', $value)) {
	$value = parseTag('#github#', $value, '<span class="github">', '</span>');
}
```
Parameters:
1. Tag
2. String
3. What to replace the opening tag with
4. What to replace the closing tag with
