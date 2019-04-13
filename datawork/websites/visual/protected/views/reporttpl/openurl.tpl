<script type="text/javascript">
    var conf = {/json_encode($confArr)/};
    var url = conf.url;
    var html = "";
    var frameHeight = $(window).height() - $('.navbar').height() - $('.nav-tabs').height() - 5;
    html += '<iframe src="' + conf.url + '" marginheight="0" marginwidth="0" frameborder="0" scrolling="auto" width="100%" height=' + frameHeight + ' id="iframepage" name="iframepage" onload="setHeight(this)"  ></iframe>';
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
