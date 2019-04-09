<?php

class Command extends CConsoleCommand {
    public $args = array();

    public function main() {
        return true;
    }

    public function run($args) {
        ini_set("memory_limit", "10G");
        foreach($args as $arg) {
            $arg = trim($arg);
            if($arg === "" || substr($arg,0,2) !== "--") {
                echo "ignore invalid argument: $arg\n";
                continue;
            }

            $arg = substr($arg, 2);
            $pos = strpos($arg, "=");
            if($pos === false)  continue;
            $key = trim(substr($arg, 0, $pos));
            $value = trim(substr($arg, $pos+1));
            $this->args[$key] = $value;
        }
        $this->main();
    }

    public function __set($name, $value){
        $this->$name = $value;
    }

    public function __get($name){
        if(isset($this -> $name))
            return $this -> $name;
        else
        {
            return Creator::getInstance() -> spawn($name);
        }
    }

    function to_log($status, $content)
    {
        echo '[' . $status . '] ' . date("Y-m-d H:i:s") . $content . "\r\n";
    }
}