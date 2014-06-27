<?php
function syscall($command, &$return=0){
    if ($proc = popen("($command)2>&1","r")) {
	$result = '';
        while (!feof($proc)) $result .= fgets($proc, 1000);
        $return = pclose($proc);
	$result = trim($result);
        return $result.($result ? "\n" : '');
    }
}
function glob_recursive($pattern, $flags = 0) {
    $files = glob($pattern, $flags);

    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
	$files = array_merge($files, glob_recursive($dir . '/' . basename($pattern), $flags));
    }

    return $files;
}
function error($message) {
    echo "\033[0;31m"."ERROR"."\033[0m"."\n".$message."\n";
    exit(1);
}
function delTree($dir) {
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
	(is_dir("$dir/$file") && !is_link($dir)) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}
mb_internal_encoding("UTF-8");