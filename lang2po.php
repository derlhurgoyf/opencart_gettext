<?php
$config = array(
    "language_dir"  => "../upload/catalog/language/de_DE",
    "language_file" => "de_DE.php",
    "locale"	    => "de_DE",
);


require_once 'commonfuncs.php';

if (!is_file("messages.pot")) {
    error("messages.pot does not exist.\nPlease create it first using createpot.php");
}
if (!is_dir($config['language_dir'])) {
    error("Language_dir does not exist:\n".$config['language_dir']);
}
$indir = realpath($config['language_dir']).DIRECTORY_SEPARATOR;
if (!is_file($config['language_dir'].DIRECTORY_SEPARATOR.$config['language_file'])) {
    error("Lang-file does not exist:\n".$config['language_dir'].DIRECTORY_SEPARATOR.$config['language_file']);
}
$langfile = realpath($config['language_dir'].DIRECTORY_SEPARATOR.$config['language_file']);
$outfile = $config['locale']."/LC_MESSAGES/messages.po";

$contents = file_get_contents("messages.pot");
$outdir = sys_get_temp_dir();
$outdir = rtrim(rtrim($outdir, '/'),'\\').DIRECTORY_SEPARATOR.'i18n'.DIRECTORY_SEPARATOR;

$infiles = glob_recursive($indir."*.php");
foreach ($infiles as $infile) {
    $dir = dirname(str_replace($indir,$outdir."f/",$infile)).DIRECTORY_SEPARATOR;
    $tmpfile = $dir.basename($infile);
    $_ = array();
    require $infile;
    if ($infile == $langfile) {
	$infile = str_replace(basename($infile), "language.php", $infile);
    }
    foreach ($_ as $key => $value) {
	$regex = "/(\n#\. ".preg_quote(str_replace($indir,'',$infile).":".$key, "//")."\s*\n(?:#[:\.] [^\n]*\n)*msgid \"[^\n]*\n(?:\"[^\n]*\n)*)msgstr \"\"/is";
	$contents = preg_replace($regex,"\\1msgstr \"".str_replace('"', '\"', $value)."\"",$contents);
    }
}
file_put_contents($outfile, $contents);
