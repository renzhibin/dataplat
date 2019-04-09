<!--维度list模板 -->
<script id='dimensionstmpl' type='text/x-dot-template'>
  <table class='table table-condensed table-bordered'  style='margin:0px'>
    {{~it:item:key}} 
      <tr class='runList'  >
        {{ if(item.cn_name =='' ){  }}
          <td name='{{=item.name }}' style='width:20%' class='dimensions'
           {{  if(item.explain ==undefined){  }}
              title='无'
           {{ }else{ }}
              title='{{=item.explain}}'
           {{ } }}
           >{{=item.name }}</td>
        {{ }else{ }}
          <td name='{{=item.name }}' style='width:20%' class='dimensions'
           {{  if(item.explain ==undefined){  }}
              title='无'
           {{ }else{ }}
              title='{{=item.explain}}'
           {{ } }}
           >{{=item.cn_name }}</td>
        {{ } }}
        <td style='width:35%'>
          <select name='filter' class='select'>
            <option value='filter_not' selected = selected>----</option>
            <option value='='>=</option>
            <option value='like'>like</option>
            <option value='not like'>not like</option>
            <option value='start with'>start with </option>
            <option value='end with'>end with </option>
            <option value='in'>in</option>
            <option value='not in'>not in</option>
            <option value='>='> >= </option>
            <option value='<='> <= </option>
            <option value='<'> < </option>
            <option value='>'> > </option>
            <option value='!='> != </option>
            <option value='REGEXP'>正则</option>
          </select>
        </td>
        <td contenteditable="true" style='-webkit-box-shadow:1px 1px 7px #d9edf7;width:35%'></td>
        <td style='text-align:center'>
          <input type='checkbox' class='checkInfo' dimensions='{{=item.name }}' 
           dim = {{=JSON.stringify(item.dim) }} />
        </td>
      </tr>
    {{~}}  
  </table>
</script>
<!--指标list模板-->
<script id='metricstmpl' type='text/x-dot-template'>
  {{~it.categories:categorie:cid}} 
    <h4 style='text-align:center'>{{=categorie.cn_name}}</h4>
    <table class='table table-condensed table-bordered' style='margin:0px'>
      {{~categorie.groups :group:gid}} 
      <tr class='runList'>
        <td name='{{=group.name }}' style='text-align:right;width:10%' valign="middle" >{{=group.cn_name}}</td>
        <td >
          {{ if ( group.metrics.length >0 ){ }}
            <ul class='list-group' style='margin:0px'>
            {{~group.metrics :metric:mid}} 
              <li class='list-group-item'  style='padding:5px'
              name='{{=categorie.name}}.{{=group.name}}.{{=metric.name }}'>
                <input type='checkbox' class='metric_explain'
                pseudo_code ='{{=metric.pseudo_code}}'
               explain='{{=metric.explain}}' cn_name='{{=metric.cn_name }}'/>
                {{ if(metric.cn_name =='' ){  }}
                  &nbsp;&nbsp;{{=metric.name }}&nbsp;&nbsp;&nbsp;&nbsp;
                {{ }else{ }}
                  &nbsp;&nbsp;{{=metric.cn_name }}&nbsp;&nbsp;&nbsp;&nbsp;
                {{ } }}
                <select name='metric_filter' style='display:inline-block;width:80px' class='select'>
                  <option value='filter_not' selected = selected>----</option>
                  <option value='='>=</option>
                  <option value='like'>like</option>
                  <option value='not like'>not like</option>
                  <option value='start with'>start with </option>
                  <option value='end with'>end with </option>
                  <option value='in'>in</option>
                  <option value='not in'>not in</option>
                  <option value='>='> >= </option>
                  <option value='<='> <= </option>
                  <option value='<'> < </option>
                  <option value='>'> > </option>
                  <option value='!='> != </option>
                  <option value='REGEXP'>正则</option>
                </select>
                <input type='text' style='width:80px' class='metric_filter_value' />
              </li>
            {{~}} 
            </ul>
          {{ } }}
        </td>
      </tr>
      {{~}}  
    </table>
  {{~}}
