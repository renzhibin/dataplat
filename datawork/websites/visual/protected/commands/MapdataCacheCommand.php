<?php
class MapdataCacheCommand extends Command {
    public  $objReport;
    public  $objVisual;
    function __construct(){
        $this->objReport=new ReportManager();
        $this->objVisual=new VisualManager();
    }
    //更新mapdata缓存数据
    public function main(){

        $map_configs=$this->objVisual->selectMapData();
        Yii::app()->cache->hashKey=false;
        foreach($map_configs as $mapdata){
            $map_key=$mapdata['map_key'];
            $map_data=$mapdata['map_data'];
            $map_v=implode(PHP_EOL,$this->objVisual->selectMapDataBySql($map_data));
            $cache_key=$map_key.':mapdata';
            Yii::app()->cache->set($cache_key,$map_v);
        }




    }
}
