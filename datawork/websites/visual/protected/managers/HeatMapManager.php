<?php

class HeatMapManager extends Manager
{
    function __construct()
    {
        $this->user_address = 'user_address';
        $this->amap_shop_all="amap_shop_all";
        $this->baidu_shop_all="baidu_shop_all";
        $this->amap_yg_zone_rate="amap_yg_zone_rate";
        $this->amap_yanjiu_shop_all="amap_yanjiu_shop_all";
        $this->amap_hangzhou_shop_all="amap_shop_all_1";
        $this->amap_all_zone_rate="amap_all_zone_rate";
        $this->amap_tj_zone_rate="amap_tj_zone_rate";

    }

//    function getSaleZoneCoords(){
//        $sale_zone_coords=$this->sale_zone_coords;
//        $sql="select sale_name,uid,zone_id,center_coord,`position`,style,`desc` from {$sale_zone_coords}";
//        $db=Yii::app()->sdb_dt_db;
//        $data=$db->createCommand($sql)->queryAll();
//        return $data;
//
//    }
        /**
        过滤掉大超市
        060201	购物服务	便民商店/便利店	7-ELEVEn便利店
        060202	购物服务	便民商店/便利店	OK便利店
        060401	购物服务	超级市场	家乐福
        060402	购物服务	超级市场	沃尔玛
        060403	购物服务	超级市场	华润
        060404	购物服务	超级市场	北京华联
        060405	购物服务	超级市场	上海华联
        060406	购物服务	超级市场	麦德龙
        060407	购物服务	超级市场	乐天玛特
        060408	购物服务	超级市场	华堂
        060409	购物服务	超级市场	卜蜂莲花
        060411	购物服务	超级市场	屈臣氏
        060413	购物服务	超级市场	惠康超市
        060414	购物服务	超级市场	百佳超市
        060415	购物服务	超级市场	万宁超市
         **/
    function getGDMarket($params){
        $market_type='all';
        if(array_key_exists('market_type',$params)){
            $market_type=$params['market_type'];
        }
        $amap_shop_all=$this->amap_shop_all;
        $sql="select shop_name,address,latitude,longitude,shop_tel from {$amap_shop_all} ";
        #过滤大超市数
        $sql=$sql."where shop_typecode not like '%060201%' and ";
        $sql=$sql."shop_typecode not like '%060202%' and ";
        $sql=$sql."shop_typecode not like '%060401%' and ";
        $sql=$sql."shop_typecode not like '%060402%' and ";
        $sql=$sql."shop_typecode not like '%060403%' and ";
        $sql=$sql."shop_typecode not like '%060404%' and ";
        $sql=$sql."shop_typecode not like '%060405%' and ";
        $sql=$sql."shop_typecode not like '%060406%' and ";
        $sql=$sql."shop_typecode not like '%060407%' and ";
        $sql=$sql."shop_typecode not like '%060408%' and ";
        $sql=$sql."shop_typecode not like '%060409%' and ";
        $sql=$sql."shop_typecode not like '%060411%' and ";
        $sql=$sql."shop_typecode not like '%060413%' and ";
        $sql=$sql."shop_typecode not like '%060414%' and ";
        $sql=$sql."shop_typecode not like '%060415%' ";

        $bj_sql=$sql;
        $union=" union ";
        $yanjiu_sql=" select shop_name,address,latitude,longitude,shop_tel from {$this->amap_yanjiu_shop_all} ";

        $sql=$sql.$union.$yanjiu_sql;

        $shop_all_1=" select shop_name,address,latitude,longitude,shop_tel from {$this->amap_hangzhou_shop_all} ";

        $hz_sql=" select shop_name,address,latitude,longitude,shop_tel from {$this->amap_hangzhou_shop_all} where pname='浙江省' ";

        $tj_sql=" select shop_name,address,latitude,longitude,shop_tel from {$this->amap_hangzhou_shop_all} where pname='天津市' ";

        $hb_sql=" select shop_name,address,latitude,longitude,shop_tel from {$this->amap_hangzhou_shop_all} where pname='河北省' ";

        $sql=$sql.$union.$shop_all_1;

        if($market_type=='yanjiu'){
            $sql="select shop_name,address,latitude,longitude,shop_tel from {$this->amap_yanjiu_shop_all} ";
        }
        //杭州
        if(array_key_exists('region',$params) and strval($params['region'])=='1002'){
            $sql=$hz_sql;
        }
        //北京
        if(array_key_exists('region',$params) and strval($params['region'])=='1000'){
            $sql=$bj_sql.$union.$yanjiu_sql.$union.$hb_sql;
        }
        //天津
        if(array_key_exists('region',$params) and strval($params['region'])=='1001'){
            $sql=$tj_sql;
        }


        $db=Yii::app()->sdb_scrapy;
        $data=$db->createCommand($sql)->queryAll();
        return $data;

    }


    function getBaiduMarket($params){

        $amap_shop_all=$this->baidu_shop_all;
        $sql="select shop_name,address,gg_lat as latitude,gg_lon as longitude,shop_tel from {$amap_shop_all} ";
        $where=' where 1=1 ';
        if($params['region']!='all'){
            if($params['region']=='1002'){
                $where.=" and zone_name='杭州'";
            }

        }
        $sql=$sql.$where;

        $db=Yii::app()->sdb_scrapy;
        $data=$db->createCommand($sql)->queryAll();
        return $data;

    }



    function getGYzoneRate($params){
//        $amap_yg_zone_rate=$this->amap_yg_zone_rate;
        $amap_all_zone_rate=$this->amap_all_zone_rate;
//        $amap_tj_zone_rate=$this->amap_tj_zone_rate;

        $sql="select gaode,yg,rate,gaode_num,yg_num,zone_coords from {$amap_all_zone_rate} where gaode_num>5 and (rate>2 or (gaode_num-yg_num>=15))";
        $region=$params['region'];
        $where='';
        if($region!='all' and $region!=''){

            $where=' and zone_id='.$region;
        }
        $sql=$sql.$where;
        $db=Yii::app()->sdb_scrapy;
        $data=$db->createCommand($sql)->queryAll();
        return $data;

    }

    //fetch success visit data and status=2
    function getVisitData($params) {
        $result = array();
        if (!empty($params['cur_date']) && !empty($params['uid'])) {

            $sql = "select a.*,b.* from
            (
            select uid,address_id,position as visit_position,FROM_UNIXTIME(created_at,'%Y-%m-%d %H:%i:%s') as cate_date from lsh_sales.sales_visit_records where uid=".$params['uid']." and from_unixtime(created_at,'%Y-%m-%d')='".$params['cur_date']."' and status = 2 order by created_at asc
            )a inner join
            (
                select market_name,contact_name,contact_phone,address,address_id,real_position as market_position,`status`,zone_id from lsh_market.user_address
            )b on a.address_id = b.address_id";

            $result = Yii::app()->sdb_lsh_market->createCommand($sql)->queryAll();
            return !empty($result) ? $result : array();
        }
        return $result;
    }

    function getMarketData($uid) {
        $sql = "
        select a.uid,b.* from
            (
                select address_id,uid from lsh_sales.salesman_market where uid={$uid}
            )a inner join (
                select market_name,contact_name,contact_phone,address,address_id,real_position,zone_id from lsh_market.user_address where `status`=3 and is_deleted=0
            )b on a.address_id = b.address_id";
        $result = Yii::app()->sdb_lsh_market->createCommand($sql)->queryAll();
        return !empty($result) ? $result : array();
    }
}
