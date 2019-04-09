<!--微信钱包专用资源位申请 -->
<script id='wechatapplysourcetmpl' type='text/x-dot-template'>
    <table class="table table-bordered">
        <tbody>
        <tr>
            <td>活动名称：</td>
            <td>{{=it[0].sourceinfo.active_name}}
                <input type="hidden" class="form-control input-sm" name="active_name" placeholder=""
                       value="{{ if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null) { }}
                   {{=it[0].sourceinfo.active_name}}
                   {{ } }}" /></td>
        </tr>
        <tr>
            <td>商品类目：</td>
            <td>{{=it[0].sourceinfo.product_categroy}}
                <select class="form-control" style="display: none" name="product_categroy">
                    <option value="女装" selected="selected">女装</option>
                    <option value="女鞋">女鞋</option>
                    <option value="女包">女包</option>
                    <option value="男装">男装</option>
                    <option value="男包">男包</option>
                    <option value="男鞋">男鞋</option>
                    <option value="男配">男配</option>
                    <option value="童装">童装</option>
                    <option value="童鞋">童鞋</option>
                    <option value="童包">童包</option>
                    <option value="童配">童配</option>
                    <option value="美妆">美妆</option>
                    <option value="配饰">配饰</option>
                    <option value="食品">食品</option>
                    <option value="家居">家居</option>
                    <option value="数码小家电">数码小家电</option>
                    <option value="优惠券">优惠券</option>
                    <option value="其他">其他</option>
                </select>

            </td>
        </tr>
        <tr>
            <td>投放时间：</td>
            <td>
                <from class="form-inline">
                    <div class="form-group">
                        <label for="starttime">开始时间</label>
                        <input type="text" class="form-control input-sm datetimepicker" name="starttime" value="{{ if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null) { }}
                   {{=it[0].sourceinfo.starttime}}
                   {{ } }}" />&nbsp;
                        <label for="endtime">结束时间</label>
                        <input type="text" class="form-control input-sm datetimepicker" name="endtime" value="{{ if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null) { }}
                   {{=it[0].sourceinfo.endtime}}
                   {{ } }}" />
                    </div>
                </from>
            </td>
        </tr>
        <tr>
            <td>申请帧位：</td>
            <td><input type="text" class="form-control input-sm" name="locationsort" placeholder="整数" value="{{if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null){ }}{{=parseInt(it[0].sourceinfo.locationsort)}}{{ } }}" /></td>
        </tr>
        <tr>
            <td>申请位置：</td>
            <td>
                <label class="checkbox-inline">
                    <input type="checkbox" name="location" id="ck-mob" value="mob" {{ if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null && it[0].sourceinfo.location.indexOf('mob')>=0) { }}
                           checked {{ } }}>mob
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="location" id="ck-pc" value="pc" {{ if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null && it[0].sourceinfo.location.indexOf('pc')>=0) { }}
                           checked
                            {{ } }}>pc
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="location" id="ck-splash" value="splash" {{ if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null && it[0].sourceinfo.location.indexOf('splash')>=0) { }}
                           checked
                            {{ } }}> splash
                </label>
            </td>
        </tr>
        <tr>
            <td>背景：</td>
            <td>{{=it[0].sourceinfo.info}}
                <textarea style="display: none;" class="form-control input-sm" name="info" placeholder="">{{ if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null) { }}
                    {{=it[0].sourceinfo.info}}
                    {{ } }}</textarea></td>
        </tr>
        </tbody>
    </table>
</script>


