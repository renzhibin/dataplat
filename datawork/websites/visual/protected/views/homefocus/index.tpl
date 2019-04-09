{/include file="homefocus/header.tpl"/}
<script type='text/javascript' src="/assets/lib/bootstrap-3.3/js/bootstrap-paginator.js"></script>
<!--<link href="/assets/lib/DataTables-1.10.4/media/css/jquery.dataTables.css" rel="stylesheet" />
<link href="/assets/lib/dataTables/dataTables.css" rel="stylesheet" />
<script type='text/javascript' src="/assets/lib/DataTables-1.10.4/media/js/jquery.dataTables.min.js"></script>-->
<div class="container-fluid box">
    <div class="alert alert-success" style="padding: 10px; margin-bottom: 15px;">
        首焦&splash资源位申请共分为五个环节：资源位申请->时尚度申请->banner图申请->上线申请->已上线 or 已下线，
        每个环节都有待审核、已通过、审核不通过和已撤销四个状态；
    </div>

    <!--<table class="table table-bordered totalbox">
        {/foreach $locationTotal as $key=>$val/}
        <tr><td>{/$val.date/}</td>
            <td>mob端总帧数：<b class="{/if $val.mob<=5 /}redcolor{//if/}">{/$val.mob/}</b></td>
            <td>pc端总帧数：<b class="{/if $val.pc<=1 /}redcolor{//if/}">{/$val.pc/}</b></td>
        </tr>
        {//foreach/}

    </table>-->
    <div class="totalbox alert alert-warning" style="padding: 10px; margin-bottom: 15px;"><i class="glyphicon glyphicon-volume-up" style=""></i>
        今天：<span>mob端总帧数 <b class="totalnub {/if $locationTotal[0].mob<=5 /}redcolor{//if/}">{/$locationTotal[0].mob/}</b></span>
        <span>pc端总帧数 <b class="totalnub {/if $locationTotal[0].pc<=1 /}redcolor{//if/}">{/$locationTotal[0].pc/}</b></span>
        明天：<span>mob端总帧数 <b class="totalnub {/if $locationTotal[1].mob<=5 /}redcolor{//if/}">{/$locationTotal[1].mob/}</b></span>
        <span>pc端总帧数 <b class="totalnub {/if $locationTotal[1].pc<=1 /}redcolor{//if/}">{/$locationTotal[1].pc/}</b></span>
    </div>

    <div class="pad-bt10">
        <a href="/AppHomefocus/apply?flow=1" class="btn btn-primary">申请资源位</a>
        <a href="/AppHomefocus/fastapply?flow=0" class="">紧急上线申请</a>
    </div>
    <!-- Nav tabs -->
    <div>
        <ul class="nav nav-tabs mynavtabs" role="tablist">
            <li role="presentation" name="begin"><a href="?tag=begin">资源申请中</a></li>
            <li role="presentation" name="line"><a href="?tag=line">资源已上线</a></li>
            <li role="presentation" name="reply_over"><a href="?tag=reply_over">审核已过期</a></li>
            <li role="presentation" name="over"><a href="Getdatalist?tag=over">资源已下线</a></li>
            {/if $roleshowbtn['myapplylist'] == 1 /}
            <li role="presentation" name="applylist"><a href="?tag=applylist">申请资源汇总</a></li>
            {//if/}
        </ul>
    </div>
    <div class="searchbox pad-bt10">
        <form class="form-inline">
            <div class="form-group">
                <!--<label for="active_name">ID </label>
                <input type="text" class="form-control input-sm" name="ID" placeholder="" value="" />-->
                &nbsp;&nbsp;
                <label for="active_name">主题名称 </label>
                <input type="text" class="form-control input-sm" name="active_name" placeholder="" value="" />
                &nbsp;&nbsp;
                <label for="starttime">投放时间 </label>
                <input type="text" class="form-control input-sm datetimepicker" name="starttime" value="" />
                <label for="endtime">介于</label>
                <input type="text" class="form-control input-sm datetimepicker" name="endtime" value="" />
                &nbsp;&nbsp;
                <label for="creater">申请人 </label>
                <input type="text" class="form-control input-sm" name="creater" value="" />
                <label for="creater">审核状态 </label>
                <select name="reply_status">
                    <option value="">全部</option>
                    {/foreach from=$reply_arr key=k item=val /}
                        <option value="{/$k/}">{/$val/}</option>
                    {//foreach/}
                </select>
            </div>
            <input butype="button" class="btn btn-primary input-sm searchBtn" value="查询" />
            &nbsp; &nbsp; &nbsp;
            {/if $roleshowbtn['myreply'] == 0 /}
            <a href="javascript:void(0)" class="myapply" data-user="{/$name/}">我的申请</a>&nbsp;
            {//if/}
            {/if $roleshowbtn['myreply'] == 1 /}
            <a href="/AppHomefocus/myreply" class="">我的审核</a>
            {//if/}

            <a href="/AppHomefocus/entrust" class="">我的委托</a>

        </form>
    </div>

    <table class="table table-striped table-hover table-bordered">
        <thead>
        <tr>
            <!--<th>ID</th>-->
            <th>主题名称</th>
            <th>商品类目</th>
            <th>帧位</th>
            <th width="120px">图片</th>
            <th>投放开始时间～结束时间</th>
            <th>申请人</th>
            <th>申请时间</th>
            <th>申请位置</th>
            {/if $tag eq 'over'/}
            <th>uv</th>
            <th>购买用户</th>
            <th>实际成交金额</th>
            <th>uv价值</th>
            {//if/}
            <th>审核状态（申请环节）</th>
            <th>操作</th>
        </tr>
        </thead>
        <tfoot></tfoot>
        <tbody class="tablelist datalist">
        {/if count($result) neq 0 /}
        {/foreach from=$result key=k item=val /}
        <tr data_title="{/$val.active_name/}">
            <!--<td>{/$val.id/}</td>-->
            <td class="activename">
                <a href="javascript:void(0)" data-toggle="tooltip" title="{/$val.fashioninfostr/}">{/$val.active_name/}</a>
            </td>
            <td>{/$val.product_categroy/}</td>
            <td>{/$val.locationsort/}</td>
            <td width="120">
                {/if isset($val.imgurl) && $val.imgurl != "" /}
                <a target='_blank' href='http://imgtest.meiliworks.com/{/$val.imgurl/}'>
                    <img width='120' src='http://imgtest.meiliworks.com/{/$val.imgurl/}' /></a>

                {/else/}
                暂无
                {//if/}
                {/if isset($val.outurl) && $val.outurl != "" /}
                <br/><a target='_blank' href='{/$val.outurl/}'>查看活动链接</a>
                {//if/}
            </td>
            <td>{/$val.starttime/}<br/>{/$val.endtime/}</td>
            <td>{/$val.creater/}</td>
            <td>{/$val.create_time/}</td>
            <td>{/$val.locationstr/}</td>
            {/if $tag eq 'over'/}
            <td class="activi_page_flux_banner_click_uv">不存在</td>
            <td class="activi_page_flux_banner_order_succ_banner_buyer_uid">不存在</td>
            <td style="display: none" class="activi_page_flux_banner_order_succ_price_gmv">不存在</td>
            <td style="display: none" class="activi_page_flux_banner_order_succ_banner_shop">不存在</td>
            <td class="price_shiji">
                不存在</td>
            <td class="uv_jiazhi">不存在</td>
            {//if/}
            <td>{/$val.statusname/}</td>
            <td>
                <a href="/AppHomefocus/detail?flow={/$val.status/}&id={/$val.id/}">查看</a>

                {/if $val.role eq "creater"/}
                    {/if $val.reply_status eq 0 && $val.status != -1 /}
                    {/assign var=auditarr value="{/$val.auditinfo/}"|@json_decode /}
                        {/if $auditarr->do|@count eq 0/}
                            {/if $val.status eq 0/}<!--紧急上线-->
                    <a href="/AppHomefocus/fastapply?flow={/$val.status/}&id={/$val.id/}">修改</a>
                    {/else/}
                    <a href="/AppHomefocus/apply?flow={/$val.status/}&id={/$val.id/}">修改</a>
                            {//if/}
                        {/else/}
                <a href="javascript:void(0)" class="editdisabled">修改</a>
                        {//if/}
                <a href="javascript:void(0)" class="clickBtn" key="cancel" flow="{/$val.status/}" id="{/$val.id/}">撤销</a>
                    {/else if $val.reply_status eq 1 && $val.status < 4/}
                <a href="/AppHomefocus/apply?flow={/math equation="( x + y ) " x=$val.status y=1 /}&id={/$val.id/}">
                        {/if $val.status eq "1"/}时尚度申请
                        {/else if $val.status eq "2"/}
                    banner图申请
                        {/else if $val.status eq "3"/}
                    上线申请
                        {//if/}
                </a>
                    {/else if $val.reply_status eq 2/}
                        {/if $val.status eq 0/}<!--紧急上线-->
                    <a href="/AppHomefocus/fastapply?flow={/$val.status/}&id={/$val.id/}">修改</a>
                        {/else/}
                    <a href="/AppHomefocus/apply?flow={/$val.status/}&id={/$val.id/}">修改</a>
                        {//if/}
                {/else if $val.reply_status eq -1/}
                <a href="/AppHomefocus/apply?flow={/$val.status/}&id={/$val.id/}">
                        {/if $val.status eq "1"/}资源位申请
                        {/else if $val.status eq "2"/}
                    时尚度申请
                        {/else if $val.status eq "3"/}
                    banner图申请
                        {/else if $val.status eq "4"/}
                    上线申请
                        {//if/}
                </a>
                    {/else if $val.status > 4/}
                <a href="/AppHomefocus/hotEdit?flow={/$val.status/}&id={/$val.id/}">在线修复</a>

                    {//if/}

                {/else if $val.role eq "reply" && $val.reply_status eq "0"/}
                <a href="/AppHomefocus/reply?flow={/$val.status/}&id={/$val.id/}">审核</a>
                {/else if $val.role eq "online"/}
                {/if ($val.status eq '0' || $val.status eq '4') && $val.reply_status eq '1'/}
                <a href="/AppHomefocus/edit?flow={/$val.status/}&id={/$val.id/}">上线</a>
                {/else if $val.status gte '5'/}
                <a href="javascript:void(0)" class="clickBtn" key="offlinecode" flow="{/$val.status/}" id="{/$val.id/}">下线</a>
                {/if $val.status eq '5' && $val.isMob eq 1/}
                <a href="/AppHomefocus/wechatpurse?flow={/$val.status/}&id={/$val.id/}">打通微信钱包</a>
                {/else if $val.status eq '6' && $val.isMob eq 1/}
                <a href="javascript:void(0)" class="clickBtn" key="offlinewechatpurse" flow="{/$val.status/}" id="{/$val.id/}">下线微信钱包</a>
                {//if/}
                {//if/}
                {//if/}

            </td>
        </tr>
        {//foreach/}
        {/else/}
        <tr><td colspan="11"> <span class="redcolor">暂无数据 </span></td></tr>
        {//if/}
        </tbody>
    </table>
    <!--分页-->
    <div class="row">
        <div class="col-xs-2" >
            <p style="margin:20px 0">总共 <b class="total">{/$total/}</b> 条数据</p>
        </div>
        <div class="col-xs-8 text-center">
            <div class="pagination" id="list_pageination">
                <!--<ul class="">
                    <li><a href="#">上一页</a></li>
                    <li class="active"><a href="#">1</a></li>
                    <li><a href="#">2</a></li>
                    <li><a href="#">3</a></li>
                    <li><a href="#">4</a></li>
                    <li><a href="#">5</a></li>
                    <li><a href="#">下一页</a></li>
                </ul>-->
            </div>
        </div>

    </div>

</div>


<script type="text/javascript">
    if(!window.indexlist){
        window.indexlist = {};
    }
    indexlist['listpages'] = {/$pages/};
    indexlist['total'] = {/$total/};
</script>
<script type="text/javascript" src="/assets/homefocus/list.js?version={/$version/}"></script>
<script>
    function GetQueryString(name)
    {
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r!=null)return  unescape(r[2]); return null;
    }

    function GetDateStr(AddDayCount) {
        var dd = new Date();
        dd.setDate(dd.getDate()+AddDayCount);//获取AddDayCount天后的日期
        var y = dd.getFullYear();
        var m = dd.getMonth()+1;//获取当前月份的日期
        var d = dd.getDate();
        return y+"-"+m+"-"+d;
    }

    function get_data_uv() {
        if (GetQueryString("tag") == 'over'){

            $('.activename').each(function () {
                $this_td = $(this);

                //console.log($(this).find('a').html(data));
                $.get('/visual/getData?search=[{"key":"title_desc","val":["'+$(this).find('a').html()+'"],"op":"like"}]&project=home_page_business&group=title_desc&metric=activi_page_flux.banner_click.uv,activi_page_flux.banner_click.pv,activi_page_flux.banner_click.link_url,activi_page_flux.banner_single.uv,activi_page_flux.banner_single.pv,activi_page_flux.banner_xia_order.banner_order,activi_page_flux.banner_xia_order.banner_buyer_uid,activi_page_flux.banner_xia_order.banner_shop,activi_page_flux.banner_xia_order.banner_twitter,activi_page_flux.banner_xia_order.price_gmv,activi_page_flux.banner_order_succ.banner_order,activi_page_flux.banner_order_succ.banner_buyer_uid,activi_page_flux.banner_order_succ.banner_shop,activi_page_flux.banner_order_succ.banner_twitter,activi_page_flux.banner_order_succ.price_gmv,activi_page_flux.banner_order_succ.amount,activi_page_flux.banner_order_succ.shop_credit&date='+GetDateStr(-1)+'&edate='+GetDateStr(-1)+'&date_type=day'
                        , {}, function (data) {
                            data = $.parseJSON(data);
                            if (data.total > 0) {
                                //alert(data.rows[0].title_desc);
                                $jq_tr = $('[data_title="' + data.rows[0].title_desc + '"]');
                                $jq_tr.find('.activi_page_flux_banner_click_uv').html(data.rows[0].activi_page_flux_banner_click_uv);
                                $jq_tr.find('.activi_page_flux_banner_order_succ_banner_buyer_uid').html(data.rows[0].activi_page_flux_banner_order_succ_banner_buyer_uid)
                                $jq_tr.find('.activi_page_flux_banner_order_succ_price_gmv').html(data.rows[0].activi_page_flux_banner_order_succ_price_gmv);
                                $jq_tr.find('.activi_page_flux_banner_order_succ_banner_shop').html(data.rows[0].activi_page_flux_banner_order_succ_banner_shop);
                                $shop = data.rows[0].activi_page_flux_banner_order_succ_banner_shop;
                                $uv = $jq_tr.find('.activi_page_flux_banner_click_uv .data_name').html();
                                $gmv = $jq_tr.find('.activi_page_flux_banner_order_succ_price_gmv .data_name').html();
                                $shop = $jq_tr.find('.activi_page_flux_banner_order_succ_banner_shop .data_name').html();
                                $gmv = $gmv.replace(',', '');
                                $shop = $shop.replace(',', '');
                                $shiji = parseFloat($gmv) - parseFloat($shop);
                                $jq_tr.find('.price_shiji').html($shiji);
                                $uv = $uv.replace(',', '');
                                $uv_jiazhi = $shiji / parseFloat($uv);
                                $jq_tr.find('.uv_jiazhi').html($uv_jiazhi.toFixed(2));
                                if($uv_jiazhi.toFixed(2)=='NaN'){
                                    $uv_jiazhi='不存在';
                                    $jq_tr.find('.uv_jiazhi').html($uv_jiazhi);
                                }
                            }else{

                                $.post('/visual/getData'
                                        , {
                                            datas:'{"project":"home_page_business","group":"acticity_title","metric":"splash_data.splash_event_dianji.splash_uv,splash_data.splash_event_dianji.splash_pv,splash_data.splash_event_danbao.splash_duv,splash_data.splash_event_danbao.splash_dpv,splash_data.splash_event_xiadan.banner_order,splash_data.splash_event_xiadan.banner_buyer_uid,splash_data.splash_event_xiadan.banner_shop,splash_data.splash_event_xiadan.banner_twitter,splash_data.splash_event_xiadan.price_gmv,splash_data.splash_event_xiadan.amount,splash_data.splash_event_order.banner_order_succ,splash_data.splash_event_order.banner_uid_succ,splash_data.splash_event_order.banner_shop_succ,splash_data.splash_event_order.banner_t_succ,splash_data.splash_event_order.price_gmv_succ,splash_data.splash_event_order.amount_succ,splash_data.splash_event_order.shop_credit","date":"'+GetDateStr(-1)+'","edate":"'+GetDateStr(-1)+'","customSort":"[{\\"key\\":\\"date\\",\\"order\\":\\"desc\\"},{\\"key\\":\\"splash_data_splash_event_dianji_splash_pv\\",\\"order\\":\\"desc\\"}]","udc":"单品upv=splash_data.splash_event_danbao.splash_dpv/splash_data.splash_event_danbao.splash_duv,danping_upv=splash_data.splash_event_danbao.splash_duv/splash_data.splash_event_dianji.splash_uv*100,tidailv=splash_data.splash_event_order.banner_uid_succ/splash_data.splash_event_dianji.splash_uv*100,kejianshu=splash_data.splash_event_order.amount_succ/splash_data.splash_event_order.banner_uid_succ,shiji_gmv=splash_data.splash_event_order.price_gmv_succ-splash_data.splash_event_order.shop_credit,kedianjia=shiji_gmv/splash_data.splash_event_order.banner_uid_succ,uvjiazhe=shiji_gmv/splash_data.splash_event_dianji.splash_uv","udcconf":"%5B%7B%22name%22%3A%22%E5%8D%95%E5%93%81upv%22%2C%22cn_name%22%3A%22%E5%8D%95%E5%93%81UPV%22%2C%22explain%22%3A%22%E5%8D%95%E5%93%81%E9%A1%B5%E7%82%B9%E5%87%BBPV%EF%BC%8F%E5%8D%95%E5%93%81%E9%A1%B5%E7%82%B9%E5%87%BBUV%22%2C%22expression%22%3A%22splash_data.splash_event_danbao.splash_dpv%2Fsplash_data.splash_event_danbao.splash_duv%22%7D%2C%7B%22name%22%3A%22danping_upv%22%2C%22cn_name%22%3A%22%E5%8D%95%E5%93%81UV%E7%A9%BF%E9%80%8F%22%2C%22explain%22%3A%22%E5%8D%95%E5%93%81%E9%A1%B5%E7%82%B9%E5%87%BBUV%EF%BC%8F%E8%90%BD%E5%9C%B0%E9%A1%B5%E7%82%B9%E5%87%BB%E4%BA%BA%E6%95%B0%22%2C%22expression%22%3A%22splash_data.splash_event_danbao.splash_duv%2Fsplash_data.splash_event_dianji.splash_uv*100%22%7D%2C%7B%22name%22%3A%22tidailv%22%2C%22cn_name%22%3A%22%E6%8F%90%E8%A2%8B%E7%8E%87%22%2C%22explain%22%3A%22%E8%B4%AD%E4%B9%B0%E7%94%A8%E6%88%B7%E6%95%B0%EF%BC%8F%E8%90%BD%E5%9C%B0%E9%A1%B5%E7%82%B9%E5%87%BB%E4%BA%BA%E6%95%B0%22%2C%22expression%22%3A%22splash_data.splash_event_order.banner_uid_succ%2Fsplash_data.splash_event_dianji.splash_uv*100%22%7D%2C%7B%22name%22%3A%22kejianshu%22%2C%22cn_name%22%3A%22%E5%AE%A2%E4%BB%B6%E6%95%B0%22%2C%22explain%22%3A%22%E8%B4%AD%E4%B9%B0%E9%94%80%E9%87%8F%EF%BC%8F%E8%B4%AD%E4%B9%B0%E7%94%A8%E6%88%B7%E6%95%B0%22%2C%22expression%22%3A%22splash_data.splash_event_order.amount_succ%2Fsplash_data.splash_event_order.banner_uid_succ%22%7D%2C%7B%22name%22%3A%22shiji_gmv%22%2C%22cn_name%22%3A%22%E5%AE%9E%E9%99%85%E6%88%90%E4%BA%A4%E9%87%91%E9%A2%9D%22%2C%22explain%22%3A%22%E6%88%90%E4%BA%A4%E9%87%91%E9%A2%9D%EF%BC%8D%E5%BA%97%E9%93%BA%E4%BC%98%E6%83%A0%E5%88%B8%E9%87%91%E9%A2%9D%22%2C%22expression%22%3A%22splash_data.splash_event_order.price_gmv_succ-splash_data.splash_event_order.shop_credit%22%7D%2C%7B%22name%22%3A%22kedianjia%22%2C%22cn_name%22%3A%22%E5%AE%A2%E5%8D%95%E4%BB%B7%22%2C%22explain%22%3A%22%E5%AE%9E%E9%99%85%E6%88%90%E4%BA%A4%E9%87%91%E9%A2%9D%2F%E8%B4%AD%E4%B9%B0%E7%94%A8%E6%88%B7%E6%95%B0%22%2C%22expression%22%3A%22shiji_gmv%2Fsplash_data.splash_event_order.banner_uid_succ%22%7D%2C%7B%22name%22%3A%22uvjiazhe%22%2C%22cn_name%22%3A%22UV%E4%BB%B7%E5%80%BC%22%2C%22explain%22%3A%22%E5%AE%9E%E9%99%85%E6%88%90%E4%BA%A4%E9%87%91%E9%A2%9D%2F%E8%90%BD%E5%9C%B0%E9%A1%B5%E7%82%B9%E5%87%BB%E4%BA%BA%E6%95%B0%22%2C%22expression%22%3A%22shiji_gmv%2Fsplash_data.splash_event_dianji.splash_uv%22%7D%5D","filter":"[{\\"op\\":\\">\\",\\"val\\":[\\"0\\"],\\"key\\":\\"splash_data.splash_event_dianji.splash_pv\\"}]","grade":{"header":[{"key":"type","name":"类型","width":"4%"},{"key":"name","name":"列显示名称","width":"10%"},{"key":"key","name":"列key","width":"8%"},{"key":"explain","name":"列说明","width":"13%"},{"key":"filter","name":"数据过滤","width":"10%"},{"key":"expression","name":"计算值","width":"10%"},{"key":"percent","name":"百分比","width":"5%"},{"key":"issearch","name":"搜索","width":"10%"},{"key":"search","name":"即时过滤","width":"6%"},{"key":"otherlink","name":"外链","width":"6%"},{"key":"img_link","name":"图片显示","width":"6%"},{"key":"fixed","name":"是否固定","width":"7%"},{"key":"sort","name":"默认排序","width":"4%"},{"key":"hide","name":"隐藏全选","width":"6%"},{"key":"converge","name":"聚合","width":"6%"},{"key":"width","name":"宽度<br/>(像素)","width":"6%"}],"data":[{"type":"维度","name":"时间","key":"date","explain":"显示时间","filter":"-","expression":null,"percent":"-","issearch":"-","search":"-","otherlink":"-","img_link":"-","fixed":1,"sort":"desc","hide":0,"converge":"-","width":"100","isgroup":1},{"type":"维度","name":"活动名称","key":"acticity_title","explain":"","filter":null,"expression":null,"percent":"-","issearch":{"is_check":1,"is_accurate":0},"search":{"isshow":1},"otherlink":"/visual/index/menu_id/721/id/2535?acticity_title=${acticity_title}","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"-","width":"100","isgroup":1},{"type":"指标","name":"落地页访问人数","key":"splash_data.splash_event_dianji.splash_uv","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"","width":"100","isgroup":2},{"type":"指标","name":"落地页访问次数","key":"splash_data.splash_event_dianji.splash_pv","explain":"","filter":{"op":">","val":["0"],"key":"splash_data.splash_event_dianji.splash_pv"},"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"desc","hide":0,"converge":"","width":"100","isgroup":2},{"type":"指标","name":"单品页点击UV","key":"splash_data.splash_event_danbao.splash_duv","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"","width":"100","isgroup":2},{"type":"指标","name":"单品页点击PV","key":"splash_data.splash_event_danbao.splash_dpv","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"","width":"100","isgroup":2},{"type":"UDC","name":"单品UPV","key":"单品upv","explain":"单品页点击PV／单品页点击UV","filter":null,"expression":"splash_data.splash_event_danbao.splash_dpv/splash_data.splash_event_danbao.splash_duv","percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"-","width":"100","isgroup":3},{"type":"UDC","name":"单品UV穿透","key":"danping_upv","explain":"单品页点击UV／落地页点击人数","filter":null,"expression":"splash_data.splash_event_danbao.splash_duv/splash_data.splash_event_dianji.splash_uv*100","percent":1,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"-","width":"100","isgroup":3},{"type":"指标","name":"下单数","key":"splash_data.splash_event_xiadan.banner_order","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"","width":"100","isgroup":2},{"type":"指标","name":"下订单人数","key":"splash_data.splash_event_xiadan.banner_buyer_uid","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"","width":"100","isgroup":2},{"type":"指标","name":"下单店铺数","key":"splash_data.splash_event_xiadan.banner_shop","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"","width":"100","isgroup":2},{"type":"指标","name":"下单商品数","key":"splash_data.splash_event_xiadan.banner_twitter","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"","width":"100","isgroup":2},{"type":"指标","name":"下单金额","key":"splash_data.splash_event_xiadan.price_gmv","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"","width":"100","isgroup":2},{"type":"指标","name":"下单销量","key":"splash_data.splash_event_xiadan.amount","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"","width":"100","isgroup":2},{"type":"指标","name":"购买订单数","key":"splash_data.splash_event_order.banner_order_succ","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"","width":"100","isgroup":2},{"type":"指标","name":"购买用户数","key":"splash_data.splash_event_order.banner_uid_succ","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"","width":"100","isgroup":2},{"type":"UDC","name":"提袋率","key":"tidailv","explain":"购买用户数／落地页点击人数","filter":null,"expression":"splash_data.splash_event_order.banner_uid_succ/splash_data.splash_event_dianji.splash_uv*100","percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"-","width":"100","isgroup":3},{"type":"指标","name":"购买店铺数","key":"splash_data.splash_event_order.banner_shop_succ","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"","width":"100","isgroup":2},{"type":"指标","name":"购买商品数","key":"splash_data.splash_event_order.banner_t_succ","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"","width":"100","isgroup":2},{"type":"指标","name":"成交金额","key":"splash_data.splash_event_order.price_gmv_succ","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":1,"converge":"","width":"100","isgroup":2},{"type":"指标","name":"购买销量","key":"splash_data.splash_event_order.amount_succ","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"","width":"100","isgroup":2},{"type":"UDC","name":"客件数","key":"kejianshu","explain":"购买销量／购买用户数","filter":null,"expression":"splash_data.splash_event_order.amount_succ/splash_data.splash_event_order.banner_uid_succ","percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"-","width":"100","isgroup":3},{"type":"指标","name":"店铺优惠券金额","key":"splash_data.splash_event_order.shop_credit","explain":"","filter":null,"expression":null,"percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":1,"converge":"","width":"100","isgroup":2},{"type":"UDC","name":"实际成交金额","key":"shiji_gmv","explain":"成交金额－店铺优惠券金额","filter":null,"expression":"splash_data.splash_event_order.price_gmv_succ-splash_data.splash_event_order.shop_credit","percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"-","width":"100","isgroup":3},{"type":"UDC","name":"客单价","key":"kedianjia","explain":"实际成交金额/购买用户数","filter":null,"expression":"shiji_gmv/splash_data.splash_event_order.banner_uid_succ","percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"-","width":"100","isgroup":3},{"type":"UDC","name":"UV价值","key":"uvjiazhe","explain":"实际成交金额/落地页点击人数","filter":null,"expression":"shiji_gmv/splash_data.splash_event_dianji.splash_uv","percent":0,"issearch":null,"search":{"isshow":0},"otherlink":"","img_link":"","fixed":0,"sort":"filter_not","hide":0,"converge":"-","width":"100","isgroup":3}],"contrast":null,"pubdata":{"isproportion":"0","ispagesize":"1","pagesize":10}},"isnew":1,"date_type":"day","type":"1","title":"Splash活动数据","id":0,"search":"[{\\"key\\":\\"acticity_title\\",\\"val\\":[\\"'+$this_td.find('a').html()+'\\"],\\"op\\":\\"like\\"}]"}'
                                                ,page:1
                                                ,rows:10
                                            }
                                                , function (data) {
                                                data = $.parseJSON(data);
                                                if (data.total > 0) {
                                                var div = $('<div></div>');
                                                div.html(data.rows[0].acticity_title);

                                                //alert(data.rows[0].acticity_title);
                                                $jq_tr = $('[data_title="' + div.find('a').html() + '"]');
                                                $jq_tr.find('.activi_page_flux_banner_click_uv').html(data.rows[0].splash_data_splash_event_dianji_splash_uv);
                                                $jq_tr.find('.activi_page_flux_banner_order_succ_banner_buyer_uid').html(data.rows[0].splash_data_splash_event_xiadan_banner_buyer_uid)
                                                $jq_tr.find('.activi_page_flux_banner_order_succ_price_gmv').html(data.rows[0].splash_data_splash_event_xiadan_price_gmv);
                                                $jq_tr.find('.activi_page_flux_banner_order_succ_banner_shop').html(data.rows[0].splash_data_splash_event_xiadan_banner_shop);

                                                $shop = data.rows[0].splash_data_splash_event_xiadan_banner_shop;
                                                $uv = $jq_tr.find('.activi_page_flux_banner_click_uv .data_name').html();
                                                $gmv = $jq_tr.find('.activi_page_flux_banner_order_succ_price_gmv .data_name').html();
                                                $shop = $jq_tr.find('.activi_page_flux_banner_order_succ_banner_shop .data_name').html();
                                                console.log($jq_tr);
                                                $gmv = $gmv.replace(',', '');
                                                $shop = $shop.replace(',', '');
                                                $shiji = parseFloat($gmv) - parseFloat($shop);
                                                $jq_tr.find('.price_shiji').html($shiji);
                                                $uv = $uv.replace(',', '');
                                                $uv_jiazhi = $shiji / parseFloat($uv);
                                                $jq_tr.find('.uv_jiazhi').html($uv_jiazhi.toFixed(2));

                                                console.log(data);
                                                                    }
                                                $('.showbox.btn-group.btn-group-xs').remove();
                                                $('.compros').removeAttr('style');
                                                //console.log(data);
                                                                })


                                    }
                                    $('.showbox.btn-group.btn-group-xs').remove();
                            $('.compros').removeAttr('style');
                            //console.log(data);
                        })
                        })

            }


    }


    get_data_uv();
    window.onload=function() {
        //太脏了....有空改吧
        var tag = $('.mynavtabs').find('li.active').attr('name');
        var senddata = getSearchData();
        senddata['page'] = 1;
        senddata['pagecount'] = 50;
        senddata['tag'] = tag ? tag : 'begin';
        renderstylelist(senddata);
    }
</script>
{/include file="homefocus/footer.tpl"/}
