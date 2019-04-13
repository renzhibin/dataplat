{/include file="layouts/header.tpl"/}
<table class="easyui-datagrid"
        data-options="singleSelect:true,collapsible:true,url:'/visual/data',method:'get'">
    <thead>
        <tr>
            <th data-options="field:'itemid',width:80">Item ID</th>
            <th data-options="field:'productid',width:100">Product</th>
            <th data-options="field:'listprice',width:80,align:'right'">List Price</th>
            <th data-options="field:'unitcost',width:80,align:'right'">Unit Cost</th>
            <th data-options="field:'attr1',width:250">Attribute</th>
            <th data-options="field:'status',width:60,align:'center'">Status</th>
        </tr>
    </thead>
</table>
<button class='btn btn-info'>aaaa</button>
{/include file="layouts/footer.tpl"/}
