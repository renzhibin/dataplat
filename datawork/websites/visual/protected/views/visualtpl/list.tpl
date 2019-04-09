<!--展示报表模板-->
<script id='datagridtmpl' type='text/x-dot-template'>
  <table id ='dashboard' data-options="rownumbers:true,
          singleSelect:true,
          collapsible:false,
          method:'post',
          height:'auto',
          multiSort:false,
          singleSelect: true,
          loadMsg:'数据正在加载...',
          autoRowHeight:true,
          pageSize:10,
          pagination:true,
          url:'{{=it.url}}',
          onLoadSuccess:onLoadSuccess">
     <thead data-options="frozen:true">
        <tr>
            {{~it.fiexd :fiexd:fkey}}
              <th data-options="field:'{{=fiexd.name}}'
                {{ if ( fiexd.name =='mgj_item_id' ){  }}
                    ,width:180,
                {{ }else{  }}
                    ,width:120,
                {{ } }}
              sortable:true">
                {{ if(fiexd.cn_name ==''){ }}
                   {{=fiexd.name}}
                    {{if(fiexd.explain !='无' && fiexd.explain !='' && fiexd.explain!=fiexd.name ){ }}
                      <a data-toggle="tooltip" title='{{=fiexd.explain}}' class="showinfo glyphicon glyphicon-question-sign"></a>
                      {{ } }}

                {{ }else{ }}
                   {{=fiexd.cn_name}}
                    {{if(fiexd.explain !='无' && fiexd.explain !='' && fiexd.explain!=fiexd.cn_name){ }}
                      <a data-toggle="tooltip" title='{{=fiexd.explain}}' class="showinfo glyphicon glyphicon-question-sign"></a>
                    {{ } }}

                {{ } }}

              </th>
            {{~}}

        </tr>
    </thead>
    <thead>
        <tr>
            {{~it.header :header:hkey}}
              <th data-options="field:'{{=header.name}}'
                {{ if ( header.name =='mgj_item_id' ){  }}
                    ,width:180,
                {{ }else{  }}
                    ,width:130,
                {{ } }}
                sortable:true{{if(header.percent == 1 ){ }},formatter:formatPrice{{ } }}" >
              {{if(header.isudc=="1"||header.isudc=="true"){ }}
              <span  style="font-style: italic"> {{=header.cn_name}}(相对占比)</span>
              {{ }else{ }}
              {{=header.cn_name}}
              {{ } }}
              {{if(header.explain !='无' && header.explain !='' && header.explain !=header.cn_name ){ }}
                <a data-toggle="tooltip" title='{{=header.explain}} {{ if(header.isudc=="1"||header.isudc=="true"){ }}<br/>该结果根据动态结果筛选而成{{ } }}' class="showinfo glyphicon glyphicon-question-sign"></a>
              {{ } }}
              </th>
            {{~}}
        </tr>
    </thead>
  </table>
</script>
<script id="contrastTable" type='text/x-dot-template'>
  <table class='table table-bordered table-condensed' style='margin:0px'>
    <tr class='table_header'>
      <td>指标名称</td>
      {{~it.header:item:key}}
      <td>{{=item.name}}
      {{ if(item.date){ }}
        （{{=item.date}}）
      {{ } }}
      </td>
      {{~}}
    </tr>
    <tbody class='contrasttr'>

     {{~it.data:data:key}}
     <tr>
      <td>{{=data.name}}</td>
      {{~it.header:htem:hkey}}
          {{ if(htem.key.indexOf('percent')>= 0 ){  }}
              {{ if(data[htem.key] !='不存在'){ }}
                   {{ if(data[htem.key] > 0){ }}
                      <td style='color:red'>{{=data[htem.key] }}%</td>
                    {{  }else if( data[htem.key] ==0 ){  }}
                      <td>{{=data[htem.key]}}%</td>
                   {{ }else{ }}
                      <td style='color:green'>{{=data[htem.key]}}%</td>
                   {{ } }}
              {{ }else{ }}
                  <td>{{=data[htem.key]}}</td>
              {{ } }}
          {{ }else{ }}
            <td>{{=data[htem.key]}}</td>
          {{ } }}
      {{~}}
     </tr>
     {{~}}
    </tbdoy>
  </table>
</script>
