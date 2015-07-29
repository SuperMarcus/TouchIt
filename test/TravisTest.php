<?php
define("BINARY_PATH", PHP_BINARY);
define("PLUGIN_DIR", "plugins");

function build($bin, $phar, $pluginDir){
    echo "[Build] Building plugin with '$phar'\n";

    $server = proc_open("$bin $phar --no-wizard --disable-readline --plugins $pluginDir", [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ], $pipes);

    fwrite($pipes[0], "version\nmakeplugin TouchIt\nstop\n\n");

    $error = 0;

    while(!feof($pipes[1])){
        $line = fgets($pipes[1]);
        if(strpos($line, "[CRITICAL]") or strpos($line, "[EMERGENCY]") or strpos($line, "[FATAL]")){
            echo "[Test] Server output an error message\n";
            echo "[Server] ".$line;
            ++$error;
        }
    }

    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    echo "\n\n[Build] Return value: ". proc_close($server) ."\n";

    return $error;
}

echo "[Build] Travis CI Build started\n";

$test = 0;
$failed = 0;
$buildCount = 0;

foreach(scandir("server") as $serverBuild){
    if(strpos($serverBuild, ".phar")){
        ++$test;
        $error = build(BINARY_PATH, "server/".$serverBuild, PLUGIN_DIR);
        $build = glob(PLUGIN_DIR."/DevTools/TouchIt*.phar");
        if(count($build) <= 0){
            echo "[Build] No phar created!\n";
            ++$error;
        }else{
            foreach($build as $b){
                if(strpos($b, ".phar")){
                    echo "[Build] Found Phar: $b\n";
                    file_put_contents("build/TouchIt_Build_".(++$buildCount).".phar", file_get_contents($b));
                    unlink($b);
                }
            }
        }

        if($error > 0){
            echo "[Build] There are $error errors during this build\n";
            ++$failed;
        }
    }
}