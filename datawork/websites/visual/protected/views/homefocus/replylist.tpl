{/include file="homefocus/header.tpl"/}
<script type='text/javascript' src="/assets/lib/bootstrap-3.3/js/bootstrap-paginator.js"></script>

<div class="container-fluid box">
    <!--<div class="alert alert-success">
        首焦&splash资源位申请共分为五个环节：资源位申请->时尚度申请->banner图申请->上线申请->已上线 or 已下线，
        每个环节都有待审核、已通过、审核不通过和已撤销四个状态；
    </div>-->
    <div class="">
        <ol class="breadcrumb">
            <li><a href="/AppHomefocus/index">首页</a></li>
            <li><a href="javascript:void(0)"><b class="applistTitle">我的审核列表</b></a></li>
        </ol>
    </div>
    <div class="">
        <input type="button" value="批量审核通过" name="batchreply" class="btn btn-primary batchreply" />
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
            </div>
            <input type="button" class="btn btn-primary input-sm myreply_searchBtn" value="查询" />
        </form>
    </div>

    <table class="table table-striped table-hover table-bordered">
        <thead>
        <tr>
            <!--<th>ID</th>-->
            <th>全选 <input type="checkbox" value="" name="allcheck" /></th>
            <th>主题名称</th>
            <th>商品类目</th>
            <th>帧位</th>
            <th width="120px">图片</th>
            <th>投放开始时间～结束时间</th>
            <th>申请人</th>
            <th>申请时间</th>
            <th>申请位置</th>
            <th>审核状态（申请环节）</th>
            <th>操作</th>
        </tr>
        </thead>
        <tfoot></tfoot>
        <tbody class="tablelist reply_datalist">
        {/if count($result) neq 0 /}
        {/foreach from=$result key=k item=val /}
        <tr>
            <!--<td>{/$val.id/}</td>-->
            <td><input type="checkbox" value="" name="ckreply" data="{/$val.id/}" status="{/$val.status/}" /></td>
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
            <td>
                {/$val.locationstr/}</td>
            <td>{/$val.statusname/}</td>
            <td>
                <a href="/AppHomefocus/detail?flow={/$val.status/}&id={/$val.id/}">查看</a>
                {/if $val.role eq "creater"/}
                {/if $val.reply_status eq 0 && $val.status != -1 /}
                {/assign var=auditarr value="{/$val.auditinfo/}"|@json_decode /}
                {/if $auditarr->do|@count eq 0/}
                <a href="/AppHomefocus/apply?flow={/$val.status/}&id={/$val.id/}">修改</a>
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
                <a href="/AppHomefocus/apply?flow={/$val.status/}&id={/$val.id/}">修改</a>
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
                {/else/}

                {//if/}
                {/else if $val.role eq "reply" && $val.reply_status eq "0"/}
                <a href="/AppHomefocus/reply?flow={/$val.status/}&id={/$val.id/}">审核</a>
                {/else if $val.role eq "online"/}
                {/if $val.status eq '4' && $val.reply_status eq '1'/}
                <a href="/AppHomefocus/edit?flow={/$val.status/}&id={/$val.id/}">上线</a>
                {/else if $val.status eq '5'/}
                <a href="javascript:void(0)" class="clickBtn" key="offlinecode" flow="{/$val.status/}" id="{/$val.id/}">下线</a>
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
            <div class="pagination" id="replylist_pageination">
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
<script type="text/javascript" src="/assets/homefocus/list.js"></script>
<script type="text/javascript" src="/assets/homefocus/replylist.js"></script>


{/include file="homefocus/footer.tpl"/}
