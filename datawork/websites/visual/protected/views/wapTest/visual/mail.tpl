{/include file="layouts/script.tpl"/}
<h1>请勿随意发送全体邮件.发送人 di-inf@meilishuo.com</h1>
<div id="left" style="width: 620px;display: inline-block;">
    <span>将对最近30天访问过的人发送邮件</span>
    <form style="width: 600px;" action="/Visual/send_all_mail" method="post">
        <div style="margin:20px;"><label>标题:</label><input name="title" type="text" style="width: 500px; height: 20px; border:solid 1px;" /></div>
        <textarea name="content" style="width:600px; height: 300px; vertical-align:top;"></textarea>
        <br/>
        <select name="set" style="display: inline-block;float: left; margin-top: 30px;">
            <option value="1">直接发送</option>
            <option value="2">回车转html换行</option>
        </select>
        <br/>
        <input type="submit" class="btn for_parent btn-primary" value="确认发送" style="font-size: 20px; float: right; margin-top: 10px;height: 60px;width: 80px;"/>
    </form>
</div>
<div id="right" style="display: inline-block; vertical-align:top;  margin-left: 50px;">
    <h5 style="margin-right: 50px;">最近30天访问过的人(共计{/$count/}人)</h5>
    <ul>
        {/foreach from = $recently item= item key=key/}
        <li>{/$item.user_name/}</li>
        {//foreach/}
    </ul>
</div>