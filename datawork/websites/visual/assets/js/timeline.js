$(function(){
  //加载数据
  var interText = doT.template($("#eventtpl").text());
  if(isEmptyObject(eventData)){
    eventData.timeline ={};
    eventData.timeline.date =[];
  }
  $("#eventBox").html(interText(eventData.timeline.date)); 

  $('body').on('click','.addEvent',function(){
    var coloumHtml ="<tr>";
    coloumHtml +="<td style='width:10%'><input name='startDate'  type='date'/> </td>";
    coloumHtml +="<td  style='width:10%'><input name='endDate'  type='date'/> </td>";
    coloumHtml +="<td ><input style='width:100%' type='text'/></td>";
    coloumHtml +="<td style='width:30%' ><textarea style='width:200px'></textarea></td>";
    coloumHtml +="<td><button class='btn btn-default btn-xs  eventdel'>删除</button</td>";
    coloumHtml +="</tr>";
    $('#groupid').append(coloumHtml);
    //$('#groupid').find('.datepicker').datepicker({format: 'yyyy-mm-dd'});
  });
  $('body').on('click','.eventdel',function(){
    $(this).parent().parent().remove();
  }); 

  $('body').on('blur','#event_name',function(){
      //验证名称是否重复

      var event_name = $.trim($('#event_name').val());
      var event_id = $.trim($('#event_id').val());
      var obj = $(this);
      if(event_name ==''){
          $('.error_box').show().text('请填写时间线名称');
          return;
      }else{
          $.ajax({ 
            type: "POST", 
            url: "/addition/checkname", 
            async:false, 
            data: {
              'event_id':event_id,
              'event_name':event_name
            },
            dataType:"json",
            success: function(data){ 
              if(data.status !=0){
                $('.error_box').show().text(data.msg);
                obj.focus();
              }else{
                $('.error_box').show().text('');
              } 
            } 
          });
          return;
      }
      
      
  });
  //保存  
  $('.saveInfo').click(function(){
      var  eventInfo ={};
      eventInfo.event_name = $.trim($('#event_name').val());
      eventInfo.event_id = $.trim($('#event_id').val());
      eventInfo.data =[];
      $('#groupid').find('tr').each(function(){
          var one ={};
          one.startDate = $(this).find('td').eq(0).find('input').val();
          one.endDate = $(this).find('td').eq(1).find('input').val();
          one.headline = $(this).find('td').eq(2).find('input').val();
          one.text = $.trim($(this).find('td').eq(3).find('textarea').val());
          eventInfo.data.push(one);
      });
      //数据验证
      if(event_name ==''){
          $.messager.alert('提示','请填写时间线名称','info');
          return;
      }
      if( $.trim($('.error_box').text())  !=''){
        return false;
      }
      for(var i=0; i<eventInfo.data.length; i++){
        if(eventInfo.data[i].startDate ==''){
          $.messager.alert('提示','请设置开始时间','info');
          return;
        }
        if(eventInfo.data[i].endDate ==''){
          $.messager.alert('提示','请设置结束时间','info');
          return;
        }
        if(eventInfo.data[i].headline ==''){
          $.messager.alert('提示','请设置标题','info');
          return;
        }
      }
      $.post('/addition/SaveTimeline', {
        'eventInfo':JSON.stringify(eventInfo)
      },function(data){
          if(data.status ==0){
               $.messager.alert('提示',data.msg,'info');
               if(eventInfo.event_id >0){
                  window.location.href = '/Addition/showtimeline?event_id='+eventInfo.event_id;
               }else{
                  window.location.href = '/Addition/showtimeline?event_id='+data.data;
               }
          }else{
              $.messager.alert('提示',data.msg,'info');
          }
      }, 'json');
      });
});