<?php
/*
__PocketMine Plugin__
name=TouchItAutoInstaller
description=TouchIt Auto Installer
version=1.0
apiversion=12,13
author=Marcus
class=TouchItAutoInstaller
*/

class TouchItAutoInstaller implements Plugin{
    private $api;
    public function __construct(ServerAPI $api, $server = false){
        $this->api = $api;
    }
    public function __destruct(){}
    
    public function init(){
        if(!Utils::isOnline())return;//check network
        if(file_exists(DATA_PATH."plugins/.touchit") and file_exists(DATA_PATH."plugins/touchit.php")){
            $local = trim(file_get_contents(DATA_PATH."plugins/.touchit"));
            $online = json_decode(Utils::curl_get("https://api.github.com/repos/SuperMarcus/TouchIt/commits"), true);
            if(!$online){
                console("[TouchIt] Github API error.", true, true, 0);
                return;
            }
            if($local != $online[0]["commit"]["committer"]["date"]){
                console("[TouchIt] A new version of TouchIt has been found.");
                file_put_contents(DATA_PATH."plugins/.touchit", $online[0]["commit"]["committer"]["date"]);
                file_put_contents(DATA_PATH."plugins/touchit.php", Utils::curl_get("https://github.com/SuperMarcus/TouchIt/raw/master/TouchIt.php"));
                console("[TouchIt] Done for updating TouchIt. Restarting server...");
                $this->api->console->defaultCommands("stop", array(), "console", "stop");
            }
            return;
        }
        console("[TouchIt] Downloading TouchIt...");
        $online = json_decode(Utils::curl_get("https://api.github.com/repos/SuperMarcus/TouchIt/commits"), true);
        if(!$online){
            console("[TouchIt] Github API error.", true, true, 0);
        }
        file_put_contents(DATA_PATH."plugins/.touchit", $online[0]["commit"]["committer"]["date"]);
        file_put_contents(DATA_PATH."plugins/touchit.php", Utils::curl_get("https://github.com/SuperMarcus/TouchIt/raw/master/TouchIt.php"));
        console("[TouchIt] Done for download TouchIt. Restarting server...");
        $this->api->console->defaultCommands("stop", array(), "console", "stop");
    }
}
?>
