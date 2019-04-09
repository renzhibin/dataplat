{/include file="layouts/header.tpl"/}
<link rel="stylesheet" type="text/css" href="/assets/css/index.css?version={/$version/}">
{assign var="index" value=1} 
<div>
    {/include file="layouts/menu.tpl"/}
    <div id='right' style="margin-top: 30px">
        <div id="content" class="content" style="padding:15px 15px 0px 15px">
             
            <table class="table table-bordered table-condensed table-bordered table-striped">
                <tr style="background-color: #d5d2c4">
                    <td style="text-align:center">序号</td>
                    <td style="text-align:center">一级菜单</td>
                    <td style="text-align:center">二级菜单</td>
                    <td style="text-align:center">报表名称</td>
                </tr>
                {/foreach from = $first item=firstMenu key=firstkey/}
                    <tr>
                    <td style="text-align:center">{/$firstkey +1/}</td>
                    <td style='text-align: center' >{/$firstMenu.name/}</td>
                    <td style='text-align: center' > {/$firstMenu.children[0].name/}</td>
                    <td style='text-align: center' >{/$firstMenu.children[0]['children'][0].name/}</td>
                    </tr>
                    {/foreach from = $firstMenu.children item=secondMenu key=secnondkey/}
                        {/if $secnondkey >0/}
                            <tr>
                                <td> </td>
                                <td> </td>
                                <td style='text-align: center'>{/$secondMenu.name/}</td>
                                <td style='text-align: center' >{/$secondMenu.children[0].name/}</td>  
                            </tr>
                        {//if/}
                        {/foreach from = $secondMenu.children item=tableMenu key=tablekey/}
                            {/if $tablekey >0/}
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td ></td>
                                    <td style='text-align: center'>{/$tableMenu.name/}</td>
                                </tr>
                            {//if/}
                        {//foreach/}
                    {//foreach/}
                {//foreach/}
            </table>
        </div>
    </div>
</div>
{/include file="layouts/menujs.tpl"/}
 































