<!--资源位申请 -->
<script id='applysourcetmpl' type='text/x-dot-template'>
    <table class="table table-bordered">
        <tbody>
        <tr>
            <td>活动名称：</td>
            <td><input type="text" maxlength="10" class="form-control input-sm" name="active_name" placeholder=""
                       value="{{ if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null) { }}
                   {{=it[0].sourceinfo.active_name}}
                   {{ } }}" /></td>
        </tr>
        <tr>
            <td>商品类目：</td>
            <td>
                <select class="form-control" name="product_categroy">
                    <option value="女装" selected="selected">女装</option>
                    <option value="女鞋">女鞋</option>
                    <option value="女包">女包</option>
                    <option value="男装">男装</option>
                    <option value="男包">男包</option>
                    <option value="男鞋">男鞋</option>
                    <option value="男配">男配</option>
                    <option value="童装">童装</option>
                    <option value="童鞋">童鞋</option>
                    <option value="童包">童包</option>
                    <option value="童配">童配</option>
                    <option value="美妆">美妆</option>
                    <option value="配饰">配饰</option>
                    <option value="食品">食品</option>
                    <option value="家居">家居</option>
                    <option value="数码小家电">数码小家电</option>
                    <option value="优惠券">优惠券</option>
                    <option value="其他">其他</option>
                </select>

            </td>
        </tr>
        <tr>
            <td>投放时间：</td>
            <td>
                <from class="form-inline">
                    <div class="form-group">
                        <label for="starttime">开始时间</label>
                        <input type="text" class="form-control input-sm datetimepicker" name="starttime" value="{{ if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null) { }}
                   {{=it[0].sourceinfo.starttime}}
                   {{ } }}" />&nbsp;
                        <label for="endtime">结束时间</label>
                        <input type="text" class="form-control input-sm datetimepicker" name="endtime" value="{{ if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null) { }}
                   {{=it[0].sourceinfo.endtime}}
                   {{ } }}" />
                    </div>
                </from>
            </td>
        </tr>
        <tr>
            <td>申请帧位：</td>
            <td><input type="text" class="form-control input-sm" name="locationsort" placeholder="整数" value="{{if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null){ }}{{=parseInt(it[0].sourceinfo.locationsort)}}{{ } }}" /></td>
        </tr>
        <tr>
            <td>申请位置：</td>
            <td>
                <label class="checkbox-inline">
                    <input type="checkbox" name="location" id="ck-mob" value="mob" {{ if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null && it[0].sourceinfo.location.indexOf('mob')>=0) { }}
                           checked {{ } }}>mob
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="location" id="ck-pc" value="pc" {{ if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null && it[0].sourceinfo.location.indexOf('pc')>=0) { }}
                           checked
                            {{ } }}>pc
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="location" id="ck-splash" value="splash" {{ if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null && it[0].sourceinfo.location.indexOf('splash')>=0) { }}
                           checked
                            {{ } }}> splash
                </label>
            </td>
        </tr>
        <tr>
            <td>背景：</td>
            <td><textarea class="form-control input-sm" name="info" placeholder="">{{ if( it && it.length >0 && it[0].sourceinfo && it[0].sourceinfo != null) { }}
                    {{=it[0].sourceinfo.info}}
                    {{ } }}</textarea></td>
        </tr>
        </tbody>
    </table>
</script>

<!--时尚度申请 -->
<script id='applyfashiontmpl' type='text/x-dot-template'>
    <table class="table table-bordered">
        <tbody>
        <tr>
            <td class="col-xs-2">文案：</td>
            <td><textarea class="form-control input-sm" name="tips" placeholder="必填项">{{if( it && it.length >0 && it[0].fashioninfo && it[0].fashioninfo != null){ }}{{=it[0].fashioninfo.tips}}{{ } }}</textarea></td>
        </tr>
        <tr>
            <td class="col-xs-2">活动主题：</td>
            <td><textarea class="form-control input-sm" name="styleinfo" placeholder="必填项">{{if( it && it.length >0 && it[0].fashioninfo && it[0].fashioninfo != null) { }}{{=it[0].fashioninfo.styleinfo}}{{ } }}</textarea></td>
        </tr>
        <tr>
            <td class="col-xs-2">活动内容：</td>
            <td><textarea class="form-control input-sm" name="info" placeholder="">{{if( it && it.length >0 && it[0].fashioninfo && it[0].fashioninfo != null) { }}{{=it[0].fashioninfo.info}}
                    {{ } }}</textarea></td>
        </tr>
        </tbody>
    </table>
</script>


