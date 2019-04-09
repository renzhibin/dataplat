<?php
 class ChartManager extends Manager{

    public $mapArr = array(
      '广西壮族自治区'=>'广西',
      '内蒙古自治区'=>'内蒙古',
      '新疆维吾尔自治区'=>'新疆',
      '宁夏回族自治区'=>'宁夏',
      '西藏自治区'=>'西藏',
      '香港特别行政区'=>'香港'
    );
    public $publicArr = array(
        'color'=>array(
          //  '#2ec7c9','#b6a2de','#5ab1ef','#ffb980','#d87a80',
          // '#8d98b3','#e5cf0d','#97b552','#95706d','#dc69aa',
          // '#07a2a4','#9a7fd1','#588dd5','#f5994e','#c05050',
          // '#59678c','#c9ab00','#7eb00a','#6f5553','#c14089'

          // '#4FBFF2','#BF3038','#25A962','#A9C55D',
          // '#515093','#E05893','#E8A74A','#BCBCBC',
          // '#9457E6','#27C1CF','#b8860b'
          '#005eaa','#2b821d','#0098d9','#e6b600','#c12e34',
          '#339ca8','#cda819','#32a487',
          '#f5994e','#07a2a4','#9a7fd1','#c05050',
          '#59678c','#c9ab00','#7eb00a','#6f5553','#c14089'
        ),
        'chart'=>array(
          'renderTo'=>'',
          'type'=>''
        ),
        'backgroundColor'=>'#fff',
        'title'=>array(
          'text'=>'',
           'x'=>'center',
           'textStyle'=>array(
              'fontSize'=>'13',
              'fontWeight'=>'normal',
            )
        ),
        'toolbox'=>array(
          'show'=>true,
          'feature'=>array(
            'dataZoom'=>array(
              'show'=>false,
              'title'=>array(
                'dataZoom'=>'区域缩放',
                'dataZoomReset'=>'区域缩放-后退'    
              ),
              'color'=>'#5eb2ed'
            ),
            'saveAsImage'=>array(
              'show'=>true,
              'color'=>'#5eb2ed'
              )
          )
        ),
        'legend'=>array(
          'orient'=>'horizontal',
          "x"=>'center',
          'y'=>'bottom',
          'data'=>array()
        ),
        'calculable'=>false
      );
 	  #转化成图表数据
    function  __construct(){
       $this->objFackecube=new FackcubeManager();
    }

    //关联项目时间线
    function  projectEvent($evenArr,$data){
        //获取项目数据
        $projectSql = "select * from  t_visual_timeline  where event_id in (".implode(',',$evenArr).") ";
        $db = Yii::app()->sdb_metric_meta;
        $projectInfo = $db->createCommand($projectSql)->queryAll();
        if(!empty($projectInfo)){
            //按照给定的顺序重新排序从数据库取出的数据
            $temArr = array();
            foreach ($evenArr as $eid) {
               foreach ($projectInfo as $eitem) {
                  if($eid == $eitem['event_id']){
                       $tempArr[] = $eitem;
                  }
               }
            }
            $projectInfo = $tempArr;
            foreach ($data as $key => $value) {
              $eventArr = array();
              foreach ($projectInfo as $item => $itemInfo) {
                $timeLine = $itemInfo['event_data'];
                if(!empty($timeLine)){
                  $timeLine = json_decode($timeLine,true);
                  foreach ($timeLine as $time => $timeVal) {
                    $endDate= implode("-",explode(",",$timeVal['startDate'])); 
                    if($value['name'] == $endDate ){
                      $one['title']= $timeVal['headline'];
                      $one['text']= $timeVal['text'];
                      $eventArr[]  = $one;
                    }
                  }
                }    
              }  
              $data[$key]['event'] = $eventArr;
            }      
            return $data;
        }else{
          return array();
        }
    }
    /*
      生成带有指标 事件的数据
      $data  数据
      $key   指标key
    */
    function  chartDataEvent($data,$key){
      $returnData = array();
      foreach ($data as $item => $value) {
        $tmp_value=$value[$key];
        if(!isset ( $value[$key] ) or empty ( $value[$key] ) == true){
            $tmp_value='-';
        }
        if(isset($value['event']) && !empty($value['event']) ){
            $one = array();
            $one['value']= $tmp_value;
            $one['symbol']= 'emptypin';
            $one['symbolSize'] = 3;
            $one['event'] = $value['event'];
            $one['itemStyle'] = array(
              'normal'=>array(
                 'color'=>'#27c9cb'
              )
            );
            $returnData[] = $one;
        }else{
            $returnData[] = $tmp_value;
        }
      }
      return $returnData;
    }
    /*
    设置开关事件
    */
    function  isonEvent($data,$evenArr=array()){
      if(!empty($evenArr)){
        $eventData = $this->projectEvent($evenArr,$data);
        $data =  $this->chartDataEvent($eventData,'value');
      }else{
        $data =  $this->common->DataToArray($data,'value');
      }
      return $data;            
    }

     function  isonEventAll($data,$evenArr=array(),$tem=[]){
         if(!empty($evenArr)){
             $eventData = $this->projectEvent($evenArr,$data);
             $data =  $this->chartDataEvent($eventData,'value');
         }else{
             $data =  $this->common->DataToArrayAll($data,'value',$tem);
         }
         return $data;
     }

    //生成lendtg 转换
    function legendData($data){
      $newArr = array();
      if(!empty($data)){
        foreach ($data as $key => $value) {
            $newArr[$value] = true;
        }
      }
      return $newArr;
    }
    //生成图表数据
    /**
     生成key value 的形式
    $result 传入的数据
    $keyName 要显示的指标
    $is_time 是否时间格式
    $dim   数据显示维度
    **/
 	  function getChartData($result,$keyName,$is_time=false,$dim=array()){
        $keyName=strtolower($keyName);
        $returnData = array();
        $chartData = array();
        if(empty($result))
          return $returnData;
        foreach ($result as $name => $namevalue) {
            if($is_time){
              $nameArr = array();
              foreach ($dim as $nkey) {
                 if(is_array($namevalue[$nkey])){
                    if(isset($namevalue[$nkey]['commentdata'])){
                        $nameArr[]= strip_tags( $namevalue[$nkey]['commentdata']);
                    }else{
                        $nameArr[]=  $namevalue[$nkey]['realdata'];
                    }
                 }else{
                    $nameArr[]=  $namevalue[$nkey];
                 }
              }
              if(in_array("其它(系统自动合并)", $nameArr)){
                $returnData['name'] = '其它(系统自动合并)';
              }else{
                $returnData['name'] = implode("_", $nameArr);
              }
            }else{
               // $returnData[$name][0] = strtotime($namevalue['date'])*1000;
               // $returnData[$name][0] = $namevalue['date'];
              $returnData['name'] = $namevalue['date'];
            }
            $returnData['value'] = round($namevalue[$keyName],4);

            $chartData[] = $returnData;
        }
        return $chartData;
    } 

    #验证数据长度
    # 此方法为显示过长时缩短显示名称 但有个问题是如果指标名称相似会造成chart颜色单一，暂时放大,暂时方案为拼上序号防止重复同时也可以作为大小的序号，@TODO
    function checkChartLength($data){
      foreach ($data as $key => $value) {
          $len =  mb_strlen($value['name'],'utf-8');//$this->common->strlength($value['name']);
          if($len >= 50){
              $str =  ($key+1).'. '.mb_substr($value['name'],0,2,'utf-8')."...".mb_substr($value['name'],$len-2,2,'utf-8');
          }else{
              $str = $value['name'];
          }
          $data[$key]['name'] = $str;
          $data[$key]['detialname'] = $value['name'];
      }
      return $data;
    }
    #生成图表配置文件
    function getChartParams($chartConfig){
      $configArray = $this->publicArr;
      $configArray['chart']['renderTo'] =  $chartConfig['key'];
      $configArray['chart']['type'] =  $chartConfig['chartType'];
      $configArray['title']['text'] =  $chartConfig['chartTitle'];

      switch ($chartConfig['chartType']) {
        case 'map':
          $configArray['dataRange'] = array(
              'min'=>0,
              'x'=>'left',
              'y'=>'bottom',
              'text'=>array('高','低'),
              'calculable'=>true
          );
          $configArray['legend']['data'] = $this->common->DataToArray($chartConfig['series'],'name');
          $configArray['legend']['selected'] = $this->legendData($configArray['legend']['data']);
          $tmp =0;
          foreach ($chartConfig['series'] as $key => $value) {
            $oneSeries = array();
            $oneSeries['name'] =  $value['name'];
            $oneSeries['type'] = 'map';
            $oneSeries['mapType'] = 'china';
            //$oneSeries['roam'] =true;
            $oneSeries['itemStyle'] = array(
              'normal'=>array('label'=>array('show'=>true)),
              'emphasis'=>array('label'=>array('show'=>true))
            );
            $maxArr = $this->common->DataToArray($value['data'],'value');

            if(!empty($maxArr)){
              if($tmp < max($maxArr)){
                $tmp = max($maxArr);
              }
            }else{
              $tmp = 2000;
            }
            $mapData = array();
            foreach ($value['data'] as $m => $mVal) {
               $one = $mVal;
               if(isset($this->mapArr[$mVal['name']]) ){
                $one['name'] = $this->mapArr[$mVal['name']];
               }
               $mapData[] = $one;
            }
            $oneSeries['data'] =  $mapData;//$value['data'];
            $configArray['series'][] = $oneSeries;
          }
          //计算图表最大值，图表最大值必须被 分割段数整除（默认为100）
          if($tmp <100){
              $tmp =100;
          }else{
              $tmp = round($tmp/100) * (100);
          }
          $configArray['dataRange']['max'] = $tmp;
          $category =  $this->common->DataToArray($value['data'],'name');
          if(empty($category)){
            $category = array('');
          }
          $configArray['tooltip']= array('trigger'=>'item');
         // print_r($configArray);exit;
          break;
       
        case "area":
        case 'spline_time':
        case 'cursor_line':
          $category = array('');
          // $category =  $this->common->DataToArray($value['data'],'name');
          // if(empty($category)){
          //     $category = array('');
          // }
          $configArray['toolbox']['feature']['dataZoom']['show'] =true;
          $legend = array();
          foreach ($chartConfig['series'] as $key => $value) {
              
              if( isset($chartConfig['dataConig'][0]['chartconf'][0]['event']) ){
                  $evenArr=  $chartConfig['dataConig'][0]['chartconf'][0]['event'];
              }else{
                  $evenArr = array();
              }
              if(isset($value['more'])){
                $allXAxis = [];
                if (isset($chartConfig['xAxisTime']) && is_numeric($chartConfig['xAxisTime']) && is_int(intval($chartConfig['xAxisTime']))) {
                    $xAxisTime = intval($chartConfig['xAxisTime']);

                    $allXAxis    = [];
                    $startSecond = 1506787200;
                    $endSecond   = 1506873600;

                    while ($startSecond < $endSecond) {
                        $allXAxis[]  = date('H:i:s', $startSecond);
                        $startSecond += $xAxisTime * 60;
                    }
                } else {
                    foreach ($value['data'] as $item => $more) {
                        $category = $this->common->DataToArray($more, 'name');
                        $allXAxis = array_keys(array_merge(array_flip($allXAxis), array_flip($category)));
                    }
                    sort($allXAxis);
                }
                $tem = $allXAxis;

                foreach ($value['data'] as $item => $more) {
                  $oneSeries = array();
                  //获取名称
                  $tempArr    =  explode("@", $item);
                  $nameAll = array();
                  foreach ($tempArr as $tq) {
                      $tp = explode("$", $tq);
                      if( trim($tp[1]) !='' ){
                        $nameAll[] = $tp[1];
                      }
                  }
                  /*
                  $category =  $this->common->DataToArray($more,'name'); 
                  if(is_array($tem)){
                    if(count($tem) < count($category) ){
                      $tem = $category;
                    }
                  }
                  */
                  if(!empty($nameAll)){
                     if(!empty($value['name'])){
                       $oneSeries['name'] =  implode("_",$nameAll)."_".$value['name'];
                     }else{
                       $oneSeries['name'] =  implode("_",$nameAll);
                     }
                  }else{
                    $oneSeries['name'] = $value['name'];
                  }
                  $legend[] = $oneSeries['name'];
                  $oneSeries['type'] = 'line';
                  if($chartConfig['chartType'] =='area'){
                    $oneSeries['stack'] ='总量';
                    $oneSeries['itemStyle'] = array(
                      'normal'=>array(
                        'areaStyle'=>array(
                            'type'=>'default'
                          )
                        )
                    );
                  }
                  $oneSeries['data'] = $this->isonEventAll($more,$evenArr,$tem);
                  $oneSeries['smooth'] =true;
                  $configArray['series'][] = $oneSeries;
                }
              }else{
                $oneSeries = array();
                $oneSeries['name'] =  $value['name'];
                $oneSeries['type'] = 'line';
                if($chartConfig['chartType'] =='area'){
                  $oneSeries['stack'] ='总量';
                  $oneSeries['itemStyle'] = array(
                    'normal'=>array(
                      'areaStyle'=>array(
                          'type'=>'default'
                        )
                      )
                  );
                }
                $oneSeries['data'] = $this->isonEvent($value['data'],$evenArr);

                $oneSeries['smooth'] =true;
                $configArray['series'][] = $oneSeries;
                $category =  $this->common->DataToArray($value['data'],'name'); 
                if(count($tem) < count($category) ){
                  $tem = $category;
                }
              }      
          }
          $category = $tem;
          if(!empty($legend)){
            $configArray['legend']['data'] = $legend;
          }else{

            $configArray['legend']['data'] = $this->common->DataToArray($chartConfig['series'],'name');
          }
          $configArray['legend']['selected'] = $this->legendData($configArray['legend']['data']);
          $configArray['yAxis'] = array(
            'type'=>'value',
            'axisLabel'=>array(
              'formatter'=>''
            ),
            'axisLine'=>array(
              'lineStyle'=>array(
                'color'=>'#999',
                'width'=>1
              )
            ),
            'scale'=>true
          );     
          $configArray['xAxis'] = array(
            'type'=>'category',
            'axisLine'=>array(
              'lineStyle'=>array(
                'color'=>'#999',
                'width'=>1
              )
            ),
            'data'=>$category
          );
          if($chartConfig['chartType'] =='area'){
               $configArray['xAxis']['boundaryGap']= false;
          }
          $configArray['tooltip']= array('trigger'=>'axis');
          $configArray['grid'] = array(
                'x'=>50,
                'x2'=>50,
                'y2'=>75
          );
          if($chartConfig['chartUnit'] !='filter_not' && $chartConfig['chartUnit'] !='' ){
              $configArray['yAxis']['name'] = "单位(".$chartConfig['chartUnit'].")"; 
          }
          $configSpine =  array();
          $configSpine['chart_type'] = $chartConfig['chartType'];
          $configSpine['chart_title'] = $chartConfig['chartTitle'];
          $configSpine['dataConig'] = $chartConfig['dataConig'];
          $configSpine['header'] = $chartConfig['header'];
          if($chartConfig['chartType'] !='cursor_line'){
            $configArray['allhtml'] =$this->getChartContiner($configSpine);
          }
          //$configArray['allhtml'] =$this->getChartContiner($configSpine);
          $configArray['id'] = $this->chartConfig['container'];     
          break;
        
        case 'funnel':
          $configArray['color']=array(
             '#c12e34','#e6b600','#0098d9','#2b821d',
          '#005eaa','#339ca8','#cda819','#32a487'
          );
          $configArray['series'] =$chartConfig['series'];
          $percent = $chartConfig['series'][0]['data'][0]['value'];
          $funnelArr = array();
          $funneSourceData = $chartConfig['series'][0]['data'];
          foreach ($chartConfig['series'][0]['data'] as $key => $value) {
              $oneArr =array();
              $oneArr['name'] = $value['name'];
              $oneArr['funnel'] = $value['value'];
              if($percent !=0){
                $oneArr['value'] = round($value['value']/$percent,4)*100;
              }else{
                $oneArr['value'] = 0;
              }
              if(  $key-1  >0 ){
                  $ctrVal =  $funneSourceData[$key-1]['value'];
              }else{
                  $ctrVal = $percent;
              }

              if($ctrVal =='不存在'){
                $oneArr['layter'] = 0;
                $oneArr['funnel'] = 0;
                $oneArr['name'] = $value['name'].'(不存在)';
              }else{
                if( !empty($ctrVal)){
                   $oneArr['layter'] = round($value['value']/$ctrVal,4)*100;
                }else{
                    $oneArr['layter'] = 0;
                }
               
              }             
              $funnelArr[] = $oneArr;
          }

          $ctrAll = array();
          
          $configArray['series'][0]['data'] = $funnelArr;
          $configArray['series'][0]['type'] ='funnel';
          $configArray['series'][0]['width'] ='35%';
          $configArray['series'][0]['x'] ='30%';
          $configArray['series'][0]['itemStyle']=array(
            'normal'=>array(
                'label'=>array(
                  'show'=>true,
                  'formatter'=>"{b} {c}%"
                 )
              )
          );
          $configArray['tooltip']= array(
            'trigger'=>'item',
            'formatter'=>"{a} <br/>{b} : {c}%"
          );
          $configArray['legend']['data'] = $this->common->DataToArray($chartConfig['series'][0]['data'],'name'); 
          $configArray['legend']['selected'] = $this->legendData($configArray['legend']['data']);
          break;
        case 'pie':
          $configArray['series'] =$chartConfig['series'];
          $configArray['series'][0]['data']  = $this->checkChartLength($configArray['series'][0]['data']);
          //print_r($configArray['series']);exit;
          if( count($chartConfig['series'][0]['data']) > 15 ){
            $configArray['series'][0]['center'] = array('50%','65%');
          }else{
            $configArray['series'][0]['center'] = array('50%','55%');
          }
          $configArray['series'][0]['type'] ='pie';
          $configArray['series'][0]['radius'] ='55%';
          $configArray['series'][0]['selectedMode'] ='single';
          $configArray['series'][0]['itemStyle']=array(
            'normal'=>array(
                'label'=>array(
                  'show'=>true,
                  'formatter'=>"{b} {d}%"
                 )
              )
          );
          $configArray['tooltip']= array(
            'trigger'=>'item',
            //'formatter'=> "{a}<br/>{b} : {c} ({d}%)"
          );
          break;
        case 'column':
             $configArray['legend']['data'] = $this->common->DataToArray($chartConfig['series'],'name');
             $configArray['legend']['selected'] = $this->legendData($configArray['legend']['data']);
             foreach ($chartConfig['series'] as $key => $value) {
                $oneSeries = array();
                $oneSeries['name'] =  $value['name'];
                $oneSeries['type'] = 'bar';
                $oneSeries['data'] =  $this->common->DataToArray($value['data'],'value');
                $configArray['series'][] = $oneSeries;
             }
             $configArray['yAxis'] = array(
              'type'=>'value',
              'axisLabel'=>array(
                'formatter'=>''
              ),
              'axisLine'=>array(
                'lineStyle'=>array(
                  'color'=>'#999',
                  'width'=>1
                )
              )
             );
             $category =  $this->common->DataToArray($value['data'],'name');
             if(empty($category)){
                $category = array('');
             }
             $configArray['xAxis'] = array(
              'type'=>'category',
             // 'boundaryGap'=>false,
              'data'=>$category,
              'axisLabel'=>array(
                'rotate'=>30
              ),
              'axisLine'=>array(
                'lineStyle'=>array(
                  'color'=>'#999',
                  'width'=>1
                )
              )
             );
             $configArray['grid'] = array(
                'x'=>50,
                'x2'=>50,
                'y2'=>80
             );
             $configArray['tooltip']= array('trigger'=>'axis');
             if($chartConfig['chartUnit'] !='filter_not' && $chartConfig['chartUnit'] !='' ){
                $configArray['yAxis']['name'] = "单位(".$chartConfig['chartUnit'].")"; 
             }
          break;
        case 'hour':
           $configArray['legend']['data'] = $this->common->DataToArray($chartConfig['series'],'name');
           $configArray['legend']['selected'] = $this->legendData($configArray['legend']['data']);
           foreach ($chartConfig['series'] as $key => $value) {
              $oneSeries = array();
              $oneSeries['name'] =  $value['name'];
              $oneSeries['type'] = 'line';
              $oneSeries['data'] =  $this->common->DataToArray($value['data'],'value');
              $configArray['series'][] = $oneSeries;
           }
           $configArray['yAxis'] = array(
            'type'=>'value',
            'axisLabel'=>array(
              'formatter'=>''
            )
           );
           $category =  $this->common->DataToArray($value['data'],'name');
           if(empty($category)){
              $category = array('');
           }
           $configArray['xAxis'] = array(
            'type'=>'category',
           // 'boundaryGap'=>false,
            'data'=>$category,
            'axisLabel'=>array(
              'rotate'=>20
            )
           );
           $configArray['tooltip']= array('trigger'=>'axis');
          break;
        default:
          $configArray['chart']['type'] = 'pie';
          $configArray['series'] =$chartConfig['series'];
          $configArray['series'][0]['type'] ='pie';
          $configArray['series'][0]['selectedMode'] ='single';  
          $configArray['tooltip']= array('trigger'=>'item');
          break;
      }
      $configArray['legend']['show'] = true;
      return $configArray;
    }
    /**
     * 给每一个图表配置highcharts所需要的配置文件,返回一个配置数组
     * @param $setting 图表配置信息
     */
    function setConfig($setting){
        $configArray = $this->publicArr;
        $configArray['chart']['renderTo'] =  $setting['container'];
        $configArray['chart']['type'] =  $setting['chart_type'];
        $configArray['title']['text'] =  $setting['chart_title'];
        switch ($setting['chart_type'] ) {
          case 'area':
          case 'spline':
              $configArray['toolbox']['feature']['dataZoom']['show'] =true;
              $chartData = $this->getData($setting['dataConig'],$setting['date_from'],$setting['date_to'] );
              $configArray['legend']['data'] = $this->common->DataToArray($chartData,'name');
              $configArray['legend']['selected'] = $this->legendData($configArray['legend']['data']);
              $configArray['legend']['show'] = true;
               foreach ($chartData as $key => $value) {
                  $oneSeries = array();
                  $oneSeries['name'] =  $value['name'];
                  $oneSeries['type'] = 'line';
                  if($setting['chart_type'] =='area'){
                    $oneSeries['stack'] ='总量';
                    $oneSeries['itemStyle'] = array(
                      'normal'=>array(
                        'areaStyle'=>array(
                            'type'=>'default'
                          )
                        )
                    );
                  }

                  $project = $setting['dataConig'][0]['project'];
                  if( isset($setting['dataConig'][0]['chartconf'][0]['event']) ){
                      $evenArr =$setting['dataConig'][0]['chartconf'][0]['event'];
                  }else{
                      $evenArr = array();
                  }
                  $oneSeries['data'] = $this->isonEvent($value['data'],$evenArr);
                  //$oneSeries['data'] =  $this->common->DataToArray($value['data'],'value');
                  $oneSeries['smooth'] =true;
                  $configArray['series'][] = $oneSeries;
              }
              $configArray['yAxis'] = array(
                'type'=>'value',
                'axisLabel'=>array(
                  'formatter'=>''
                ),
                 'scale'=>true
              );
              $category =  $this->common->DataToArray($chartData[0]['data'],'name');
              if(empty($category)){
                  $category = array('');
              }
              $configArray['xAxis'] = array(
                'type'=>'category',
                'data'=>$category
              );
              if($setting['chart_type'] =='area'){
                $configArray['xAxis']['boundaryGap'] =false;
              }
              $configArray['tooltip']= array('trigger'=>'axis');
              $configArray['grid'] = array(
                  'x'=>50,
                  'x2'=>50,
                  'y2'=>75
              );
              break;
        }
        return  $configArray;
    }
    /**
     * 图表扩展功能配置数组   
     * @param $setting
     * $setting = array(
                'chart_type'=>'spline',                       //显示图表的类型 :spline(曲线型)，pie(饼状图)，line(直线型)，column(柱状图)
                'chart_title'=>'UV显示图表(三个月内)',            //图表显示的标题
                'keys'=>array(
                        'pvstat.total_uv',pvstat.goods_uv     //需要显示的数据keys  写入对应的名称
                ),
                'container'=>'show_uv',                       //显示的容器名
                'date_from'=>'2012-06-20',                    //开始时间    (可选参数，默认前三个月)
                'date_to'=>'2013-02-19',                      //结束时间   (可选参数，默认前一天)
                'style'=>array(
                        'width'=>'1100px',                    //容器宽度 (可选参数,默认为900px)  应功能扩展，  图表宽度请尽量 大于900px
                        'height'=>'400px',                    //容器高度 (可选参数,默认为500px)
                        'class'=>''                           //容器样式(为容器的样式的class名，添加此参数需要在页面中单独为容器设置样式)
                )
        );  
     */
    private $strList = array();
    public $chartConfig = array(
            'chart_type'=>'spline', //可用参数:   spline曲线  column 柱状图  area 面积图s
            'chart_title'=>'',//图表标题
            'keys'=>array(),
           // 'date_from'=>date("Y-m-d",strtotime('-3 month')),
           // 'date_to'=>date("Y-m-d",strtotime('-1 day')),
            'header'=>false
    );
    /*
    设置标题
    */
    function setTitle($title){
        $this->chartConfig['chart_title'] = $title; 
    }
    /*
    设置是否去掉头部
    */
    function setHeader(){
        $this->chartConfig['header'] = true; 
    }
    /*
    设置宽高
    */
    function setArea($width,$height){
        $this->chartConfig['style']['width'] = $width."px";
        $this->chartConfig['style']['height'] = $height."px";
    }
    /*设置时间*/
    function setTime($date_from,$date_to){ 
        $this->chartConfig['date_from'] = $date_from;
        $this->chartConfig['date_to'] = $date_to;
    }
    /*
    设置数据
    */
    function setChartData($arr){
        //设置标题
        $this->setTitle($arr['chart_title']);
        if(!isset($this->chartConfig['date_from'])){
            $this->chartConfig['date_from'] =$arr['dataConig']['date'];// date("Y-m-d",strtotime('-3 month'));
        }
        if( $arr['chart_type'] =='area' ){
            $this->chartConfig['chart_type'] = 'area';// date("Y-m-d",strtotime('-3 month'));
        }
        if(!isset($this->chartConfig['date_to'])){
            $this->chartConfig['date_to'] =$arr['dataConig']['edate'];// date("Y-m-d",strtotime('-1 day'));
        }
        $this->chartConfig['dataConig'] = $arr['dataConig'];
    }
     /*
    设置数据
    */
    function chartInit(){
       //设置Id
       $this->chartConfig['container'] =$this->generateStr(6);
       //图表宽高
       if( !isset($this->chartConfig['style']['width'])   ) {
           // $this->chartConfig['style']['width'] = '99.99%';
       }
       if(!isset($this->chartConfig['style']['height'])){
            $this->chartConfig['style']['height'] = '307px';
       }
       
    }
    /*
    设置对比数据
    */
    function contrast($key,$keyName,$data){
        $content = "<div class='compros' style='width:130px' >".$data."
        <div class='showbox btn-group'>
            <button class='ePopup btn btn-info btn-mini' type='button' url='".$key.":".$keyName."'>添加对比</button>
            <button class='showinfo btn btn-info btn-mini' type='button' url='".$key.":".$keyName."'>查看趋势</button></div>
        </div>";
        return $content;
    }   
    /*
    接口函数
    */
    function getChartContiner($chartData) {
        // echo "<pre>";
        // print_r($chartData);exit;
        $this->setChartData($chartData);
        $this->chartInit();
        //设置id
        $settingStr = $this->settingEncode($this->chartConfig );
        $str  ="<div id='{$this->chartConfig['container']}'
          class='chartContiner {$this->chartConfig['style']['class']} bg-transparent'
           style='min-height:{$this->chartConfig['style']['height']}' 
           url='".$settingStr."'></div>";
        $strcss = (is_array($chartData['dataConig']) && $chartData['dataConig'][0]['date_type'] == 'hour')? "display:none":"";
        if($this->chartConfig['header'] || $chartData['header'] ==1 ){
            return $str;
        }
        //引入html代码
        $chartStr = '<div class="chartBox" style="text-align:left;overflow:hidden;position:relative;">
               <div class="chartHeader" style="background-color:#fff;'.$strcss.'">
                 <div class="changeTime btn-group btn-group-sm">
                        <span rul="day" class="btn btn-default active">日</span>
                        <span rul="week" class="btn btn-default">周</span>
                        <span rul="month" class="btn btn-default">月</span>
                 </div>
                 <div class="btn btn-default sumTotal pull-right" style="padding:6px 6px 5px 6px;font-size:12px;margin-right:6px">求和</div>
                 <div class="timeBox btn-group btn-group-sm pull-right"  style="margin-right:5px">
                        <span rul="8 day" class="btn btn-default">1周</span>
                        <span rul="1 month" class="btn btn-default">1月</span>
                        <span rul="3 month" class="btn btn-default">3月</span>
                        <span rul="6 month" class="btn btn-default">6月</span>
                        <span rul="1 year" class="btn btn-default">1年</span>
                        <span rul="alldata" class="btn btn-default">全部</span>
                        <span rul="now_year" class="btn btn-default">今年以来</span>
                 </div>
                </div>
               <div class="chartContent" style="padding:0px;">'.$str.'
                 <div class="chartloading" style="display:none">
                  <div style=" height:300px;line-height:300px;color:#333;background:#f1f6fd;font-size:14px;padding:2px 10px 2px 10px">
                      <span><img src="/assets/img/loading.gif" width="16px"  height="16px"/></span>
                      <span>数据正在加载...</span>
                  </div>
                 </div>
               </div>
           </div>
        ';
        return  $chartStr;
    }
    /*
    随机生成不同的key
    * @param $length 生成长度
    */
    function generateStr($length){
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $password ='';
        for ( $i = 0; $i < $length; $i++ ){
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        $password .="_key";
        if(in_array($password,$this->strList)){
            $this->generate_Str($length);
        }else{
            array_push($this->strList, $password);
            return $password;
        }
    }

    /**
     * 获取接口层的数据
     * @param 数据字段 $key
     * @param 开始时间 $dateFrom
     * @param 结束时间 $dateTo
     */
    function getapiData($keyArr,$dateFrom,$dateTo){
        $returnData = array();
        foreach ($keyArr as $one => $onVal) {
          //如果小时级别的数据 特殊处理
          if(array_key_exists('date_type',$onVal)){
            switch ($onVal['date_type']) {
              case 'hour':
                  if(strlen($dateFrom)<11){
                    $dateFrom = $dateFrom.' 00:00';
                    $dateTo = $dateTo.' 00:00';
                  } else if(strlen($dateFrom)>11 && strlen($dateFrom)<=13){
                    $dateFrom = $dateFrom.':00';
                    $dateTo = $dateTo.':00';
                  }
                break;
              case 'month':
                  $dateFrom = substr($dateFrom, 0,7);
                  $dateTo = substr($dateTo, 0,7);
                break;
              default:
                # code...
                break;
            }
          } else {
            $onVal['date_type'] = 'day';
          }
          $onVal['date'] = $dateFrom;
          $onVal['edate'] = $dateTo;
          // $gardConfig = $this->visual->getVisualHeader($onVal);

          $isCustomChart = false;
          $params = $onVal;
          if (isset($params['grade']['sql'])) {
              $isCustomChart             = true;
              $params['group']           = $params['grade']['group'];  # 兼容 table 的取数逻辑
              $params['metric']          = $params['grade']['metric']; # 兼容 table 的取数逻辑
              $params['title']           = '获取自定义chart数据'; # 兼容 table 的取数逻辑
              $params['type']            = 8; # 兼容 table 的取数逻辑
              $params['page']            = 1; # 兼容 table 的取数逻辑
              $params['rows']            = 1000000; # 不分页
              $params['offset']          = 1000000; # 不分页
              $params['total']           = 0; # 不获取总数
              $params['sql']             = $params['grade']['sql'];
              $params['customsql_start'] = $params['grade']['customsql_start'];
              $params['search']          = []; # 兼容 table 的取数逻辑
              $params['master']          = 0;  # 兼容 table 的取数逻辑
              $params['customSort']      = ''; # 排序字段置为空 防止代入 date 字段出错
              // 暂不支持小时
              $params['date']            = date('Y-m-d', strtotime( $params['date']));
              $params['edate']           = date('Y-m-d', strtotime( $params['edate']));
              $params['date_type']       = 'day';
          }

          if ($isCustomChart) {
              $gardConfig = $this->visual->getCustomChartHead($params);
          } else {
              $gardConfig = $this->visual->getVisualHeader($params);
          }

          $sourceData=$this->objFackecube->getData($onVal,true);
          //支持小时的 特殊处理
          //2015-07-29 处理小时级数据 date 和 hour 合并一起作为date日期计算
          if($onVal['date_type'] == 'hour' && $sourceData['status'] == 0){
              foreach ($sourceData['data'] as $tempkey => $tempval) {
                  if(array_key_exists('hour',$tempval)){
                      $tempval['date'] = $tempval['date'].' '.$tempval['hour'].':00';
                      unset($tempval['hour']);
                      $sourceData['data'][$tempkey] = $tempval;
                  }
              }

              foreach ($onVal['chartconf'][0]['group'] as $k1 => $v1) {
                  if($v1 == 'hour'){
                      unset($onVal['chartconf'][0]['group'][$k1]);
                  }
              }    
          }

          foreach ($onVal['chartconf'][0]['chartData'] as $sVal) {
            //指标名是否隐藏
            $name_hide=[];
            if($onVal['chartconf'][0]['chartKeys'][$sVal] !=''){
                $name = $onVal['chartconf'][0]['chartKeys'][$sVal];
            }else{
//                $metricArr = explode(",", $params['metric']);
                foreach ($gardConfig['header'] as $gid => $gVal) {
                    if(strtolower($gVal['name']) == strtolower($sVal) ){
                        $gVal['cn_name'] = str_replace("\n","",$gVal['cn_name']);
                        $gVal['cn_name'] = trim($gVal['cn_name']);
                        $name = $gVal['cn_name'];
                        if( isset($onVal['chartconf'][0]['name_hide'])){
                            if(in_array($sVal, $onVal['chartconf'][0]['name_hide'])){
                                array_push($name_hide,$sVal) ;
                            }
                        }
                    }
                }
            }
            $rangeDate = $this->common->getDateRangeArray($dateFrom,$dateTo,$onVal['date_type']);
            if ($isCustomChart) {
                $currentGroup = $onVal['chartconf'][0]['group'];
                foreach ($currentGroup as $key => $value) {
                    if ($value == 'date') {
                        unset($currentGroup[$key]);
                    }
                }
                $allData  = $this->visual->getSplineData($sourceData['data'],$currentGroup,$sVal,$rangeDate);
            } else {
                $allData  = $this->visual->getSplineData($sourceData['data'],$onVal['chartconf'][0]['group'],$sVal,$rangeDate);
            }
            if(!empty($allData)){
              foreach ($allData as $all => $allitem) {
                  $tempArr    =  explode("@", $all);
                  $nameAll = array();
                  foreach ($tempArr as $tem) {
                      $tp = explode("$", $tem);
                      if( trim($tp[1]) !='' ){
                        $nameAll[] = $tp[1];
                      }
                  }
                  $chartSrcData = $this->getChartData($allitem,$sVal);
                  if(!empty($nameAll)){
                      if(empty($name_hide)){
                          $nameStr =  implode("_",$nameAll)."_".$name;
                      }else{
                        $nameStr =  implode("_",$nameAll);
                      }
//                    $nameStr =  implode("_",$nameAll)."_".$name;
                  }else{
                    $nameStr = $name;
                  }
                  $chartData = array(
                      'key'=>$sVal.implode("_",$nameAll),
                      'name'=>$nameStr,
                      'data'=>$allitem
                  );
                  $returnData[]= $chartData;
              }
            }else{
                //sql模式将cdate改为date
                foreach($sourceData['data'] as $k =>&$v){
                    if(isset($v['cdate'])){
                        $v['date']=$v['cdate'];
                    }
                };
              $chartSrcData = $this->getChartData($sourceData['data'],$sVal);
                $chartSrcData = $this->common->arrSort($chartSrcData,'date','asc');
                //var_dump($chartSrcData);
              $chartSrcData = $this->visual->checkData($chartSrcData,$rangeDate);
              $chartData = array(
                  'key'=>$sVal,
                  'name'=>$name,
                  'data'=>$chartSrcData
              );
              $returnData[]= $chartData;
            }

          }

        }
        return $returnData;
    }
    /**
     * 报表通用sql方法
     */
    function getSqlData($params,$dateFrom, $dateTo){
        
        if(!$dateFrom){
            $dateFrom = date('Y-m-d',strtotime('-3 month'));
        }
        if(!$dateTo){
            $dateTo = date('Y-m-d',strtotime('-1 day'));
        }
        $params[1]  = str_replace("{starttime}", "'".$dateFrom."'", $params[1]);
        $params[1]  = str_replace("{endtime}", "'".$dateTo."'", $params[1]);
        $sqlComm = $params[1]." order by dt asc";
        $result = array();
        $sqlData = array();
        if(isset($param[3])){
            $dbName = $param[3];
        }else{
            $dbName = 'sdb_focus';
        }
        $db = Yii::app()->$dbName;    
        $result = $db->createCommand($sqlComm)->queryAll();
        return $this->getChartStyleData($result);
    }
    /*转换成图表需要的数据*/
    function getChartStyleData($data){
        $nameData = array();
        foreach($data as $key=>$val){
            $nameData[$key][0] =reset($val);
            $nameData[$key][1] =floatval(end($val));
        }
        return $nameData;
    }
    /**
     * 处理接口层的数据，变成图表需要的数据
     * 目前新增约定的数据格式　：原数据格式：[时间，数据值]
     　*    新增数据格式：[时间，数据值，原因(或说明),数据点状态值(两种状态，1为高亮，2为普通显示)] 　　　　　
     　* @param 数据字段 $key
     　*/
    function getData($key,$dateFrom,$dateTo){
        $chartData = $this->getapiData($key,$dateFrom,$dateTo);
        //$newData = $this->changeDate($chartData);
        return $chartData;
    }
    /**
     *生成表格 
     */
    function tableexcel($data){
        $res = array();
        if($data[0]['name'] == $data[1]['name']){
            //同一指标，不同时间
            foreach($data as $k1=>$v1){
                foreach($v1['time'] as $tk=>$time){   
                    if(!strstr($time, '-')){
                        $time = date('Y-m-d',($time/1000));
                    }
                    $res[$tk]['time_'.$k1] = $time;
                    $res[$tk][$v1['name'].'_'.$k1] = $v1['data'][$tk];
                }   
            }
        }else{
            //不同指标          
            foreach($data as $k1=>$v1){
                foreach($v1['time'] as $tk=>$time){
                    if(!strstr($time, '-')){
                        $time = date('Y-m-d',($time/1000));
                    }
                    if (!array_key_exists($time, $res)){
                        $res[$time] = array('time'=>$time, $v1['name']=>$v1['data'][$tk]);
                    }else{
                        $res[$time][$v1['name']] = $v1['data'][$tk];
                    }
                }
            }
        }
        $html = "";         
        $html = "<table width='100%' border='1' cellpadding='0' cellspacing='0' style='margin:auto'>";
        //取出所有名称
        $key1 =  array_keys($res);      
        $lenNum = 0;
        $keyNameArr = array();
        foreach ($res as $len => $lenVal) {
            if(count($lenVal) > $lenNum){
                $lenNum = count($lenVal);
                $keyNameArr = $lenVal;
            }
        }
        $key2 = array_keys($keyNameArr);
        $html .= "<tr>";
        foreach ($key2 as $name => $val){
            $html .= "<td align='center'>".$val."</td>";
        }
        $html .= "</tr>";
        foreach ($res as $b => $s){
            $html .= "<tr>";
            //取出数组
            $keyArr = array_keys($keyNameArr);
            for($j = 0; $j< count($keyNameArr); $j++){                              
                $html .= "<td align='center'>".$s[$keyArr[$j]]."</td>";                             
            }
            $html .= "</tr>";
        }
        $html .= "</table>";
        return $html;
    }
    /**
     * 根据给写的keys 数组　生成相应的table
     * @param 一系列keys集合　 $keyArr　　$keyArr =array('pvstat.total_uv','pvstat.total_pv');
     * @param  $dateFrom    开始时间 　可选默认三个月前
     * @param  $dateTo　　　 结束时间　可选默认前一天
     */
    function getHtmlTable($setting,$dateFrom,$dateTo,$tabClass){
        if(!$dateFrom){
            $dateFrom = date('Y-m-d',strtotime('-3 month'));
        }
        if(!$dateTo){
            $dateTo = date('Y-m-d',strtotime('-1 day'));
        }
        $arrSer = array();
        $timeArr = array();
        $chartData = $this->getapiData($setting['keys'],$dateFrom,$dateTo );
        //print_r($chartData);exit;
        $abMERGE= array();
        foreach( $chartData as $index=>$dataArray ) {
            foreach( $dataArray['data'] as $values ) {
                list( $dt, $value ) = $values;
                if(!array_key_exists($dt, $abMERGE ) ) {
                    $abMERGE[$dt] = array();
                }
                $abMERGE[$dt][$dataArray['name']] = $value;
            }
        }
        $abMERGE = array_reverse($abMERGE);
        $headerName = array();
        $num = 0;
        foreach ($abMERGE as $key=>$val){
            $abMERGE[$key]['dt'] = $key;
            //获取最长名称
            if(count($val) >$num){
                foreach ($val as $keyid =>$val){
                       if(!in_array($keyid, $headerName)){
                            array_push($headerName, $keyid);
                       }
                }
                $num = count($val);
            }
        }
        $html = "<table width='100%' class='".$tabClass." table table-striped table-bordered table-hover table-condensed' border='1' style='margin:auto'>";
        /*输出标题*/
        $html .= "<tr>";
        $html .= "<td align='center'>时间</td>";
        foreach ($headerName as $title => $cont){
            $html .= "<td align='center'>".$cont."</td>";
        }
        $html .= "</tr>";
        /*输入内容 */
        foreach ($abMERGE as $name => $data){
            $html .= "<tr>";
            $html .= "<td align='center'>".$data['dt']."</td>";
            foreach ($headerName as $dataVal){
                $html .= "<td align='center'>".$data[$dataVal]."</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</table>";
        return $html;
     }
    /*
    *数据求合
    */
    function sumData($data,$type){
        $sumData = array();
        foreach($data  as $key =>$val){
            $oneData = array();
            $oneData['name'] = $val['name'];
            $totalNum = 0;
            if($type =='day'){
                foreach($val['data'] as $item){
                    $totalNum += $item['value'];
                }
            }else{
                foreach($val['data'] as $item){
                    $totalNum += $item['value'];
                }
            }
            if(count($val['data']) ==0){
                $oneData['avg'] = 0;
            }else{
                $oneData['avg'] =number_format( round($totalNum/count($val['data']),2),2);
            }
            //$oneData['avg'] =number_format( round($totalNum/count($val['data']),2),2);
            $oneData['total'] =number_format($totalNum);
            $oneData['type'] = $type;
            $sumData[] = $oneData;
        }
        return $sumData;
    }
    /**
     * 把数据格式化的时间转换成时间戳
     * @param $data 约定格式的数据 [2012-05-07,58755],[2012-05-07,58755,reason,1];
     */
    function changeDate($data){
        if(is_array($data)){
            foreach($data as $key => $val){
                foreach($val['data'] as $k => $t){
                    if(!strstr($t[0],'-') ){
                        $data[$key]['data'][$k][0] = $t[0]*1000;// ($t[0] + 8*3600)*1000;
                    }else{
                        $data[$key]['data'][$k][0] = strtotime($t[0])*1000;// (strtotime($t[0])+8*3600)*1000;
                    }
                }
            }
         }else{
            echo '无法获取数据';exit;
         }
        return $data;
    }
    /**
     * 月 周 日 时间处理
     */
    function dayweekmonthData($type,$chartArr){


         $retunArr = array();
         $count = 1;
         $num = 0;
         $timeStr = null;
         $str = array();
         $data = array();
         $datatimes = array();
         switch ($type){
             case 'day':
                return $chartArr;
             break;
             case 'week':               
                //获取当天是星期几，然后累加
                foreach($chartArr as $key => $val){
                    $w = date('w',strtotime($val['name']));
                    if($w!=0){
                        if( $count  <= 1 ){
                              $str[] = $val['name'];
                              $count ++;
                        }
                          //如果当前val 等于最后一天
                          if($key == (count($chartArr)-1)){
                                $str[] = $val['name'];
                                $datatimes[] = implode('~',$str);
                          }
                          $num  += $val['value'];
                          continue;
                   }else{
                          $str[] = $val['name'];
                          $num += $val['value'];
                          $data[] = $num;
                          $datatimes[] = implode('~',$str);
                          $num = 0;
                          $count = 1;
                          $str = array();
                   }
                }
                if(!empty($num)){
                   $data[] = $num;
                }
                $retunArr['xaxis'] =  $datatimes;
                $retunArr['data'] = $data;
                return $retunArr;                                   
               break;
             case 'month':
                foreach($chartArr as $key => $val){
                    $firstday = date('Y-m-01', strtotime($val['name']));
                    $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
                    if($lastday != $val['name']){
                          if( $count  <= 1 ){
                              $str[] = $val['name'];
                              $count ++;
                          }
                          //如果当前val 等于最后一天
                          if($key == (count($chartArr)-1)){
                                $str[] = $val['name'];
                                $datatimes[] = implode('~',$str);
                          }
                          $num  += $val['value'];
                          continue;
                   }else{
                          $str[] = $val['name'];
                          $num += $val['value'];
                          $data[] = $num;
                          $datatimes[] = implode('~',$str);
                          $num = 0;
                          $count = 1;
                          $str = array();
                   }
                }
                if(!empty($num)){
                   $data[] = $num;
                }
                $retunArr['xaxis'] =  $datatimes;
                $retunArr['data'] = $data;
                return $retunArr;                        
               break;
         }
    }
    /**
     * 加密encode成base64串
     * @param $setting
     */
    function settingEncode( $setting ) {
        return base64_encode(json_encode($setting));
    }
    /**
     * 解密decode成数组
     * Enter description here ...
     * @param $settingStr
     */
    function settingDecode( $settingStr ) {
        return json_decode( base64_decode( $settingStr ),true); //强制转换成数组
    }
 }
