<?php
echo "[Build] Travis CI Build started.\n";
$server = proc_open(PHP_BINARY . " ../PocketMine.phar --no-wizard --disable-readline --plugins ../", [
    0 => ["pipe", "r"],
    1 => ["pipe", "w"],
    2 => ["pipe", "w"]
], $pipes);

fwrite($pipes[0], "version\nmakeplugin TouchIt\nstop\n\n");

$error = 0;

while(!feof($pipes[1])){
    $line = fgets($pipes[1]);
    echo "[Server] ".$line;
    if(strpos($line, "[CRITICAL]") or strpos($line, "[EMERGENCY]") or strpos($line, "[FATAL]")){
        echo "[Test] Server output an error message.\n";
        ++$error;
    }
}

fclose($pipes[0]);
fclose($pipes[1]);
fclose($pipes[2]);

echo "\n\n[Build] Return value: ". proc_close($server) ."\n";
if(count(glob("../DevTools/TouchIt*.phar")) === 0){
    echo "[Build] No Phar created!\n";
    exit(1);
}elseif($error > 0){
    echo "[Build] There are $error errors during the test.\n";
    exit(1);
}else{
    echo "[Build] Build successfully ended.\n";
    exit(0);
}