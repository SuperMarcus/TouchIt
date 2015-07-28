<?php
echo "[Build] Travis CI Build started.\n";
$server = proc_open(PHP_BINARY . " ../PocketMine.phar --no-wizard --disable-readline --plugins ../", [
    0 => ["pipe", "r"],
    1 => ["pipe", "w"],
    2 => ["pipe", "w"]
], $pipes);

fwrite($pipes[0], "version\nmakeplugin TouchIt\nstop\n\n");

while(!feof($pipes[1])){
    echo "[Server] ".fgets($pipes[1]);
}
fclose($pipes[0]);
fclose($pipes[1]);
fclose($pipes[2]);

echo "\n\n[Build] Return value: ". proc_close($server) ."\n";
if(count(glob("../DevTools/TouchIt*.phar")) === 0){
    echo "[Build] No Phar created!\n";
    exit(1);
}else{
    echo "[Build] Build successfully ended.\n";
    exit(0);
}