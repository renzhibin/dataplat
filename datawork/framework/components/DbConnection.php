<?php

class DbConnection extends CDbConnection {
    public $dbs = array();

    public function init() {
        $index = rand(0, count($this->dbs)-1);        
        foreach($this->dbs[$index] as $key=>$value) {
            !in_array($key, array('host', 'port', 'db', 'database', 'direct')) ? $this->$key=$value:null;
        }
        
        // if (YII_DEBUG && !$this->dbs[$index]['direct']) {
            
        //     $remoteHost = $this->dbs[$index]['host'];
        //     $remotePort = $this->dbs[$index]['port'];
        //     $db         = $this->dbs[$index]['db'];
        //     $database   = $this->dbs[$index]['database'];

        //     $localPort = Yii::app()->cache->get($database);
        //     if ($localPort && @fsockopen('127.0.0.1', $localPort)) {
        //         $this->connectionString = "mysql:host=127.0.0.1;port={$localPort};dbname={$db}";
        //     } else {
        //         // build the tunnel
        //         $localPort = $this->availablePort();
                
        //         $shell = "ssh -f ".DEVELOPER."@osys11.meilishuo.com -L {$localPort}:{$remoteHost}:{$remotePort} -N";
                
        //         $fp = popen($shell, 'r');
        //         stream_set_blocking( $fp , false );
        //         sleep(1);
        //         if (!fgets($fp, 4096)) {
        //             $this->connectionString = "mysql:host=127.0.0.1;port={$localPort};dbname={$db}";
        //             Yii::app()->cache->set($database, $localPort);
        //         }
        //     }
        // }
        parent::init();
    }

    public function availablePort()
    {
        $socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        socket_bind($socket,'0.0.0.0',0);
        socket_getsockname($socket, $IP, $PORT);
        socket_close($socket);
        return $PORT;
    }
}