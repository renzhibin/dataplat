<script id='charttpl' type='text/x-dot-template'>
  {{ if( it.length >0 ){  }}
	<div class='row chart_bg' style="margin:0px 0px 10px 0px;">
      <ul id="drapQ">
       {{~it:chart:kid }}
        <li class ='chart_bottom' style='width:{{=chart.chartconf[0].chartWidth}}%'
         data-index='{{=kid}}'>
          <div class='chartlist' style='position:relative;padding:1px'>
            <div class='btn-group pull-right' style='position:absolute;top:0px;right:0px'>
              <span class='chartedit btn btn-primary btn-xs'>编辑</span>
              <span class='chartclose btn btn-primary btn-xs'>删除</span>
            </div>
  	       <div class='chart_box'  id="chart_box_init_{{=kid}}"
            {{? chart.chartconf[0].header ==1 && ( chart.chartconf[0].chartType == 'spline_time' ||  chart.chartconf[0].chartType =='area') }}
            {{??}}
                style='min-height:343px;'
            {{?}}
            ></div>
           <div style="height:343px;width:99%;" class='chartloading'>
                <span><img src="/assets/img/loading.gif" width="16px"  height="16px"/></span>
                <span style='color:#000'>数据正在加载...</span>
            </div>
          </div>
        </li>
       {{~}}
      </ul>
  </div>
  {{ } }}
</script>
