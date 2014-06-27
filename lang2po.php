<?php
$config = array(
    "language_dir"  => "../upload/catalog/language/spanish",
    "language_file" => "spanish.php",
    "locale"	    => "es_ES",
);


require_once 'commonfuncs.php';

if (!is_file("messages.pot")) {
    error("messages.pot does not exist.\nPlease create it first using createpot.php");
}
if (!is_dir($config['language_dir'])) {
    error("Language_dir does not exist:\n".$config['language_dir']);
}
$indir = realpath($config['language_dir']).DIRECTORY_SEPARATOR;
$langfile = realpath($config['language_dir'].DIRECTORY_SEPARATOR.$config['language_file']);
$outfile = $config['locale']."/LC_MESSAGES/messages.po";
if (!is_dir(dirname($outfile))) {
    mkdir(dirname($outfile),'0777',true);
}
$outdir = sys_get_temp_dir();
$outdir = rtrim(rtrim($outdir, '/'),'\\').DIRECTORY_SEPARATOR.'i18n'.DIRECTORY_SEPARATOR;
if(!file_exists($outdir)) {
    mkdir($outdir, 0777, true);
}


$contents = file_get_contents("messages.pot");

$infiles = glob_recursive($indir."*.php");
foreach ($infiles as $infile) {
    $_ = array();
    require $infile;
    if ($infile == $langfile) {
	$infile = str_replace(basename($infile), "language.php", $infile);
    }
    foreach ($_ as $key => $value) {
	if (!mb_detect_encoding($value, "UTF-8", true)) {
	    $value = utf8_encode($value);
	}
	$value = html_entity_decode($value, ENT_COMPAT|ENT_QUOTES ,"UTF-8");
	$regex = "/(\n#\. ".preg_quote(str_replace($indir,'',$infile).":".$key, "//")."\s*\n(?:#[:\.] [^\n]*\n)*msgid \"[^\n]*\n(?:\"[^\n]*\n)*)msgstr \"\"/is";
	$contents = preg_replace($regex,"\\1msgstr \"".str_replace('"', '\"', $value)."\"",$contents);
    }
}
/*
 * Merging is too risky
if (is_file($outfile)) {
    $pofile = $outdir.$config['locale'].".po";
    //remove untranslated strings
    $contents = preg_replace("/\n\s*\n(#[^\n]*\n)*msgid (\"[^\n]*\n)+msgstr \"\"/is","",$contents);
    file_put_contents($pofile, $contents);
    $msgmergecmd = "msgmerge --silent --verbose --previous --output-file=merged.po";
    $cmd = $msgmergecmd." ".$pofile." ".$outfile;
    echo "\nrunning $cmd \n";
    echo syscall($cmd,$return);
    if ($return) {
	error($return);
    }
    exit;
    copy($pofile, $outfile);
    delTree($outdir);
}
else
 */
{
    file_put_contents($outfile, $contents);
}
echo "done\n";
