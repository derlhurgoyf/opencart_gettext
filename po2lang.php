<?php
$config = array(
    "languages_dir"  => "../upload/catalog/language",
);


require_once 'commonfuncs.php';

if (!is_dir($config['languages_dir'])) {
    error("Languages_dir does not exist:\n".$config['languages_dir']);
}
$langdir = realpath($config['languages_dir']);
$locales = array();
if (($dh = opendir("."))) {
    while (($file = readdir($dh)) !== false) {
	if (is_dir($file) && preg_match("/^(\w+)_(\w+)$/", $file, $match)) {
	    $locales[ucfirst(Locale::getDisplayLanguage($file, $file))] = $file;
	}
    }
    closedir($dh);
}
foreach ($locales as $language => $locale) {
    $infile = $locale.DIRECTORY_SEPARATOR."LC_MESSAGES".DIRECTORY_SEPARATOR."messages.po";
    if (!is_file($infile)) {
	echo "Skipping $locale, no messages.po found\n";
	continue;
    }
    echo "Processing $locale\n";
    $contents = file_get_contents($infile);

    $translations = preg_split("/\n\s*\n(?=#)/is", $contents, -1, PREG_SPLIT_NO_EMPTY);
    unset($translations[0]);
    foreach ($translations as $translation) {
	if (!preg_match("/\nmsgstr \"((?:[^\n]|\"\s*\n\s*\")*)\"/is", $translation, $msgstr)) {
	    error ("no msgstr in $translation");
	}
	$msgstr = preg_replace("/\"\s*\n\s*\"/is", "", $msgstr[1]);
	$msgstr = trim(str_replace('\"','"',$msgstr));
	if ($msgstr == "") {
	    continue;
	}
	if (!preg_match_all("/^#\. ([^:]+):(\S+)/im", $translation, $files)) {
	    error("No files for $translation");
	}
	foreach ($files[1] as $i => $outfile) {
	    $key = $files[2][$i];
	    if ($outfile == "language.php") {
		$outfile = strtolower($locale).".php";
	    }


	    $outfile = $langdir.DIRECTORY_SEPARATOR.$locale.DIRECTORY_SEPARATOR.$outfile;
	    if (!is_dir(dirname($outfile))) {
		mkdir(dirname($outfile), 0777, true);
	    }
	    if (!is_file(dirname($outfile).DIRECTORY_SEPARATOR."index.html")) {
		touch(dirname($outfile).DIRECTORY_SEPARATOR."index.html");
	    }
	    if (is_file($outfile)) {
		$langfile = file_get_contents($outfile);
	    }
	    else {
		$langfile = "<?php\n/*\n * This file has been automatically created by OpenCart_gettext\n * https://github.com/derlhurgoyf/opencart_gettext\n */\n";
	    }
	    $regex = "/\$_\[[\"\']".preg_quote($key, "\/")."[\"\']\]\s*=\s*(?:[^\"]|\\\")+\";/is";
	    $langstring = "\$_['".$key."']".str_repeat(" ",max(0,25-strlen($key)))." = \"".  str_replace('"', '\\"', $msgstr)."\";";
	    if (preg_match(str_replace("  ","\\s+",$regex), $langstring)) {
		$langfile = preg_replace($regex,$langstring,$langfile);
	    }
	    else {
		$langfile .= "\n".$langstring;
	    }
	    file_put_contents($outfile, $langfile);
	}
    }

}