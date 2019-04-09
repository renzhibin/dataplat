<script type="text/javascript">
    var conf = {/json_encode($confArr)/};
    var url = conf.url;
    var html = '';
    if (conf.params.indexOf("sensitive=1")!=-1) {
        html += '<div class="reportexplainbox" style="display:block;"> <div class="arrow_box"></div> <span style="color: red;" class="reportexplaincon">该报表包含敏感数据，请谨慎使用，禁止以任何形式泄露给其他人员</span></div>';
    }
    var frameHeight = $(window).height() - $('.navbar').height() - $('.nav-tabs').height() - 5;
    html += '<iframe src="' + conf.url + '" marginheight="0" marginwidth="0" frameborder="0" scrolling="auto" width="{/if $isMobile /}1000px{/else /}100%{//if/}" height={/if $isMobile /}2000px{/else /}' + frameHeight + '{//if/} id="iframepage" name="iframepage" onload="setHeight(this)"  ></iframe>';
    console.log(conf);
    if ($('#iframepage').length > 0) {
        $('#iframepage').attr('src', conf.url);
        if ($('.muneIcon').css('display') != 'none') {
            // breadMenu($this);
        }
    } else {
        if ($('.muneIcon').css('display') != 'none') {
            // breadMenu($this);
        }
        $('.rightreport').html('');
        $('.rightreport').html(html);
    }
</script>