<!--微信钱包专用banner申请 -->
<script id='wechatapplybannertmpl' type='text/x-dot-template'>
    <!--<h5>banner申请</h5>-->
    <table class="table table-bordered bannerinfo">
        <thead>
        <tr>
            <th class="col-xs-2">终端</th>
            <th class="col-xs-1">尺寸</th>
            <th class="col-xs-5">banner图</th>
            <th class="col-xs-4">备注</th>
        </tr>
        </thead>
        {{ if( it && it[0].sourceinfo!=null && it[0].sourceinfo['location'].indexOf('mob')>=0) { }}
        <tbody class="banner_mob">
        <tr>
            <td class="col-xs-2" rowspan="3">banner图（mob）</td>
            <td class="col-xs-1">
                750*360
            </td>
            <td class="col-xs-5">
                <!--dom结构部分-->
                <div class="uploader-demo">
                    <!--用来存放item-->
                    <div class="fileList uploader-list row">
                        {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['banner_mob'] && it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]) { }}
                        <div class="file-item thumbnail upload-state-done">
                            <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]["n_pic_file"]}}"><img src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]["n_pic_file"]}}" /></a>
                            <input type="hidden" class="picinfo" value="" data='{{=JSON.stringify(it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"])}}' pickey="home_Top_Banner_6_6" pictitle="banner_mob">
                        </div>
                        {{ } }}
                    </div>
                    <div class="btns row">
                        <!--<div class="filePicker col-xs-4" key="home_Top_Banner_6_6" title="banner_mob">选择图片</div>-->
                        <!--<div class="col-xs-3"><button class="btn btn-default removebtn">删除图片</button></div>-->
                    </div>
                </div>

            </td>
            <td class="col-xs-4">
                   <textarea placeholder="备注" class="form-control input-sm">{{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['banner_mob'] && it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]) { }} {{=it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]["info"]}}
                       {{ } }}</textarea>
            </td>
        </tr>
        </tbody>
        {{ } }}
        {{ if( it && it[0].sourceinfo!=null && it[0].sourceinfo['location'].indexOf('pc')>=0) { }}
        <tbody class="banner_pc">
        <tr>
            <td class="col-xs-2">banner图（pc）</td>
            <td class="col-xs-1">960*420</td>
            <td class="col-xs-5">
                <!--dom结构部分-->
                <div class="uploader-demo">
                    <!--用来存放item-->
                    <div class="fileList uploader-list row">
                        {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['banner_pc'] && it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]) { }}
                        <div class="file-item thumbnail upload-state-done">
                            <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]["n_pic_file"]}}"><img src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]["n_pic_file"]}}" /></a>
                            <input type="hidden" class="picinfo" value="" data='{{=JSON.stringify(it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"])}}' pickey="web_welcome_top_banner_carousel" pictitle="banner_pc">
                        </div>
                        {{ } }}

                    </div>
                    <div class="btns row">
                        <!--<div class="filePicker col-xs-4" key="web_welcome_top_banner_carousel" title="banner_pc">选择图片</div>-->
                        <!--<div class="col-xs-3"><button class="btn btn-default removebtn">删除图片</button></div>-->
                    </div>
                </div>
            </td>
            <td class="col-xs-4">
                <textarea class="form-control input-sm">{{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['banner_pc'] && it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]) { }}
                    {{=it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]["info"]}}
                    {{ } }}
                </textarea>
            </td>
        </tr>
        </tbody>
        {{ } }}
        {{ if( it && it[0].sourceinfo!=null && it[0].sourceinfo['location'].indexOf('splash')>=0) { }}
        <tbody class="splash_mob">
        <tr>
            <td class="col-xs-2" rowspan="3">splash（mob）</td>
            <td class="col-xs-1">
                640*960
            </td>
            <td class="col-xs-5">
                <!--dom结构部分-->
                <div class="uploader-demo">
                    <!--用来存放item-->
                    <div class="fileList uploader-list row">
                        {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]) { }}
                        <div class="file-item thumbnail upload-state-done">
                            <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]["n_pic_file"]}}"><img src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]["n_pic_file"]}}" /></a>
                            <input type="hidden" class="picinfo" value="" data='{{=JSON.stringify(it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"])}}' pickey="pic_for_iphone" pictitle="Activity_splash_global">
                        </div>
                        {{ } }}

                    </div>
                    <div class="btns row">
                        <!--<div class="filePicker col-xs-4" key="pic_for_iphone" title="Activity_splash_global">选择图片</div>-->
                        <!--<div class="col-xs-3"><button class="btn btn-default removebtn">删除图片</button></div>-->
                    </div>
                </div>
            </td>
            <td class="col-xs-4">
                 <textarea class="form-control input-sm">
                    {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]) { }}
                     {{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]["info"]}}
                     {{ } }}
                </textarea>
            </td>
        </tr>
        <tr>
            <td class="col-xs-1">
                640*1136
            </td>
            <td class="col-xs-5">
                <!--dom结构部分-->
                <div class="uploader-demo">
                    <!--用来存放item-->
                    <div class="fileList uploader-list row">
                        {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]) { }}
                        <div class="file-item thumbnail upload-state-done">
                            <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]["n_pic_file"]}}"><img src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]["n_pic_file"]}}" /></a>
                            <input type="hidden" class="picinfo" value="" data='{{=JSON.stringify(it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"])}}' pickey="pic_for_iphone5" pictitle="Activity_splash_global">
                        </div>
                        {{ } }}
                    </div>
                    <div class="btns row">
                        <!--<div class="filePicker col-xs-4" key="pic_for_iphone5" title="Activity_splash_global">选择图片</div>-->
                        <!--<div class="col-xs-3"><button class="btn btn-default removebtn">删除图片</button></div>-->
                    </div>
                </div>
            </td>
            <td class="col-xs-4">
                <textarea class="form-control input-sm">
                    {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]) { }}
                    {{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]["info"]}}
                    {{ } }}
                </textarea>
            </td>
        </tr>
        <tr>
            <td class="col-xs-1">
                720*1280
            </td>
            <td class="col-xs-5">
                <!--dom结构部分-->
                <div class="uploader-demo">
                    <!--用来存放item-->
                    <div class="fileList uploader-list row">
                        {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_android"]) { }}
                        <div class="file-item thumbnail upload-state-done">
                            <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_android"]["n_pic_file"]}}"><img src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_android"]["n_pic_file"]}}" /></a>
                            <input type="hidden" class="picinfo" value="" data='{{=JSON.stringify(it[0].bannerinfo['Activity_splash_global']["pic_for_android"])}}' pickey="pic_for_android" pictitle="Activity_splash_global">
                        </div>
                        {{ } }}
                    </div>
                    <div class="btns row">
                        <!--<div class="filePicker col-xs-4" key="pic_for_android" title="Activity_splash_global">选择图片</div>-->
                        <!--<div class="col-xs-3"><button class="btn btn-default removebtn">删除图片</button></div>-->
                    </div>
                </div>
                </div>
            </td>
            <td class="col-xs-4">
                 <textarea class="form-control input-sm">
                    {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_android"]) { }}
                     {{=it[0].bannerinfo['Activity_splash_global']["pic_for_android"]["info"]}}
                     {{ } }}
                </textarea>
            </td>
        </tr>
        </tbody>
        {{ } }}
    </table>