</script>
<!--生成reportgrade表格-->
<script id='gradetmpl' type='text/x-dot-template'> 
  <table class='table table-bordered table-condensed' style='margin:0px'>
    <tr  class='table_header'>
      <td style='width:4%'>类型</td>
      <td style='width:10%'>列显示名称</td>
      <td style='width:8%'>列Key</td>
      <td style='width:13%'>列说明</td>
      <td style='width:10%'>数据过滤</td>
      <td style='width:12%'>计算值</td>
      <td style='width:5%'>百分比</td>
      <td style='width:10%'>搜索</td>
      <td style='width:6%'>即时过滤</td>
      <td style='width:6%'>外链</td>
      <td style='width:7%'>是否固定
      <br>
        <input type='checkbox' class='selectFixed'/><span class='fixedtext'>全选</span>
      </td>
      <td style="width:4%">默认排序</td>
      <td style='width:6%'>
        <span>隐藏</span><br>
        <input type='checkbox' class='selectHide'/><span class='hidetext'>全选</span>
      </td>
    </tr>
    <tbody id='groupid' class='gradebox' style='border-bottom:12px solid #939da8;'>
      {{~it:coloum:item}} 
        {{ if ( coloum.isgroup == 1 ){ }}
          <tr {{ if(coloum.ishide == 1){  }} class='disable'{{ } }} index="{{=item}}">
            <td>维度</td>
            <td class='reportname'>{{=coloum.name}} </td>
            <td class='reportkey'>
              <b style='display:none' id="clipContent_{{=item}}">{{=coloum.key}}</b>
              <!-- <small class="clipBtn" id="clipBtn_{{=item}}" title="{{=coloum.key}}" data-clipboard-target="clipContent_{{=item}}" data="{{=item}}">复制</small> -->
              <small data-clipboard-text="{{=coloum.key}}" class="clipBtn" id="clipBtn_{{=item}}" title="{{=coloum.key}}" data-clipboard-target-old="clipContent_{{=item}}" data="{{=item}}">复制</small>
              <a data-toggle="tooltip" title='{{=coloum.key}}' class="showinfo glyphicon glyphicon-question-sign"></a>
            </td>
            <td class='reportexplain'>
                {{if(coloum.explain.length > 0 && coloum.explain.length >6){ }}
                    <span> {{=coloum.explain}}</span>
                    <a data-toggle="tooltip" title='{{=coloum.explain}}' class="showinfo glyphicon glyphicon-question-sign"></a>
                {{ } else { }}
                  <span> {{=coloum.explain}}</span>
                {{ } }}
           </td>
           {{if(coloum.key=='date'){ }}
           <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
           {{ }else{  }}
            <td class='reportf'>
                  <select name='op' style='width:60px;display:inline-block'>
                    <option value='filter_not' selected = selected>----</option>
                    <option value='='
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == '='){ }} selected='selected'{{ } }}
                    {{ } }}
                    >=</option>
                    <option value='like'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'like'){ }} selected='selected'{{ } }}
                    {{ } }}
                    >like</option>
                    <option value='not like'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'not like'){ }} selected='selected'{{ } }}
                    {{ } }}
                    >not like</option>
                    <option value='start with'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'start with'){ }} selected='selected'{{ } }}
                    {{ } }}
                    >start with </option>
                    <option value='end with'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'end with'){ }} selected='selected'{{ } }}
                    {{ } }}
                    >end with </option>
                    <option value='in'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'in'){ }} selected='selected'{{ } }}
                    {{ } }}
                    >in</option>
                    <option value='not in'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'not in'){ }} selected='selected'{{ } }}
                    {{ } }}
                    >not in</option>
                    <option value='>='
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == '>='){ }} selected='selected'{{ } }}
                    {{ } }}
                    > >= </option>
                    <option value='<='
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == '<='){ }} selected='selected'{{ } }}
                    {{ } }}
                    > <= </option>
                    <option value='<'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == '<'){ }} selected='selected'{{ } }}
                    {{ } }}
                    > < </option>
                    <option value='>'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == '>'){ }} selected='selected'{{ } }}
                    {{ } }}  
                    > > </option>
                    <option value='!='
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == '!='){ }} selected='selected'{{ } }}
                    {{ } }}
                    > != </option>
                    <option value='REGEXP'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'REGEXP'){ }} selected='selected'{{ } }}
                    {{ } }}
                    > 正则</option>
                  </select>
                  <input type='text' class='op_val'
                  {{ if(coloum.filter !=undefined){ }} value='{{=coloum.filter.val.join("?")}}' {{ } }}
                   style='width:80px' />       
            </td>
            <td class='reportexpression'>-</td>
            <td class='reportpercent'>-</td>
            <td>
                <input type='checkbox'
                {{ if(coloum.search != undefined ){  }} 
                checked ='checked'
                {{ } }}
                class='isfilter'/>
                {{ if(coloum.type != 2 ){  }} 
                 <span class='accurate_box'
                    {{ if(coloum.search != undefined ){  }} 
                      style='display:inline-block'
                    {{ }else{ }} 
                      style='display:none'
                    {{ } }}
                   >
                   <b class='btn btn-xs
                   {{  if( coloum.search !=undefined && coloum.search.is_accurate  ==1){ }}
                      btn-primary
                    {{ }else{ }} btn-default{{ } }} accurate'>精确匹配</b>
                 </span>
              {{ } }}          
            </td>
            <td>
              /*{{ if(coloum.type == 2 ){  }} 
               -
              {{ }else{  }}*/
                <button class='btn btn-default btn-xs reportsearch'
                 {{ if( coloum.search != undefined ){  }}
                    data-config='{{=JSON.stringify(coloum.search) }}'
                 {{ }else{ }}
                    disabled='disabled' 
                 {{ } }}
                 >设置</button>
             /* {{ } }} */
            </td>
            <td class="linkbox"><input type="text" value="{{if(coloum.otherlink){ }}{{=coloum.otherlink}}{{ } }}" class="otherlink" placeholder="a/b?c=${c}&d=${d}" /></td>
            {{ } }}
            
            <td class="fixed"><input type="checkbox" name="isfixed" class="isfixed" {{if(coloum.fiexd) {}} checked='checked' {{ } }} /> </td>
            <td class="orderbyarr">
              {{ if(coloum.type == 2 ){  }} 
                -
              {{ }else{  }}             
                <input type="checkbox" {{ if(coloum.orderbyarr){  }}checked='checked'{{ } }} class="coloumOrder"/>
              {{ } }}</td>
            <td class='operate'> 
              {{ if(coloum.type == 2 ){  }} 
                -
              {{ }else{  }}             
                <input type="checkbox" {{ if(coloum.ishide == 1){  }}checked='checked'{{ } }} class="coloumOperate"/>
              {{ } }}
            </td>
          </tr>
        {{ } }}
      {{~}}
    </tbdoy>
    <tbody id='tableid' class='gradebox'>
    {{~it:coloum:item}} 
      {{ if ( coloum.isgroup  != 1 ){ }}
        {{ if ( coloum.udctype == 'udc' ){ }}
          <tr {{ if(coloum.ishide == 1){  }} class='disable'{{ } }} index="{{=item}}">
            <td>指标</td>
            <td class='reportname'>
              <textarea>{{=coloum.cn_name}}</textarea>
              <!--<input style='width:100%' type='text' value='{{=coloum.cn_name}}'/>-->
            </td>
            <td class='reportkey'>
              <textarea>{{=coloum.name}}</textarea>
              <!--<input style='width:100%' type='text' value='{{=coloum.name}}'/>-->
            </td>
            <td class='reportexplain'>
              <textarea>{{=coloum.explain}}</textarea>
            <!--<input style='width:100%' type='text' value='{{=coloum.explain}}'/>-->
            </td>
            <td class='reportf'>
                   <select name='op' style='width:60px;display:inline-block'>
                    <option value='filter_not' selected = selected>----</option>
                    <option value='='
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == '='){ }} selected='selected'{{ } }}
                    {{ } }}
                    >=</option>
                    <option value='like'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'like'){ }} selected='selected'{{ } }}
                    {{ } }}
                    >like</option>
                    <option value='not like'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'not like'){ }} selected='selected'{{ } }}
                    {{ } }}
                    >not like</option>
                    <option value='start with'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'start with'){ }} selected='selected'{{ } }}
                    {{ } }}
                    >start with </option>
                    <option value='end with'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'end with'){ }} selected='selected'{{ } }}
                    {{ } }}
                    >end with </option>
                    <option value='in'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'in'){ }} selected='selected'{{ } }}
                    {{ } }}
                    >in</option>
                    <option value='not in'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'not in'){ }} selected='selected'{{ } }}
                    {{ } }}
                    >not in</option>
                    <option value='>='
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == '>='){ }} selected='selected'{{ } }}
                    {{ } }}
                    > >= </option>
                    <option value='<='
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == '<='){ }} selected='selected'{{ } }}
                    {{ } }}
                    > <= </option>
                    <option value='<'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == '<'){ }} selected='selected'{{ } }}
                    {{ } }}
                    > < </option>
                    <option value='>'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == '>'){ }} selected='selected'{{ } }}
                    {{ } }}  
                    > > </option>
                    <option value='!='
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == '!='){ }} selected='selected'{{ } }}
                    {{ } }}
                    > != </option>
                    <option value='REGEXP'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'REGEXP'){ }} selected='selected'{{ } }}
                    {{ } }}
                    > 正则</option>
                  </select>
                  <input type='text' class='op_val'
                  {{ if(coloum.filter !=undefined){ }} value='{{=coloum.filter.val.join("?")}}' {{ } }}
                   style='width:80px' />     
            </td>
            <td class='reportexpression'>
              <textarea style='width:100%' style='width:120px;'>{{=coloum.expression}}</textarea>
            </td>
            <td class='reportpercent'>    
                 <input type='checkbox' 
                 {{ if(coloum.percent != undefined){  }} checked ='checked'{{ } }}
                  class='ispercent' />
            </td>
            <td>
                {{ if(coloum.type == 2 ){  }} 
                    -
                {{ }else{  }}
                  <input type='checkbox'
                  {{ if(coloum.search != undefined ){  }} 
                  checked ='checked'
                  {{ } }}
                  class='isfilter'/>   
                 <span class='accurate_box'
                    {{ if(coloum.search != undefined ){  }} 
                      style='display:inline-block'
                    {{ }else{ }} 
                      style='display:none'
                    {{ } }}
                   >
                    <b class='btn btn-xs
                   {{  if(  coloum.search !=undefined && coloum.search.is_accurate  ==1){ }}
                     btn-primary
                    {{ }else{ }} btn-default{{ } }} accurate'>精确匹配</b>
                 </span>
                {{ } }}  
            <td>
                {{ if(coloum.type == 2 ){  }} 
                 -
                {{ }else{  }}
                  <button class='btn btn-default btn-xs reportsearch'
                   {{ if( coloum.search != undefined ){  }}
                      data-config='{{=JSON.stringify(coloum.search) }}'
                   {{ }else{ }}
                      disabled='disabled' 
                   {{ } }}
                   >设置</button>
                {{ } }} 
            </td>
            <td class="linkbox"><input type="text" value="{{if(coloum.otherlink){ }}{{=coloum.otherlink}}{{ } }}" class="otherlink" placeholder="a/b?c=${c}&d=${d}" /></td>
            <td class="fixed"><input type="checkbox" name="isfixed" class="isfixed" {{if(coloum.fiexd) {}} checked='checked' {{ } }} /> </td>
            <td class="orderbyarr">
              {{ if(coloum.type == 2 ){  }} 
                -
              {{ }else{  }}             
                <input type="checkbox" {{ if(coloum.orderbyarr){  }}checked='checked'{{ } }} class="coloumOrder"/>
              {{ } }}</td>
            <td  class='operate'>
              <input type='checkbox' 
              style='margin-bottom:5px'
              class='coloumOperate' {{ if(coloum.ishide == 1){  }}checked='checked'{{ } }} >
              <br><a class='coloumdel btn btn-default btn-xs'>删除</a></td>
            </td>
          </tr>
        {{ }else{ }}
          <tr  {{ if(coloum.ishide == 1){  }} class='disable'{{ } }} index="{{=item}}">
            <td>指标</td>
            <td class='reportname'>{{=coloum.name}} </td>
            <td class='reportkey'>
                <b style='width:100%;height:100%;display:none' id="clipContent_{{=item}}">{{=coloum.key}}</b>
             <!-- <small class="clipBtn" id="clipBtn_{{=item}}" title="{{=coloum.key}}" data-clipboard-target="clipContent_{{=item}}" data="{{=item}}">复制</small> -->
             <small data-clipboard-text="{{=coloum.key}}" class="clipBtn" id="clipBtn_{{=item}}" title="{{=coloum.key}}" data-clipboard-target-old="clipContent_{{=item}}" data="{{=item}}">复制</small>
              <a data-toggle="tooltip" title='{{=coloum.key}}' class="showinfo glyphicon glyphicon-question-sign"></a>
            </td>
            <td class='reportexplain'>
              {{if(coloum.explain.length > 0 && coloum.explain.length >6){ }}
                    <span> {{=coloum.explain}}</span>
                    <a data-toggle="tooltip" title='{{=coloum.explain}}' class="showinfo glyphicon glyphicon-question-sign"></a>
                {{ } else { }}
                  <span> {{=coloum.explain}}</span>
                {{ } }}</td>
            <td class='reportf'>
              <select name='op' style='width:60px;display:inline-block'>
                <option value='filter_not' selected = selected>----</option>
                <option value='='
                {{ if(coloum.filter !=undefined){ }}
                  {{if(coloum.filter.op == '='){ }} selected='selected'{{ } }}
                {{ } }}
                >=</option>
                <option value='like'
                {{ if(coloum.filter !=undefined){ }}
                  {{if(coloum.filter.op == 'like'){ }} selected='selected'{{ } }}
                {{ } }}
                >like</option>
                <option value='not like'
                {{ if(coloum.filter !=undefined){ }}
                  {{if(coloum.filter.op == 'not like'){ }} selected='selected'{{ } }}
                {{ } }}
                >not like</option>
                <option value='start with'
                {{ if(coloum.filter !=undefined){ }}
                  {{if(coloum.filter.op == 'start with'){ }} selected='selected'{{ } }}
                {{ } }}
                >start with </option>
                <option value='end with'
                {{ if(coloum.filter !=undefined){ }}
                  {{if(coloum.filter.op == 'end with'){ }} selected='selected'{{ } }}
                {{ } }}
                >end with </option>
                <option value='in'
                {{ if(coloum.filter !=undefined){ }}
                  {{if(coloum.filter.op == 'in'){ }} selected='selected'{{ } }}
                {{ } }}
                >in</option>
                <option value='not in'
                {{ if(coloum.filter !=undefined){ }}
                  {{if(coloum.filter.op == 'not in'){ }} selected='selected'{{ } }}
                {{ } }}
                >not in</option>
                <option value='>='
                {{ if(coloum.filter !=undefined){ }}
                  {{if(coloum.filter.op == '>='){ }} selected='selected'{{ } }}
                {{ } }}
                > >= </option>
                <option value='<='
                {{ if(coloum.filter !=undefined){ }}
                  {{if(coloum.filter.op == '<='){ }} selected='selected'{{ } }}
                {{ } }}
                > <= </option>
                <option value='<'
                {{ if(coloum.filter !=undefined){ }}
                  {{if(coloum.filter.op == '<'){ }} selected='selected'{{ } }}
                {{ } }}
                > < </option>
                <option value='>'
                {{ if(coloum.filter !=undefined){ }}
                  {{if(coloum.filter.op == '>'){ }} selected='selected'{{ } }}
                {{ } }}  
                > > </option>
                <option value='!='
                {{ if(coloum.filter !=undefined){ }}
                  {{if(coloum.filter.op == '!='){ }} selected='selected'{{ } }}
                {{ } }}
                > != </option>
                 <option value='REGEXP'
                    {{ if(coloum.filter !=undefined){ }}
                      {{if(coloum.filter.op == 'REGEXP'){ }} selected='selected'{{ } }}
                    {{ } }}
                    > 正则</option>
              </select>
              <input type='text' class='op_val'
              {{ if(coloum.filter !=undefined){ }} value='{{=coloum.filter.val.join("?")}}' {{ } }}
               style='width:80px' />
            </td>
            <td class='reportexpression'></td>
            <td class='reportpercent'>
              /*{{ if(coloum.type == 2 ){  }} 
                      -
              {{ }else{  }}*/
                   {{ if( coloum.isgroup != 1 ){  }}
                   <input type='checkbox' 
                    {{ if(coloum.percent != undefined){  }} checked ='checked'{{ } }}
                    class='ispercent' />
                    {{ }else{ }}
                      -
                   {{ } }}
              /*{{ } }} */    
            </td>
            <td>
                {{ if(coloum.type == 2 ){  }} 
                    -
                {{ }else{  }}
                  <input type='checkbox'
                  {{ if(coloum.search != undefined ){  }} 
                  checked ='checked'
                  {{ } }}
                  class='isfilter'/>  
                  <span class='accurate_box'
                    {{ if(coloum.search != undefined ){  }} 
                      style='display:inline-block'
                    {{ }else{ }} 
                      style='display:none'
                    {{ } }}
                   >
                    <b class='btn btn-xs
                   {{  if(  coloum.search !=undefined && coloum.search.is_accurate  ==1){ }}
                      btn-primary
                    {{ }else{ }} btn-default{{ } }} accurate'>精确匹配</b>
                 </span>
                {{ } }}            
             </td>
            <td>
                {{ if(coloum.type == 2 ){  }} 
                 -
                {{ }else{  }}
                  <button class='btn btn-default btn-xs reportsearch'
                   {{ if( coloum.search != undefined ){  }}
                      data-config='{{=JSON.stringify(coloum.search) }}'
                   {{ }else{ }}
                      disabled='disabled' 
                   {{ } }}
                   >设置</button>
                {{ } }} 
               
            </td>
            <td class="linkbox"><input type="text" value="{{if(coloum.otherlink){ }}{{=coloum.otherlink}}{{ } }}" class="otherlink" placeholder="a/b?c=${c}&d=${d}"/></td>
            <td class="fixed"><input type="checkbox" name="isfixed" class="isfixed" {{if(coloum.fiexd) {}} checked='checked' {{ } }} /> </td>
            <td class="orderbyarr">
              {{ if(coloum.type == 2 ){  }} 
                -
              {{ }else{  }}             
                <input type="checkbox" {{ if(coloum.orderbyarr){  }}checked='checked'{{ } }} class="coloumOrder"/>
              {{ } }}</td>
            <td class='operate'>
              <input type='checkbox' class='coloumOperate' {{ if(coloum.ishide ==1){  }}checked='checked'{{ } }} >
            </td>
          </tr>
        {{ } }}
      {{ } }}
    {{~}}
    </tbdoy>
  </table>
  <div class="ispagesizebox" style="padding:10px; margin:10px 0; border:1px solid #ddd">
      <span>设置默认页码</span>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" value="" name="ispagesize" class="ispagesize" />
      <div class="pagesizebox" style="display:none">设置每页显示条数
      <input class="easyui-numberspinner pagesize" name="pagesize" value="10" data-options="min:10,increment:5,max:100" style="width:50px;" />
      </div>
  </div>
  <div class="new_proportion" style="display:none;line-height:32px; padding:0 10px;margin:10px 0; border:1px solid #ddd">
  <span style='color:red'>&nbsp;&nbsp;慎用&nbsp;&nbsp;</span>用户自定义列 是否相对占比&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" value="" name="isproportion" class="isproportion" /> </div>
  <button class='btn btn-primary btn-xs addColoum'>增加一行</button>
  <div>
    <p class='tipinfoother' style='margin:5px'>相对占比：用户自定义列的值根据页面搜索条件动态生成</p>
   <p class='tipinfoother' style='margin:5px'>百分比：只会在数据上加上百分号(%)，不会乘以100 比如一个数据为0.01 显示的报表为0.01% </p>
   <p class='tipinfoother' style='margin:5px'>排序：请在表格第一列进行拖拽排序 </p>
   <p class='tipinfoother' style='margin:5px'>类型：类型分为维度和指标，排序只支持相同类型列排序，不同类型之间不可排序 </p>
  </div>
