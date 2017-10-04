<?php

$file = '/etc/asterisk/sip.conf';
$files = '/etc/asterisk/dynamic_sip'; 
$toDel = '#include "dynamic';

$content = file_get_contents($file);
$content = explode(PHP_EOL, $content);
$content = array_filter($content, function ($elem) use ($toDel) {
	 return strpos($elem, $toDel) === false;
});

file_put_contents($file, implode(PHP_EOL, $content));

echo 'Fichier sip.conf cleaned' . PHP_EOL;

shell_exec("rm -rf $files");

echo 'Dossier dyn_sip deleted' . PHP_EOL;