{/include file="layouts/header.tpl"/}
{/include file="layouts/script.tpl"/}
<div style='height:10px'>
   <div id='chart' style="width:600px;height:400px;margin:auto"></div>
</div>
<script type="text/javascript">
  $(function(){
     option =  {
    "chart": {
        "renderTo": "container_150820_550",
        "type": "area"
    },
    "backgroundColor": "#fff",
    "title": {
        "text": "总体成交构成走势",
        "x": "center"
    },
    "toolbox": {
        "show": true,
        "feature": {
            "dataZoom": {
                "show": true,
                "title": {
                    "dataZoom": "区域缩放",
                    "dataZoomReset": "区域缩放-后退"
                },
                "color": "#5eb2ed"
            },
            "saveAsImage": {
                "show": true,
                "color": "#5eb2ed"
            }
        }
    },
    "legend": {
        show:false, 
        "orient": "horizontal",
        "x": "center",
        "y": "bottom",
        "data": [
            "上衣_成交金额",
            "内衣_成交金额",
            "包包_成交金额",
            "家居_成交金额",
            "家电_成交金额",
            "男装_成交金额",
            "童装_成交金额",
            "精选_成交金额",
            "裙子_成交金额",
            "裤子_成交金额",
            "配饰_成交金额",
            "鞋子_成交金额",
            "食品_成交金额",
            "NULL_成交金额"
        ],
        "selected": {
            "上衣_成交金额": true,
            "内衣_成交金额": true,
            "包包_成交金额": true,
            "家居_成交金额": true,
            "家电_成交金额": true,
            "男装_成交金额": true,
            "童装_成交金额": true,
            "精选_成交金额": true,
            "裙子_成交金额": true,
            "裤子_成交金额": true,
            "配饰_成交金额": true,
            "鞋子_成交金额": true,
            "食品_成交金额": true,
            "NULL_成交金额": true
        }
    },
    "calculable": false,
    "series": [
        {
            "name": "上衣_成交金额",
            "type": "line",
            "stack": "总量",
            "itemStyle": {
                "normal": {
                    "areaStyle": {
                        "type": "default"
                    }
                }
            },
            "data": [
                "107616.27",
                "101504.38",
                "112891.33",
                "120155.43",
                "128337.03",
                "531437.88",
                "317748.62"
            ],
            "smooth": true
        },
        {
            "name": "内衣_成交金额",
            "type": "line",
            "stack": "总量",
            "itemStyle": {
                "normal": {
                    "areaStyle": {
                        "type": "default"
                    }
                }
            },
            "data": [
                "10260.92",
                "10009.32",
                "8717.28",
                "13192.26",
                "8564.51",
                "51959.98",
                "31617.78"
            ],
            "smooth": true
        },
        {
            "name": "包包_成交金额",
            "type": "line",
            "stack": "总量",
            "itemStyle": {
                "normal": {
                    "areaStyle": {
                        "type": "default"
                    }
                }
            },
            "data": [
                "34647.82",
                "34433.23",
                "28266.47",
                "42899.85",
                "26179.70",
                "177312.32",
                "91405.75"
            ],
            "smooth": true
        },
        {
            "name": "家居_成交金额",
            "type": "line",
            "stack": "总量",
            "itemStyle": {
                "normal": {
                    "areaStyle": {
                        "type": "default"
                    }
                }
            },
            "data": [
                "9337.32",
                "9976.41",
                "13116.60",
                "13057.83",
                "13124.03",
                "74471.72",
                "41429.88"
            ],
            "smooth": true
        },
        {
            "name": "家电_成交金额",
            "type": "line",
            "stack": "总量",
            "itemStyle": {
                "normal": {
                    "areaStyle": {
                        "type": "default"
                    }
                }
            },
            "data": [
                "2728.25",
                "3331.50",
                "3160.52",
                "3621.84",
                "2843.81",
                "25828.66",
                "12965.59"
            ],
            "smooth": true
        },
        {
            "name": "男装_成交金额",
            "type": "line",
            "stack": "总量",
            "itemStyle": {
                "normal": {
                    "areaStyle": {
                        "type": "default"
                    }
                }
            },
            "data": [
                "26116.31",
                "19684.44",
                "35962.97",
                "36896.31",
                "24930.34",
                "136074.24",
                "78658.17"
            ],
            "smooth": true
        },
        {
            "name": "童装_成交金额",
            "type": "line",
            "stack": "总量",
            "itemStyle": {
                "normal": {
                    "areaStyle": {
                        "type": "default"
                    }
                }
            },
            "data": [
                "2496.68",
                "3109.40",
                "4090.96",
                "3457.97",
                "2408.99",
                "27659.74",
                "16474.78"
            ],
            "smooth": true
        },
        {
            "name": "精选_成交金额",
            "type": "line",
            "stack": "总量",
            "itemStyle": {
                "normal": {
                    "areaStyle": {
                        "type": "default"
                    }
                }
            },
            "data": [
                "84618.86",
                "89878.81",
                "89475.79",
                "92245.12",
                "80436.85",
                "556895.70",
                "312700.75"
            ],
            "smooth": true
        },
        {
            "name": "裙子_成交金额",
            "type": "line",
            "stack": "总量",
            "itemStyle": {
                "normal": {
                    "areaStyle": {
                        "type": "default"
                    }
                }
            },
            "data": [
                "64564.25",
                "64746.50",
                "63007.18",
                "55470.56",
                "44394.71",
                "195349.76",
                "87132.83"
            ],
            "smooth": true
        },
        {
            "name": "裤子_成交金额",
            "type": "line",
            "stack": "总量",
            "itemStyle": {
                "normal": {
                    "areaStyle": {
                        "type": "default"
                    }
                }
            },
            "data": [
                "59816.49",
                "61601.63",
                "60568.65",
                "70133.40",
                "69994.63",
                "237158.20",
                "138538.01"
            ],
            "smooth": true
        },
        {
            "name": "配饰_成交金额",
            "type": "line",
            "stack": "总量",
            "itemStyle": {
                "normal": {
                    "areaStyle": {
                        "type": "default"
                    }
                }
            },
            "data": [
                "10155.97",
                "13163.45",
                "11338.89",
                "12038.09",
                "8884.42",
                "69654.30",
                "28514.93"
            ],
            "smooth": true
        },
        {
            "name": "鞋子_成交金额",
            "type": "line",
            "stack": "总量",
            "itemStyle": {
                "normal": {
                    "areaStyle": {
                        "type": "default"
                    }
                }
            },
            "data": [
                "56538.23",
                "60455.36",
                "60505.83",
                "68395.71",
                "65555.75",
                "269619.10",
                "163651.17"
            ],
            "smooth": true
        },
        {
            "name": "食品_成交金额",
            "type": "line",
            "stack": "总量",
            "itemStyle": {
                "normal": {
                    "areaStyle": {
                        "type": "default"
                    }
                }
            },
            "data": [
                "5113.95",
                "4904.99",
                "3569.33",
                "3990.24",
                "4167.11",
                "30218.19",
                "19861.26"
            ],
            "smooth": true
        },
        {
            "name": "NULL_成交金额",
            "type": "line",
            "stack": "总量",
            "itemStyle": {
                "normal": {
                    "areaStyle": {
                        "type": "default"
                    }
                }
            },
            "data": [
                "2137.36",
                "1573.90",
                "1520.90",
                "1757.52",
                "2151.71",
                "11338.70",
                "7474.11"
            ],
            "smooth": true
        }
    ],
    "yAxis": {
        "type": "value",
        "axisLabel": {
            "formatter": ""
        },
        "axisLine": {
            "lineStyle": {
                "color": "#999",
                "width": 1
            }
        },
        "scale": true
    },
    "xAxis": {
        "type": "category",
        "axisLine": {
            "lineStyle": {
                "color": "#999",
                "width": 1
            }
        },
        "data": [
            "2015-08-13",
            "2015-08-14",
            "2015-08-15",
            "2015-08-16",
            "2015-08-17",
            "2015-08-18",
            "2015-08-19"
        ],
        "boundaryGap": false
    },
    "tooltip": {
        "trigger": "axis"
    },
    "grid": {
        "x": 50,
        "x2": 50,
        "y2": 10
    },
    "id": "texcde_key"
    }
                    
 
  var myChart = echarts.init(document.getElementById('chart')); 
  myChart.setOption(option); 
  var divCharts = $('#chart'); 
  console.log(myChart.chart);
  var legend = myChart.chart['line'].component.legend;
  var lendBox = $('<div class="legend_layter"><b class="left"></b><b class="right"></b></div>').appendTo(divCharts);
  var divLegends = $('<div class="legend_box"></div>').appendTo(lendBox);
  $(option.legend.data).each(function(i,l){
      var color = legend.getColor(l);
      var labelLegend = $('<label class="legend">' +
              '<span class="label" style="background-color:'+color+'"></span>'+l+'</label>');
      labelLegend.mouseover(function(){
          labelLegend.css('color', color).css('font-weight', 'bold');
      }).mouseout(function(){
          labelLegend.css('color', '#333').css('font-weight', 'normal');
      }).click(function(){
          labelLegend.toggleClass('disabled');
          legend.setSelected(l,!labelLegend.hasClass('disabled'));
      });
      divLegends.append(labelLegend);
  });
  var left = 0;
  $('body').on('click','.legend_layter b.left',function(){
      var obj =  $(this).closest('.legend_layter').find('.legend_box');
      realleft = parseInt(left) + 60;
      if( left  < 0 ){
        obj.css({'left': realleft +"px"});
        left  = parseInt(left) +  60;
      }
  });
  $('body').on('click','.legend_layter b.right',function(){
      var parent  = $(this).closest('.legend_layter');
      var obj =  parent.find('.legend_box');
      var width = obj.width();
      var parentWidth = parent.width();

      realleft = parseInt(left) - 60;
      if(  Math.abs( width - parentWidth )  >  Math.abs(left) ){
        obj.css({ 'left': realleft +"px" });
        left  = parseInt(left) - 60;
      }      
  });
});
  
</script>