</script>
<!--数据源模板-->
<script id='dataSourcetpl' type='text/x-dot-template'>
   <!--<div class="panel panel-info" style='margin:5px 0;'>
        <div class="panel-heading">报表类型</div>
        <div class="panel-body">
            <label class="radio-inline" for="report_type1">
                <input type="radio" name="report_type" id="report_type1" value="1">普通报表
            </label>
            <label class="radio-inline" for="report_type2">
                <input type="radio" name="report_type" id="report_type2" value="2">对比报表
            </label>
        </div>
    </div>-->
   <!-- 数据源模式 -->
  <div class="datasourcebox">
  <div class="panel panel-info" style='margin-bottom:5px;'>
    <div class="panel-heading">
        <span>维度</span> 
        <div class='pull-right'>
          <!--<a class='selectAll btn btn-default btn-xs  btn-special'>全选</a>-->
          <a class='clearAll btn btn-default btn-xs  btn-special'>清空</a>
        </div>
        <div class='clearfix'></div>
    </div>
    <div class="panel-body">
      <ul  class='list-group groupUl' style='margin:0px; padding:0px'>
        {{~it.group:item:key}}
          <li class='list-group-item' style='padding: 5px; margin: 3px; width:160px; background-color: none; float: left; '>
              <input type='checkbox' class='grouplist'
               explain='{{=item.explain}}'
               dimensions='{{=item.name }}' 
               dim={{=JSON.stringify(item.dim) }} />{{=item.cn_name}}  
          </li>
        {{~}} 
        <div class='clearfix'></div> 
      </ul>
    </div>
  </div>
  <div class="panel panel-info">
    <div class="panel-heading">
      <span>可选指标</span>
      <div class="pull-right"><a class="groupCheckAll btn btn-default btn-xs btn-special" data-status="clear" style="line-height:20px;">全选</a></div>
    </div>
    <div class="panel-body">
      <ul  class='list-group metricUl' style='margin:0px;  padding:0px;display:none'> 
        {{~it.metric.categories:categorie:cid}} 
          <li class="show">
          <h5>{{=categorie.cn_name}}</h5>
            {{~categorie.groups :group:gid}} 
                {{if(group.metrics.length >0 ){ }}
                <p class="show"><span class="col-sm-8"><a href="/project/cubeeidtor?project={{=it.project.name}}&id={{=it.project.id}}&groupname={{=group.name}}" target="_blank">{{=group.cn_name}}</a>:</span>
                    <label class="col-sm-12">
                  {{~group.metrics :metric:mid}} 
                    <a class='list-group-item metriclist show' 
                     explain='{{=metric.explain }}' style='padding:5px; margin:3px;float: left;'
                    name='{{=categorie.name}}.{{=group.name}}.{{=metric.name }}'>{{=metric.cn_name}}</a>
                  {{~}} 
                   </label>
                  </p>
                {{ } }}
            {{~}} 
            </li> 
        {{~}}
        <div class='clearfix'></div>  
      </ul>
      <button class='btn btn-primary btn-xs saveSource'>选择</button> <label class="error" id="metric_error"> 提示：维度和可选指标为必选项</label>
    </div>
  </div>
      </div>
      <!-- 自定义报模式 -->
      <div class="custombox">
          <div class="panel panel-info">
              <!-- <div class="panel-heading">sql  <lable style="color:#ff0000;margin-left:3px">(提示：不建议使用select * from)</lable></div>-->
              <div class="panel-heading" onclick="$('.custombox #customerSuggest').toggle();">SQL<lable style="color:#ff0000;margin-left: 10px">点击查看SQL书写建议</lable></div>
              <div class="panel-heading" id="customerSuggest" style="font-size: 14px; color: red; display: none;">
                  <span>1. 禁止使用 select * from table，另 SQL 必须使用小写</span><br>
                  <span>2. 维度无需使用别名，指标必须使用 as 添加别名</span><br>
                  <span>3. 别名不能使用单引号包裹，别名不能使用中文</span><br>
                  <span>4. 除 from 关键字外，禁止出现 from 以及 from 的组合</span>
              </div>
              <div class="panel-body">
                  <div class="editcode"></div>
                  <div class="customsql_startbox" style="margin-top:10px;padding-left:15px;">起始时间：<input type="text" value="" class="customsql_datepicker customsql_start" />
                      <label class="error customsql_starterror" id="customsql_starterror">&nbsp;&nbsp; 提示：起始时间不能为空</label>
                  </div>
                  <div class="buttonbox">
                      <button class='btn btn-primary btn-xs checksql'>校验sql</button><label class="error sql_error" id="sql_error"> 提示：sql不能为空</label>
                  </div>
              </div>
          </div>
      </div>
  </div>
</script>
<!--对比模板-->
<script id="contrasttmpl" type='text/x-dot-template'>
  <table class='table table-bordered table-condensed' style='margin:0px'>
    <tr  class='table_header'>
      <td>显示</td>
      <td>中文名称</td>
      <td>绑定的key</td>
      <!--<td>千位分隔符</td>
      <td>高亮变化率</td>-->
    </tr>
    <tbody  class='contrasttr'>
     {{~it:item:key}}
     <tr>
      <td><input type='checkbox' class='isshow'  
      {{ if(item.isshow ==1){ }} checked='checked' {{ } }}/></td>
      <td class='contrastname'>{{=item.name}}</td>
      <td class='contrastkey'>{{=item.key}}</td>
      <!-- <td><input class='contrastformat' type='checkbox'/></td>
      <td>
      <input type='checkbox' class='minus'/>负值:<input type='text' class='minusval' style='width:40px'/>
      <input type='checkbox' class='plus'/>正值:<input type='text' class='plusval' style='width:40px'/>
      </td>-->
     </tr>
     {{~}}
    </tbdoy>
  </table>
</script>