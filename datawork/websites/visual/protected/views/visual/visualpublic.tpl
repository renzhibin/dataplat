<!--excelmap-->
<div id='excelMap'>
</div>
<!--报表保存框-->
<div id='visualBox'>
  <table class="table table-bordered table-condensed tbHeader"
    style="margin:0px">
      <tr>
         <td style='width:130px;text-align:right'>报表名称<b style=" color:#ff0000">*</b>:</td>
         <td>
           <input type='text' class='form-control cn_name' >
         </td>
      </tr>
      <tr>
         <td style='width:130px;text-align:right'>报表说明<b style=" color:#ff0000">*</b>:</td>
         <td>
           <input type='text'  class='form-control explain' >
           <input type='hidden'  class='id'>
         </td>
      </tr>
      <tr>
         <td style='width:130px;text-align:right'>是否显示一段时间:</td>
         <td>
           <input type='checkbox'  class='datetype' >
         </td>
      </tr>
  </table>
</div>
<!--报表列配置框-->
<div id='coloumBox' style='padding:10px'>
  <table class="table table-bordered table-condensed tbHeader"
    style="margin:0px">
      <tr>
         <td style='width:100px;text-align:right'>列中文名称<b style=" color:#ff0000">*</b>:</td>
         <td>
           <input type='text' class='form-control cn_name' >
         </td>
      </tr>
      <tr>
         <td style='width:100px;text-align:right'>列英文名称(唯一标识)<b style=" color:#ff0000">*</b>:</td>
         <td>
           <input type='text' class='form-control name' >
         </td>
      </tr>
      <tr>
         <td style='width:100px;text-align:right'>列说明<b style=" color:#ff0000">*</b>:</td>
         <td>
           <input type='text'  class='form-control explain' >
         </td>
      </tr>
      <tr>
         <td style='width:100px;text-align:right'>计算表达式<b style=" color:#ff0000">*</b>:</td>
         <td>
           <textarea class='form-control expression' placeholder='$A/$B'></textarea>
           <input type='hidden' class='showpress'/>
         </td>
      </tr>
  </table>
</div>
<!--图表配置框-->
<div id='chartBox' style='padding:10px'>
  <table class="table table-bordered table-condensed tbHeader"
    style="margin:0px">
      <tr>
         <td style='width:100px;text-align:right'>图表标题<b style=" color:#ff0000">*</b>:</td>
         <td>
            <input type='text' name='chartTitle' style='width:274px; height:26px'/>  
         </td>
      </tr>
      <tr>
         <td style='width:100px;text-align:right'>图表类型<b style=" color:#ff0000">*</b>:</td>
         <td>
            <select class='select' name='chartType'>
              <option value='pie' selected='selected' >饼图</option>
              <option value='column'>柱状图</option>
              <option value='spline_time'>曲线图(时间趋势)</option>
              <option value='area'>区域图</option>
            </select>
         </td>
      </tr>
      <tr class='dimensionFilter' style='display:none' >
         <td style='width:100px;text-align:right'>维度筛选:</td>
         <td>
             <table class='table table-condensed table-bordered dimfilter'></table>
         </td>
      </tr>
      <tr>
         <td style='width:100px;text-align:right'>数据(值)<b style=" color:#ff0000">*</b>:</td>
         <td>
            <select class='select' multiple="multiple" name='chartData'>
            </select>
         </td>
      </tr>
      <tr class='compute'>
         <td style='width:100px;text-align:right'>计算数值:</td>
         <td>
            <input type='text' class='udc' placeholder='key=$A/$B,key1=$c/$d' style='width:274px; height:26px'/>
            <button class='btn btn-default btn-xs mapclick'>对应关系</button>
         </td>
      </tr>
  </table>
</div>
{/include file="report/data.tpl"/}