</script>


<!--banner申请 -->
<script id='applybannertmpl' type='text/x-dot-template'>
    <!--<h5>banner申请</h5>-->
    <table class="table table-bordered bannerinfo">
        <thead>
        <tr>
            <th class="col-xs-2">终端</th>
            <th class="col-xs-1">尺寸</th>
            <th class="col-xs-5">banner图</th>
            <th class="col-xs-4">备注</th>
        </tr>
        </thead>
        {{ if( it && it[0].sourceinfo!=null && it[0].sourceinfo['location'].indexOf('mob')>=0) { }}
        <tbody class="banner_mob">
        <tr>
            <td class="col-xs-2" rowspan="3">banner图（mob）</td>
            <td class="col-xs-1">
                750*360
            </td>
            <td class="col-xs-5">
                <!--dom结构部分-->
                <div class="uploader-demo">
                    <!--用来存放item-->
                    <div class="fileList uploader-list row">
                        {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['banner_mob'] && it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]) { }}
                        <div class="file-item thumbnail upload-state-done">
                            <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]["n_pic_file"]}}"><img src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]["n_pic_file"]}}" /></a>
                            <input type="hidden" class="picinfo" value="" data='{{=JSON.stringify(it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"])}}' pickey="home_Top_Banner_6_6" pictitle="banner_mob">
                        </div>
                        {{ } }}
                    </div>
                    <div class="btns row">
                        <div class="filePicker col-xs-4" key="home_Top_Banner_6_6" title="banner_mob">选择图片</div>
                        <!--<div class="col-xs-3"><button class="btn btn-default removebtn">删除图片</button></div>-->
                    </div>
                </div>

            </td>
            <td class="col-xs-4">
                   <textarea placeholder="备注" class="form-control input-sm">{{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['banner_mob'] && it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]) { }} {{=it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]["info"]}}
                       {{ } }}</textarea>
            </td>
        </tr>

        </tbody>
        {{ } }}
        {{ if( it && it[0].sourceinfo!=null && it[0].sourceinfo['location'].indexOf('pc')>=0) { }}
        <tbody class="banner_pc">
        <tr>
            <td class="col-xs-2">banner图（pc）</td>
            <td class="col-xs-1">960*420</td>
            <td class="col-xs-5">
                <!--dom结构部分-->
                <div class="uploader-demo">
                    <!--用来存放item-->
                    <div class="fileList uploader-list row">
                        {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['banner_pc'] && it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]) { }}
                        <div class="file-item thumbnail upload-state-done">
                            <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]["n_pic_file"]}}"><img src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]["n_pic_file"]}}" /></a>
                            <input type="hidden" class="picinfo" value="" data='{{=JSON.stringify(it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"])}}' pickey="web_welcome_top_banner_carousel" pictitle="banner_pc">
                        </div>
                        {{ } }}

                    </div>
                    <div class="btns row">
                        <div class="filePicker col-xs-4" key="web_welcome_top_banner_carousel" title="banner_pc">选择图片</div>
                        <!--<div class="col-xs-3"><button class="btn btn-default removebtn">删除图片</button></div>-->
                    </div>
                </div>
            </td>
            <td class="col-xs-4">
                <textarea class="form-control input-sm">{{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['banner_pc'] && it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]) { }}
                    {{=it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]["info"]}}
                    {{ } }}
                </textarea>
            </td>
        </tr>
        </tbody>
        {{ } }}
        {{ if( it && it[0].sourceinfo!=null && it[0].sourceinfo['location'].indexOf('splash')>=0) { }}
        <tbody class="splash_mob">
        <tr>
            <td class="col-xs-2" rowspan="3">splash（mob）</td>
            <td class="col-xs-1">
                640*960
            </td>
            <td class="col-xs-5">
                <!--dom结构部分-->
                <div class="uploader-demo">
                    <!--用来存放item-->
                    <div class="fileList uploader-list row">
                        {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]) { }}
                        <div class="file-item thumbnail upload-state-done">
                            <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]["n_pic_file"]}}"><img src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]["n_pic_file"]}}" /></a>
                            <input type="hidden" class="picinfo" value="" data='{{=JSON.stringify(it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"])}}' pickey="pic_for_iphone" pictitle="Activity_splash_global">
                        </div>
                        {{ } }}

                    </div>
                    <div class="btns row">
                        <div class="filePicker col-xs-4" key="pic_for_iphone" title="Activity_splash_global">选择图片</div>
                        <!--<div class="col-xs-3"><button class="btn btn-default removebtn">删除图片</button></div>-->
                    </div>
                </div>
            </td>
            <td class="col-xs-4">
                 <textarea class="form-control input-sm">
                    {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]) { }}
                     {{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]["info"]}}
                     {{ } }}
                </textarea>
            </td>
        </tr>
        <tr>
            <td class="col-xs-1">
                640*1136
            </td>
            <td class="col-xs-5">
                <!--dom结构部分-->
                <div class="uploader-demo">
                    <!--用来存放item-->
                    <div class="fileList uploader-list row">
                        {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]) { }}
                        <div class="file-item thumbnail upload-state-done">
                            <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]["n_pic_file"]}}"><img src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]["n_pic_file"]}}" /></a>
                            <input type="hidden" class="picinfo" value="" data='{{=JSON.stringify(it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"])}}' pickey="pic_for_iphone5" pictitle="Activity_splash_global">
                        </div>
                        {{ } }}
                    </div>
                    <div class="btns row">
                        <div class="filePicker col-xs-4" key="pic_for_iphone5" title="Activity_splash_global">选择图片</div>
                        <!--<div class="col-xs-3"><button class="btn btn-default removebtn">删除图片</button></div>-->
                    </div>
                </div>
            </td>
            <td class="col-xs-4">
                <textarea class="form-control input-sm">
                    {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]) { }}
                    {{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]["info"]}}
                    {{ } }}
                </textarea>
            </td>
        </tr>
        <tr>
            <td class="col-xs-1">
                720*1280
            </td>
            <td class="col-xs-5">
                <!--dom结构部分-->
                <div class="uploader-demo">
                    <!--用来存放item-->
                    <div class="fileList uploader-list row">
                        {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_android"]) { }}
                        <div class="file-item thumbnail upload-state-done">
                            <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_android"]["n_pic_file"]}}"><img src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_android"]["n_pic_file"]}}" /></a>
                            <input type="hidden" class="picinfo" value="" data='{{=JSON.stringify(it[0].bannerinfo['Activity_splash_global']["pic_for_android"])}}' pickey="pic_for_android" pictitle="Activity_splash_global">
                        </div>
                        {{ } }}
                    </div>
                    <div class="btns row">
                        <div class="filePicker col-xs-4" key="pic_for_android" title="Activity_splash_global">选择图片</div>
                        <!--<div class="col-xs-3"><button class="btn btn-default removebtn">删除图片</button></div>-->
                    </div>
                </div>
                </div>
            </td>
            <td class="col-xs-4">
                 <textarea class="form-control input-sm">
                    {{ if( it && it[0].bannerinfo!=null && it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_android"]) { }}
                     {{=it[0].bannerinfo['Activity_splash_global']["pic_for_android"]["info"]}}
                     {{ } }}
                </textarea>
            </td>
        </tr>
        </tbody>
        {{ } }}
    </table>
</script>

<!--上线申请 -->
<script id='applyonlinetmpl' type='text/x-dot-template'>
    <table class="table table-bordered onlineinfo">
        <tbody>
        {{ if( it && it[0].sourceinfo!=null && it[0].sourceinfo['location'].indexOf('mob')>=0) { }}
        <tr class="mob">
            <td class="col-xs-2">mob端链接：</td>
            <td class="col-xs-10">
                <div class="addtype">
                    {{if( it && it[0].onlineinfo!=null && it[0].onlineinfo.mob.moburl){ }}
                    <input type="hidden" value='{{=it[0].onlineinfo.mob.moburl}}' class="form-control input-sm" name="url" id="url" />
                    {{ } }}
                </div>
                <input type="hidden" value='{{if( it && it[0].onlineinfo!=null && it[0].onlineinfo.mob.url_type){ }}{{=it[0].onlineinfo.mob.url_type}}{{ } }}' name="url_type" id="url_type"/>
                <input type="hidden" value='{{if( it && it[0].onlineinfo!=null && it[0].onlineinfo.mob.url_params){ }}{{=it[0].onlineinfo.mob.url_params}}{{ } }}' name='url_params' id="url_params"/>
            </td>
        </tr>
        {{  } }}
        {{ if( it && it[0].sourceinfo!=null && it[0].sourceinfo['location'].indexOf('pc')>=0) { }}
        <tr class="pc">
            <td class="col-xs-2">pc端链接：</td>
            <td class="col-xs-10">
                <input type="text" value="{{if( it && it[0].onlineinfo!=null && it[0].onlineinfo['pc']){ }} {{=it[0].onlineinfo['pc']['url']}} {{ } }}" class="form-control input-sm" name="pcurl" />
            </td>
        </tr>
        {{  } }}
        {{ if( it && it[0].sourceinfo!=null && it[0].sourceinfo['location'].indexOf('splash')>=0) { }}
        <tr class="splash">
            <td class="col-xs-2">splash端链接：</td>
            <td class="col-xs-10">
                <input type="text" value="{{if( it && it[0].onlineinfo!=null && it[0].onlineinfo['splash']){ }} {{=it[0].onlineinfo['splash']['url']}} {{ } }}" class="form-control input-sm" name="splashurl" />
            </td>
        </tr>
        {{  } }}
        </tbody>
    </table>
</script>

<!--<h5>资源位申请的信息</h5>-->
<script id='applyinfotmpl' type='text/x-dot-template'>
    {{if(it && it.length>0) { }}
    {{ if( it[0].sourceinfo && it[0].sourceinfo != null) { }}
    <table class="table table-bordered sourceinfo">
        <tbody>
        <tr>
            <td class="col-xs-2">活动名称：</td>
            <td class="col-xs-4">{{=it[0].sourceinfo.active_name}}</td>
            <td class="col-xs-2">商品类目：</td>
            <td class="col-xs-4">{{=it[0].sourceinfo.product_categroy}}</td>
        </tr>
        <tr>
            <td class="col-xs-2">投放开始时间：</td>
            <td class="col-xs-4">{{=it[0].sourceinfo.starttime}}</td>
            <td class="col-xs-2">投放结束时间：</td>
            <td class="col-xs-4">{{=it[0].sourceinfo.endtime}}</td>
        </tr>
        <tr>
            <td class="col-xs-2">申请帧位：</td>
            <td class="col-xs-4">{{=it[0].sourceinfo.locationsort}}</td>
            <td class="col-xs-2">申请位置：</td>
            <td class="col-xs-4">{{=it[0].sourceinfo.location}}</td>
        </tr>
        <tr>
            <td class="col-xs-2">背景：</td>
            <td class="col-xs-10" colspan="3">{{=it[0].sourceinfo.info}}</td>
        </tr>
        </tbody>
    </table>
    {{ } }}

    {{ if( it[0].fashioninfo && it[0].fashioninfo != null && it[0].status != 1 ) { }}

    <table class="table table-bordered fashioninfo">
        <tbody>
        <tr>
            <td class="col-xs-2">文案：</td>
            <td class="col-xs-4">{{=it[0].fashioninfo.tips}}</td>
            <td class="col-xs-2">活动主题：</td>
            <td class="col-xs-4">{{=it[0].fashioninfo.styleinfo}}</td>
        </tr>
        <tr>
            <td class="col-xs-2">活动内容：</td>
            <td class="col-xs-10" colspan="3">{{=it[0].fashioninfo.info}}</td>
        </tr>
        </tbody>
    </table>
    {{ } }}
    {{ if( it[0].bannerinfo && it[0].bannerinfo != null && (it[0].status >=3 || it[0].status ==-1 || it[0].status ==0 ) ) { }}

    <table class="table table-bordered bannerinfo bannerpic">
        <thead>
        <tr>
            <th class="col-xs-2">终端</th>
            <th class="col-xs-1">尺寸</th>
            <th class="col-xs-5">banner图</th>
            <th class="col-xs-4">备注</th>
        </tr>
        </thead>
        <tbody>
        {{ if( it[0].sourceinfo!=null && it[0].sourceinfo['location'].indexOf('mob')>=0) { }}
        <tr>
            <td class="col-xs-2">banner图（mob）</td>
            <td class="col-xs-1">
                750*360
            </td>
            <td class="col-xs-7">
                {{ if( it[0].bannerinfo['banner_mob'] && it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]) { }}
                <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]["n_pic_file"]}}"><img style="width:720px; height:440px;" src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]["n_pic_file"]}}"  /></a>
                <input type="hidden" value='{{=JSON.stringify(it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"])}}' />
                {{ } }}
            </td>
            <td class="col-xs-2">

                {{ if(it[0].bannerinfo['banner_mob'] && it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]) { }}
                {{=it[0].bannerinfo['banner_mob']["home_Top_Banner_6_6"]["info"]}}
                {{ } }}
            </td>
        </tr>
        {{  } }}
        {{ if( it[0].sourceinfo!=null && it[0].sourceinfo['location'].indexOf('pc')>=0) { }}
        <tr>
            <td class="col-xs-2">banner图（pc）</td>
            <td class="col-xs-1">960*320</td>
            <td class="col-xs-5">
                {{ if( it[0].bannerinfo['banner_pc'] && it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]!=undefined ) { }}
                <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]["n_pic_file"]}}"><img style="width:720px; height:315px;" src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]["n_pic_file"]}}"  /></a>
                <input type="hidden" value='{{=JSON.stringify(it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"])}}' />
                {{ } }}
            </td>
            <td class="col-xs-4">
                {{ if( it[0].bannerinfo['banner_pc'] && it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]!=undefined) { }}
                {{=it[0].bannerinfo['banner_pc']["web_welcome_top_banner_carousel"]["info"]}}
                {{ } }}

            </td>
        </tr>
        {{  } }}
        {{ if( it[0].sourceinfo!=null && it[0].sourceinfo['location'].indexOf('splash')>=0) { }}
        <tr>
            <td class="col-xs-2" rowspan="3">splash（mob）</td>
            <td class="col-xs-1">
                640*960
            </td>
            <td class="col-xs-5">
                {{ if( it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]!=undefined ) { }}
                <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]["n_pic_file"]}}"><img style="width:640px; height:960px;" src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]["n_pic_file"]}}"  /></a>
                <input type="hidden" value='{{=JSON.stringify(it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"])}}' />
                {{ } }}
            </td>
            <td class="col-xs-4">
                {{ if( it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]!=undefined) { }}
                {{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone"]["info"]}}
                {{ } }}

            </td>
        </tr>
        <tr>
            <td class="col-xs-1">
                640*1136
            </td>
            <td class="col-xs-5">
                {{ if( it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]!=undefined ) { }}
                <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]["n_pic_file"]}}"><img style="width:640px; height:1136px;" src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]["n_pic_file"]}}"  /></a>
                <input type="hidden" value='{{=JSON.stringify(it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"])}}' />
                {{ } }}
            </td>
            <td class="col-xs-4">
                {{ if( it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]!=undefined) { }}
                {{=it[0].bannerinfo['Activity_splash_global']["pic_for_iphone5"]["info"]}}
                {{ } }}

            </td>
        </tr>
        <tr>
            <td class="col-xs-1">
                720*1280
            </td>
            <td class="col-xs-5">
                {{ if( it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_android"]!=undefined ) { }}
                <a target="_blank" href="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_android"]["n_pic_file"]}}"><img style="width:720px; height:1280px;" src="http://imgtest.meiliworks.com/{{=it[0].bannerinfo['Activity_splash_global']["pic_for_android"]["n_pic_file"]}}"  /></a>
                <input type="hidden" value='{{=JSON.stringify(it[0].bannerinfo['Activity_splash_global']["pic_for_android"])}}' />
                {{ } }}
            </td>
            <td class="col-xs-4">
                {{ if( it[0].bannerinfo['Activity_splash_global'] && it[0].bannerinfo['Activity_splash_global']["pic_for_android"]!=undefined) { }}
                {{=it[0].bannerinfo['Activity_splash_global']["pic_for_android"]["info"]}}
                {{ } }}
            </td>
        </tr>
        {{ } }}
        </tbody>
    </table>
    {{ } }}

    {{ if( it[0].onlineinfo && it[0].onlineinfo != null && (it[0].status >=4 || it[0].status ==-1 || it[0].status == 0)) { }}
    <table class="table table-bordered onlineinfo">
        <tbody>
        {{if(it[0].onlineinfo.mob){ }}
        <tr>
            <td class="col-xs-2">mob端链接：</td>
            <td class="col-xs-10">
                {{ if(it[0].onlineinfo.mob.mob_islianjie == 1){ }}
                <a target="_blank" href="{{=it[0].onlineinfo.mob.moburl}}">{{=it[0].onlineinfo.mob.moburl}}</a>
                {{ } else { }}
                {{=it[0].onlineinfo.mob.moburl}}
                {{ } }}
            </td>
            <input type="hidden" value='{{if( it && it[0].onlineinfo!=null && it[0].onlineinfo.mob.url_type){ }}{{=it[0].onlineinfo.mob.url_type}}{{ } }}' name="url_type" id="url_type"/>
            <input type="hidden" value='{{if( it && it[0].onlineinfo!=null && it[0].onlineinfo.mob.url_params){ }}{{=it[0].onlineinfo.mob.url_params}}{{ } }}' name='url_params' id="url_params"/>
        </tr>
        {{ } }}
        {{if(it[0].onlineinfo.pc){ }}
        <tr>
            <td class="col-xs-2">pc端链接：</td>
            <td class="col-xs-10"><a target="_blank" href="{{=it[0].onlineinfo.pc.pcurl}}">{{=it[0].onlineinfo.pc.pcurl}}</a></td>
        </tr>
        {{ } }}
        {{if(it[0].onlineinfo.splash){ }}
        <tr>
            <td class="col-xs-2">splash端链接：</td>
            <td class="col-xs-10"><a target="_blank" href="{{=it[0].onlineinfo.splash.splashurl}}">{{=it[0].onlineinfo.splash.splashurl}}</a></td>
        </tr>
        {{ } }}
        </tbody>
    </table>
    {{ } }}

    {{ if( it[0].replyinfo && it[0].replyinfo != null && it[0].replyinfo.length >0) { }}

    <h6>审核信息：</h6><br/>
    <span>{{if(it[0].status==6 || it[0].status==-1 || it[0].status==5){ }}
        {{=it[0].replyinfo[0].reply_info}}
        {{ } }}</span>
    <br/>
    <table class="table table-bordered replyinfo">
        <thead>
        <tr><th>审核人</th><th>审核状态</th><th>审核信息</th><th>审核时间</th></tr>
        </thead>
        <tbody>
        {{ for(var i = 0; i < it[0].replyinfo.length; i++){ }}
        <tr><td>{{=it[0].replyinfo[i].name}}</td><td>{{=it[0].replyinfo[i].flag}}</td>
            <td>
                {{if(it[0].replyinfo[i].reply_info != undefined){ }}
                {{if(it[0].status!=6 && it[0].status!=-1 && it[0].status!=5){ }}
                {{=it[0].replyinfo[i].reply_info}}
                {{ } }}
                {{ } }}</td>
            <td>{{if(it[0].replyinfo[i].time != undefined){ }}
                {{=it[0].replyinfo[i].time}}
                {{ } }}</td></tr>
        {{ } }}
        </tbody>
    </table>
    {{ } }}


    {{ } }}



