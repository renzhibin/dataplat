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
        <th>审核状态（申请环节）</th>
        <th>操作</th>
    </tr>
    </thead>
    <tfoot></tfoot>
    <tbody class="tablelist datalist">
    {/if count($result) neq 0 /}
    {/foreach from=$result key=k item=val /}
    <tr>
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
        <td>
            {/$val.locationstr/}</td>
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
            {/else/}

            {//if/}
            {/else if $val.role eq "reply" && $val.reply_status eq "0"/}
            <a href="/AppHomefocus/reply?flow={/$val.status/}&id={/$val.id/}">审核</a>
            {/else if $val.role eq "online"/}
            {/if ($val.status eq '0' || $val.status eq '4') && $val.reply_status eq '1'/}
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