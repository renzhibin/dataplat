$.extend($.fn,{
  mask: function(msg,maskDivClass){
      this.unmask();
      // 参数
      var op = {
          opacity: 0.8,
          z: 10000,
          bgcolor: '#e2edfb'
      };
      var original=$(document.body);
      var position={top:0,left:0};
          if(this[0] && this[0]!==window.document){
              original=this;
              position=original.position();
          }
      // 创建一个 Mask 层，追加到对象中
      var maskDiv=$('<div class="maskdivgen">&nbsp;</div>');
      maskDiv.appendTo(original);
      var maskWidth=$(window).width();
      if(!maskWidth){
          maskWidth=$(window).width();
      }
      var maskHeight= $(window).height() + $(document).scrollTop();
      if(!maskHeight){
          maskHeight=$(window).height()  + $(document).scrollTop();
      }
      $('body').css({
      	 'overflow':'hidden'
      });
      
      maskDiv.css({
          position: 'absolute',
          top: position.top,
          left: position.left,
          'z-index': op.z,
       		width: maskWidth,
          height:maskHeight,
          'background-color': op.bgcolor,
          opacity: 0.5
      });
      if(maskDivClass){
          maskDiv.addClass(maskDivClass);
      }
      if(msg){
          var htmlstr ='<div style="position:absolute;background:#e2edfb">';
            htmlstr  +='<div style="line-height:26px;font-size:14px;opacity:0.5;color:#000;background:#e2edfb;padding:2px 10px 2px 10px">';
            htmlstr  +='<span><img src="/assets/img/loading.gif" width="16px"  height="16px"/></span>';
            htmlstr  +='<span>'+msg+'</span>';
            htmlstr  +='</div>';
            htmlstr  +='</div>';
          var msgDiv=$(htmlstr);
          msgDiv.appendTo(maskDiv);
          var widthspace=(maskDiv.width()-msgDiv.width());
          var heightspace=(maskDiv.height()-msgDiv.height());
          msgDiv.css({
            cursor:'wait',
            top:(heightspace/2-2),
            left:(widthspace/2-2)
          });
        }
        maskDiv.fadeIn('fast', function(){
          // 淡入淡出效果
          $(this).fadeTo('slow', op.opacity);
      });
      $(window).on("resize", function(){
      		var b = document.documentElement.clientHeight ? document.documentElement : document.body,
		      height = b.scrollHeight > b.clientHeight ? b.scrollHeight : b.clientHeight,
		      width = b.scrollWidth > b.clientWidth ? b.scrollWidth : b.clientWidth;
		      maskDiv.css({height: height, width: width});
		      var widthspace=(maskDiv.width()-msgDiv.width());
          var heightspace=(maskDiv.height()-msgDiv.height());
          msgDiv.css({
            cursor:'wait',
            top:(heightspace/2-2),
            left:(widthspace/2-2)
          });
      });
      return maskDiv;
  },
 	unmask: function(){
     var original=$(document.body);
         if(this[0] && this[0]!==window.document){
            original=$(this[0]);
      }
      original.find("> div.maskdivgen").fadeOut('slow',0,function(){
          $(this).remove();
      });
      $('body').css({'overflow':'auto'});
  }
	 
});