</script>


<!--资源已下线 data数据打通的功能-->
<script id="listdataovertmpl" type='text/x-dot-template'>

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
            <th>昨天数据</th>
            <th>查看数据</th>
        </tr>
        </thead>
        <tfoot></tfoot>
        <tbody class="tablelist datalist">
        {{if (it && it.length>0) { }}
        {{~it.datalist:val:index}}
        <tr>
            <!--<td>{{=val.id}}</td>-->
            <td class="activename">
                <a href="javascript:void(0)" data-toggle="tooltip" class="activename" title="{{=val.fashioninfostr}}">{{=val.active_name}}</a>
            </td>
            <td>{{=val.product_categroy}}</td>
            <td>{{=val.locationsort}}</td>
            <td width="120">
                {{if (val.imgurl != undefined && val.imgurl != "") { }}
                <a target='_blank' href='http://imgtest.meiliworks.com/{{=val.imgurl}}'>
                    <img width='120' src='http://imgtest.meiliworks.com/{{=val.imgurl}}' /></a>
                {{ } else { }}
                暂无
                {{ } }}
                {{ if (val.outurl != undefined && $val.outurl != "") { }}
                <br/><a target='_blank' href='{{=val.outurl}}'>查看活动链接</a>
                {{ } }}
            </td>
            <td>{{=val.starttime}}<br/>{{=val.endtime}}</td>
            <td>{{=val.creater}}</td>
            <td>{{=val.create_time}}</td>
            <td>{{=val.locationstr}}</td>
            <td>{{=val.statusname}}</td>
            <td>
                <a href="/report/showreport/2177?title_desc=团购入仓-品质优选&edate=2015-11-30">查看数据</a>
            </td>
        </tr>
        {{ } else { }}
        <tr><td colspan="11"> <span class="redcolor">暂无数据 </span></td></tr>
        {{ } }}
        </tbody>
    </table>
</script>