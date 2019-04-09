<?php

class MysqlCModel{
	
	function __construct(){

	}
        /**
         * 解析sql语锯
         * @param type $limit 限制的条数
         * @param type $offset 偏移量
         * @return string
         */
        public function parseLimit($limit, $offset) {
            $limitStr = '';
            if ($limit > 0) {
                $limitStr .= ' LIMIT ' . intval($limit);
                if ($offset > 0) {
                    $limitStr .= ' OFFSET ' . intval($offset);
                }
            }
            return $limitStr;
        }
	/*
	 数据库添加操作类
	 $params['table']   
	 $params['data'] = array('key'=>value)
	*/
	public function runInsert($params, $db='metric_meta'){
	    $keyArr = array();
	    $valueArr = array();
	    foreach ($params['data'] as $k => $v) {
	    	$valueArr[":".$k] = $v;
	    	$keyArr[] = "`".$k."`";
	    }
		$sqlComm = "insert into {$params['table']} (".implode(",",$keyArr).") values (".implode(",", array_keys($valueArr)).")";		
		$dbname = 'db_'.$db;
		$sth = Yii::app()->$dbname;
		$command = $sth->createCommand($sqlComm);
		foreach ($valueArr as $sqlkey => $sqkVal) {
			$command->bindValue($sqlkey,$sqkVal,PDO::PARAM_STR);
		}
		$re  =  $command->execute();
		$id = $sth->getLastInsertID();
		if ($id > 0){
			return $id;
		}else{
			return $re;
		}
	} 
	/*
	 数据库添加操作类
	 $params['table']   
	 $params['data'] = array('key'=>value)
	 $params['where'] = array('key'=>value)
	*/
	public function runUpate($params,$db='metric_meta'){
	    $keyArr = array();
	    $valueArr = array();
	    foreach ($params['data'] as $k => $v) {
	    	$strkey = ":".$k;
	    	$valueArr[$strkey] = $v;
	    	$str  = "`".$k."`";
	    	$setArr[]  = $str." =".$strkey;
	    }
		
		if(empty($params['where'])){
			return false;
		}else{
			$where = '';
			foreach ($params['where'] as $k=>$v){
				if (is_array($v)) {
					$str = implode(',', $v);
					$where .= " and $k in ($str) ";
				}
				else{
					if (substr($k, 0, 1) == '_'){
						$where .= ' and '.substr($k, 1).'=:'.$k;
					}
					else{
						$where .= " and $k=:$k";
					}
					$sqlData[$k] = $v;
				}
				$strkey = ":".$k;
	    		$valueArr[$strkey] = $v;
			}
			$where = substr($where, 4);

			if (empty($where)){
				return FALSE;
			}
		}
		$sqlComm = "update {$params['table']} set ".implode(",",$setArr)." where  {$where}";
		$dbname = 'db_'.$db;
		$sth = Yii::app()->$dbname;
		$command = $sth->createCommand($sqlComm);
		foreach ($valueArr as $sqlkey => $sqkVal) {
			$command->bindValue($sqlkey,$sqkVal,PDO::PARAM_STR);
		}
		$re  =  $command->execute();
		$id = $sth->getLastInsertID();
		if ($id > 0){
			return $id;
		}else{
			return $re;
		}
	} 
}