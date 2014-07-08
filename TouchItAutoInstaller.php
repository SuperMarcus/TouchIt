<?php
/*
__PocketMine Plugin__
name=TouchItAutoInstaller
description=TouchIt Auto Installer
version=1.1
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
        if(file_exists($this->api->plugin->pluginsPath().".touchit") and file_exists($this->api->plugin->pluginsPath()."touchit.php")){
            $local = @json_decode(@gzdecode(file_get_contents($this->api->plugin->pluginsPath().".touchit")), true);
            if(is_array($local) and $local["hash"] === hash_file("md5", $this->api->plugin->pluginsPath()."touchit.php")){
                if(!Utils::isOnline())return;
                $online = json_decode(Utils::curl_get("https://api.github.com/repos/SuperMarcus/TouchIt/commits"), true);
                if(!$online){
                    console("[TouchIt] Github API error.", true, true, 0);
                    return;
                }
                if($local["date"] != $online[0]["commit"]["committer"]["date"]){
                    console("[TouchIt] A new version of TouchIt has been found.");
                    file_put_contents($this->api->plugin->pluginsPath().".touchit", gzencode(json_encode(array("date" => $online[0]["commit"]["committer"]["date"], "hash" => hash_file("md5", $this->api->plugin->pluginsPath()."touchit.php")))));
                    file_put_contents($this->api->plugin->pluginsPath()."touchit.php", Utils::curl_get("https://github.com/SuperMarcus/TouchIt/raw/master/TouchIt.php"));
                    console("[TouchIt] Done for updating TouchIt. Restarting server...");
                    $this->api->console->defaultCommands("stop", array(), "console", "stop");
                }
                return;
            }
        }
        @unlink($this->api->plugin->pluginsPath().".touchit");
        if(!Utils::isOnline()){//check network
            console("[TouchIt]".FORMAT_RED." Installer needs network.");
            return;
        }
        @unlink($this->api->plugin->pluginsPath()."touchit.php");
        console("[TouchIt] Downloading TouchIt...");
        
        $online = json_decode(Utils::curl_get("https://api.github.com/repos/SuperMarcus/TouchIt/commits"), true);
        $file = Utils::curl_get("https://github.com/SuperMarcus/TouchIt/raw/master/TouchIt.php");
        
        if(!$online or !$file){
            console("[TouchIt]".FORMAT_RED." Github API error!");
            return;
        }
        
        file_put_contents($this->api->plugin->pluginsPath()."touchit.php", $file);
        file_put_contents($this->api->plugin->pluginsPath().".touchit", gzencode(json_encode(array("date" => $online[0]["commit"]["committer"]["date"], "hash" => hash_file("md5", $this->api->plugin->pluginsPath()."touchit.php")))));
        console("[TouchIt] Loading...");
        if(!$this->api->plugin->load($this->api->plugin->pluginsPath()."touchit.php")){
            console("[TouchIt] Restarting server...");
            $this->api->console->defaultCommands("stop", array(), "console", "stop");
        }else{
            $plugins = $this->api->plugin->getAll();
            foreach($plugins as $plugin){
                if(strtolower($plugin[1]["name"]) === "touchit"){
                    $plugin[0]->init();
                    console("[TouchIt] TouchIt has been loaded.");
                    return;
                }
            }
            console("[TouchIt] Faild to load TouchIt. Restarting server.");
            $this->api->console->defaultCommands("stop", array(), "console", "stop");
        }
    }
}
?>
