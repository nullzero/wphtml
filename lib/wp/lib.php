<?php

/*
function api($data, $site="th"){
    $data["format"] = "json";
    $ch = curl_init('http://' . $site . '.wikipedia.org/w/api.php?' . 
                    http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
    return json_decode(curl_exec($ch), True);
}
* */

class Site{
    public $site;
    
    function __construct($site){
        $this->site = $site;
    }
    
    public function link(){
        return "//" . $this->site . ".wikipedia.org/wiki/";
    }
    
    public function api($data){
        $data["format"] = "json";
        $ch = curl_init('http://' . $this->site . '.wikipedia.org/w/api.php?' . 
                        http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
        return json_decode(curl_exec($ch), True);
    }
    
    private function _langlinks1site($pages, $site="en"){
        foreach($pages as $i => $page) $pages[$i] = ucfirst($page);
        $data = array("action" => "query",
                      "prop" => "langlinks",
                      "titles" => join("|", $pages),
                      "redirects" => "",
                      "lllang" => $site,
                      "lllimit" => 500);
        $tmp = $this->api($data);
        if(array_key_exists("query-continue", $tmp)) echo "Error!";
        if(!array_key_exists("query", $tmp)) return array();
        $tmp = $tmp["query"];
        $map = array();
        if(array_key_exists("redirects", $tmp)){
            foreach($tmp["redirects"] as $sth){
                $map[$sth["from"]] = $sth["to"];
            }
        }
        if(array_key_exists("normalized", $tmp)){
            foreach($tmp["normalized"] as $sth){
                if(array_key_exists($sth["to"], $map)){
                    $map[$sth["from"]] = $map[$sth["to"]];
                    //unset($map[$sth["to"]]);
                }else{
                    $map[$sth["from"]] = $sth["to"];
                }
            }
        }
        if(!array_key_exists("pages", $tmp)) return array();
        $tmp = $tmp["pages"];
        $out = array();
        foreach($tmp as $pageid => $datpage){
            if($pageid < 0 or !array_key_exists("langlinks", $datpage)) continue;
            else $out[$datpage["title"]] = $datpage["langlinks"][0]["*"];
        }
        foreach($pages as $item){
            if(array_key_exists($item, $map) and array_key_exists($map[$item], $out)){
                $out[$item] = $out[$map[$item]];
                //unset($out[$map[$item]]);
            }
        }
        return $out;
    }
    
    public function langlinks1site($pages, $site="en"){
        $ans = array();
        foreach(itergroup($pages, 50) as $bunch){
            $ans += $this->_langlinks1site($bunch, $site);
        }
        return $ans;
    }
    
    public function exturlusage($url, $limit){
        $data = array("action" => "query",
                      "list" => "exturlusage",
                      "euquery" => $url,
                      "eulimit" => $limit);
        $tmp = $this->api($data);
        return $tmp["query"]["exturlusage"];
    }
    
    public function getAll($titles, $redirects=True){
        $data = array(
            "action" => "query",
            "prop" => "revisions",
            "rvprop" => "content",
            "titles" => join("|", $titles),
        );
        if($redirects) $data["redirects"] = $redirects;
        $data = $this->api($data);
        $pages = array();
        $data = $data["query"];
        foreach($data["pages"] as $item){
            $page = new Page($this, $item["title"]);
            $item = reset($item["revisions"]);
            $page->txt = $item["*"];
            $pages[$page->title] = $page;
        }
        if(array_key_exists("redirects", $data)){
            foreach($data["redirects"] as $redir){
                $pages[$redir["from"]] = $pages[$redir["to"]];
            }
        }
        if(array_key_exists("normalized", $data)){
            foreach($data["normalized"] as $redir){
                $pages[$redir["from"]] = $pages[$redir["to"]];
            }
        }
        $rets = array();
        foreach($titles as $title) $rets[] = $pages[$title];
        return $rets;
        //return $pages;
    }
}

class Page{
    public $title, $site, $txt, $newtitle;
    
    function __construct($site, $title){
        $this->site = $site;
        $this->title = $title;
    }
    
    public function get($redirects=True, $force=False){
        if(isset($this->txt) and !$force) return $this->txt;
        $pages = $this->site->getAll(array($this->title), $redirects);
        $this->txt = $pages[0]->txt;
        return $this->txt;
    }
    
    function gethist($lim=500, $reverse=False, $content=False){
        $rvprop = array("ids", "timestamp", "user");
        if($content){
            $rvprop[] = "content";
        }
        $data = array("action" => "query",
                      "prop" => "revisions",
                      "rvprop" => join("|", $rvprop),
                      "redirects" => "", 
                      "titles" => $this->title,
                      "rvlimit" => $lim,
                      "rvdir" => $reverse ? "newer" : "older");
        $tmp = $this->site->api($data);
        $tmp = reset($tmp["query"]["pages"]);
        foreach($tmp["revisions"] as &$item){
            $item["timestamp"] = Timestamp::fromISO($item["timestamp"]);
        }
        return $tmp["revisions"];
    }
}

class Revision{
    public $revid, $txt;
    
    function __construct($site, $revid){
        $this->site = $site;
        $this->revid = $revid;
    }
    
    function get($force=False){
        if(isset($this->txt) and !$force) return $this->txt;
        $data = array(
            "action" => "query",
            "prop" => "revisions",
            "rvprop" => "content",
            "revids" => $this->revid,
        );
        $data = $this->site->api($data);
        $data = reset($data["query"]["pages"]);
        $this->newtitle = $data["title"];
        $data = reset($data["revisions"]);
        $this->txt = $data["*"];
        return $this->txt;
    }
}

class Timestamp extends DateTime{
    public static function fromTimestamp($s){
        return casttoclass("Timestamp", self::createFromFormat("YmdHis", $s));
    }

    public static function fromISO($s){
        return casttoclass("Timestamp", self::createFromFormat("Y-m-d H:i:s ", 
                           preg_replace("@[TZ]@", " ", $s)));
    }

    public static function getcurrenttime(){
        $matches = False;
        $tmp = new Site("th");
        $tmp = $tmp->api(array("action" => "parse", "text" => "{{CURRENTTIMESTAMP}}"));
        preg_match("@\d+@", $tmp['parse']['text']['*'], $matches);
        return self::fromTimestamp($matches[0]);
    }
    
    public function __toString(){
        return $this->format('Y-m-d H:i:s');
    }
}

function get($title, $revid=Null, $site="th"){
    $data = array("action" => "query",
                  "prop" => "revisions",
                  "rvprop" => "content",
                  "redirects" => "");
    if($revid) $data["revids"] = $revid;
    else $data["titles"] = $title;
    $data = api($data, $site);
    $data = reset($data["query"]["pages"]);
    global $ntitle;
    $ntitle = $data["title"];
    $data = reset($data["revisions"]);
    return $data["*"];
}


function getnamespaces(){
    $data = array("action" => "query",
                  "meta" => "siteinfo",
                  "siprop" => "namespaces|namespacealiases|magicwords");
    $tmp = api($data);
    $tmp = $tmp["query"];
    $dat = array();
    foreach($tmp["namespaces"] as $item){
        $dat[$item["id"]] = array("*" => $item["*"], "#" => array());
        if(array_key_exists("canonical", $item)){
            $dat[$item["id"]]["#"][] = $item["canonical"];
        }
    }
    foreach($tmp["namespacealiases"] as $item){
        $dat[$item["id"]]["#"][] = $item["*"];
    }
    echo "<pre>";
    print_r($dat);
    echo "</pre>";
}
?>
