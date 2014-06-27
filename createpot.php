<?php
$config = array(
    "lang_dir" => "../upload/catalog/language/english",
    "copyright_holder" => "\"Cristian\"",
);
require_once 'commonfuncs.php';

@set_time_limit(9000);
$indir = realpath($config['lang_dir']).DIRECTORY_SEPARATOR;
$outdir = sys_get_temp_dir();
$outdir = rtrim(rtrim($outdir, '/'),'\\').DIRECTORY_SEPARATOR.'i18n'.DIRECTORY_SEPARATOR;
if(!file_exists($outdir)) mkdir($outdir, 0777, true);
$infiles = glob_recursive($indir."*.php");
foreach ($infiles as $infile) {
    $dir = dirname(str_replace($indir,$outdir."f/",$infile)).DIRECTORY_SEPARATOR;
    if (!is_dir($dir)) {
	mkdir($dir, 0777, true);
    }
    $_ = array();
    require $infile;
    if (str_replace($indir,'',$infile) == "english.php") {
	$infile = str_replace("english.php","language.php",$infile);
    }
    $contents = "<?php\n";
    foreach ($_ as $key => $value) {
	$line = "// ".str_replace($indir,'',$infile).":".$key."\n\$_['$key'] = gettext('".str_replace("'","\'",$value)."');\n";
	if (eval($line) === false) {
	    error("Eval error in file\n$infile\nat:\n$line");
	}

	$contents .= $line;
    }
    file_put_contents($dir.DIRECTORY_SEPARATOR.basename($infile),$contents);
}

file_put_contents($outdir."infiles.list", implode("\n", glob_recursive($outdir."f".DIRECTORY_SEPARATOR."*.php")));


$msgmergecmd = "msgmerge --update --verbose";
$localedir = realpath(__DIR__);
$xgettextoptions = array(
    "language" => "PHP",
    "copyright-holder" => $config['copyright_holder'],
    "foreign-user",
    "package-name" => "OpenCart",
    "package-version" => "1.0",
    "no-location",
    //"sort-by-file",
    "from-code" => "UTF-8",
    "files-from" => $outdir."infiles.list",
    "output" => $outdir."messages.pot",
    "add-comments",
    //"extract-all",
    "debug",
);
$xgettextcmd = "xgettext";
foreach ($xgettextoptions as $key => $value) {
    if (is_numeric($key)) {
	$xgettextcmd .= " --".$value;
    }
    else {
	$xgettextcmd .= " --".$key."=".$value;
    }
}
$return = 0;
echo "executing xgettext with:\n".$xgettextcmd."\n";
echo syscall($xgettextcmd, $return);
if ($return) {
    error($return);
}
if (!is_file($outdir."messages.pot")) {
    error("no file created");
}
flush();


if (is_file($localedir."/messages.pot")) {
    echo "merging new pot to existing\n";
    $cmd = $msgmergecmd." ".$localedir."/messages.pot ".$outdir."messages.pot";
    echo "running $cmd \n";
    echo syscall($cmd,$return);
    if ($return) {
	error($return);
    }
    flush();
}
else {
    copy($outdir."messages.pot", $localedir."/messages.pot");
}
echo "cleaning up\n";
delTree($outdir);
echo "Merging template to translated files\n";
foreach (glob_recursive($localedir."/*.po") as $pofile) {
    $cmd = $msgmergecmd." ".$pofile." ".$localedir."/messages.pot";
    echo "######################################\n";
    echo "= ".preg_replace("/.*\/(\w\w_\w\w)\/.*/is","\\1",$pofile)." =\n";
    echo "\trunning $cmd \n";
    echo syscall($cmd,$return);
    if ($return) {
	error($return);
    }
    $cmd = $msgfmtcmd." ".dirname($pofile)."/messages.mo ".$pofile;
    echo "\trunning ".$cmd."\n";
    echo "\t".syscall($cmd,$return);
    if ($return) {
	error($return);
    }
}

echo "\ndone\n";

