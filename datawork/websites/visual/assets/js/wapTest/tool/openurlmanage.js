/* 外链管理页 */
var MODAL_TPL = [
  '<form class="form-horizontal" role="form">',
      '<fieldset class="fieldset">',
          '{{if urlValue.id}}',
              '<div class="form-group">',
                  '<label for="urlId" class="col-sm-3 control-label">id</label>',
                  '<div class="col-sm-8">',
                      '<input type="text" class="form-control" id="urlId" value="{{urlValue.id}}" disabled>',
                  '</div>',
              '</div>',
          '{{/if}}',
          '<div class="form-group">',
              '<label for="name" class="col-sm-3 control-label">外链名称</label>',
              '<div class="col-sm-8">',
                  '<input type="text" class="form-control" id="urlName" value="{{urlValue.name}}" placeholder="名称">',
              '</div>',
          '</div>',
          '<div class="form-group">',
              '<label for="urlAddress" class="col-sm-3 control-label">外链URL</label>',
              '<div class="col-sm-8">',
                  '<input type="text" class="form-control" id="urlAddress" value="{{urlValue.url}}" placeholder="例如 http://www.baidu.com">',
              '</div>',
          '</div>',
          '<div class="form-group">',
              '<label for="description" class="col-sm-3 control-label">描述</label>',
              '<div class="col-sm-8">',
                  '<textarea class="form-control description" id="urlDesc" value="{{urlValue.desc}}" rows="5" placeholder="外链描述">{{urlValue.desc}}</textarea>',
              '</div>',
          '</div>',
      '</fieldset>',
  '</form>'
].join("");

var LIST_TPL = [
  '{{if itemList.length >0}}',
      '{{each itemList as item index}}',
          '<tr class="url-item" data-container="body" data-toggle="popover" data-placement="bottom" data-content="{{item.url}}">',
              '<td class="url-index">{{index + 1}}</td>',
              '<td class="url-id">{{item.id || "--"}}</td>',
              '<td class="url-name">{{item.name || "--"}}</td>',
              '<td class="url-url">{{item.url || "--"}}</td>',
              '<td class="url-desc">{{item.desc || "--"}}</td>',
              '<td class="url-edit"><div class="btn btn-primary edit" onclick="editClick(this)">编辑</div></td>',
          '</tr>',
      '{{/each}}',
  '{{else}}',
      '<tr><td colspan="6" style="text-align: center">没有数据</td></tr>',
  '{{/if}}'
].join('');

var REG = /(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:/~\+#]*[\w\-\@?^=%&amp;/~\+#])?/;

$(function () {
    var dataHandle;
    var currentPageNum = 1, pageSize = 20; // 当前页数,每页信息个数
    var totalPageNum = Math.ceil(data.length / pageSize) || 1; // 总页数
    // 筛选输入框
    var $findFilterData = $("#find-filter-data");

    // 新增管理外链页Dom
    var $addItem = $(".add-item");

    // 提交编辑/新增管理Dom
    var $submit = $(".submit");

    // 事件绑定
    // 筛选框事件绑定
    $findFilterData.on({
        keyup: function (e) {
            fnFindFilterData(e);
        }
    });

    // 新增外链管理事件绑定
    $addItem.on('click', function(event) {
        var modal = $("#myModal");
        modal.find('.modal-body').html(template.compile(MODAL_TPL)({
              urlValue:{

              }
        }));
    });

    $submit.on("click",function(event){
        // location.reload();
        // 处理提交后事项
        submitHandle();
    });

// 当总页数大于1时,需要分页,需要获取当前所在页数
    if (totalPageNum > 1) {
        renderPagination(currentPageNum, totalPageNum);
        dataHandle = sliceDataByPage(data, currentPageNum) || [];
        renderFilterHtml(dataHandle);
        bindEventHandle(data, totalPageNum);
    } else {
        renderFilterHtml(data);
    }

// 根据当前页数分割数据并渲染
// data:全部数据, currentPage:当前所在页数
    function sliceDataByPage(data, currentPage) {
        data = data.slice((currentPage - 1) * pageSize, currentPage * pageSize);
        return data;
    }

//生成即时过滤数据对应的HTML片段
    function renderFilterHtml(data) {
        $(".table-list").find(".item-list").html(template.compile(LIST_TPL)({
            itemList:data
        }));
        $("[data-toggle='popover']").popover({trigger: "hover", html: true});
    }

    // 根据输入的mapkey查询数据
    function fnFindFilterData(e) {
        var inf = $(e.target).val() || "";
        var totalPageNumFind = 1, currentPageFind = 1; // 查询结果的总页数,当前在查询结果的第几页
        var resultInf = [], resultInfHandle = []; // 查询结果
        $(".pagination").html("");
        if (inf && inf.length > 0) {
            for (var i = 0; i < data.length; i++) {
                if ((data[i].name.indexOf(inf) > -1) || (data[i].desc.indexOf(inf) > -1) || (data[i].url.indexOf(inf) > -1  || data[i]["id"].indexOf(inf) > -1) ) {
                    resultInf.push(data[i]);
                }
            }
        }else{
            resultInf = data;
        }
        totalPageNumFind = Math.ceil(resultInf.length / pageSize) || 1; // 总页数
        if (totalPageNumFind > 1) {
            publicOperation(resultInf, currentPageFind, totalPageNumFind);
        } else {
            renderFilterHtml(resultInf);
        }
    }

    function bindEventHandle(resultInf, totalPageNumFind) {
        $(".page-num-first").on('click', function () {
            var currentPage = 1;
            publicOperation(resultInf, currentPage, totalPageNumFind);
        });

        $(".page-num-last").on('click', function () {
            var currentPage = totalPageNumFind;
            publicOperation(resultInf, currentPage, totalPageNumFind);
        });

        $(".page-num-prev").on('click', function () {
            var currentPage = parseInt($("#current-page-selected").text()) || 1;
            currentPage = currentPage - 1;
            if (currentPage < 1) {
                currentPage = 1;
            }
            publicOperation(resultInf, currentPage, totalPageNumFind);
        });

        $(".page-num-next").on('click', function () {
            var currentPage = parseInt($("#current-page-selected").text()) || 1;
            currentPage = currentPage + 1;
            if (currentPage > totalPageNumFind) {
                currentPage = totalPageNumFind;
            }
            publicOperation(resultInf, currentPage, totalPageNumFind);
        });

        $(".page-num").on('click', function (event) {
            var currentPage = parseInt($(event.target).text()) || 1;
            publicOperation(resultInf, currentPage, totalPageNumFind);
        });

    }

    function publicOperation(resultInf, currentPage, totalPageNumFind) {
        var resultInfHandle = sliceDataByPage(resultInf, currentPage);
        renderPagination(currentPage, totalPageNumFind);
        renderFilterHtml(resultInfHandle);
        bindEventHandle(resultInf, totalPageNumFind);
    }

    // 对搜索结果内容进行分页,和普通的分页略有不同。之后会将两者合二为一
    function renderPagination(currentPage, totalNum) {
        var currentPage = currentPage || 1, totalNum = totalNum, ulHtml = "", currentPageSelected = "", clickablePrev = "", clickableNext = "";
        var prev = currentPage - 1, next = currentPage + 1;
        if (prev < 1) {
            prev = 1;
        }
        if (next > totalNum) {
            next = totalNum;
        }
        if (currentPage == 1) {
            clickablePrev = "btn-not-allowed";
        }
        if (currentPage == totalNum) {
            clickableNext = "btn-not-allowed";
        }

        ulHtml += '<li><a class="first-page page-num-first ' + clickablePrev + '">第一页</a></li>' +
            '  <li><a class="prev-page page-num-prev ' + clickablePrev + '">上一页</a></li>';
        for (var i = 0; i < totalNum; i++) {
            var currentNum = i + 1;
            if (currentPage == currentNum) {
                currentPageSelected = "current-page-selected";
            } else {
                currentPageSelected = "";
            }
            ulHtml += '<li><a class="page-num" id="' + currentPageSelected + '">' + currentNum + '</a></li>';
        }
        ulHtml += '<li><a class="next-page page-num-next ' + clickableNext + ' ">下一页</a></li>' +
            '<li><a class="last-page page-num-last ' + clickableNext + ' ">最后一页</a></li>';
        $(".pagination").html(ulHtml);
    }

    // 提交编辑/新增处理
    function submitHandle(){
        var requestData = {};
        var urlId = $("#urlId").val();
        var urlName = $.trim($("#urlName").val());
        var urlAddress = $.trim($("#urlAddress").val());
        var urlDesc = $.trim($("#urlDesc").val());
        if(urlName === "" ||  urlAddress === ""){
            alert("名称和URL为必填内容,请调整后重试!");
        }else if(!REG.test(urlAddress)){
            alert("您输入的url地址不符合规范,请调整后再试!");
        }else{
          requestData = {
              name:urlName,
              url:urlAddress,
              desc:urlDesc
          };
          if(urlId !== undefined){
              requestData.id = urlId;
          }
          syncData(requestData);
        }
    }



// 若每次不重新生成页码,要进行的操作也不少,so,每次点击页码还是再次生成
    /*     var pageItem=$(".pagination li a")||[];
     */
});

// 编辑外链管理事件绑定
function editClick(target){
    var index = $('.edit').index(target);
    var urlValue = {
        id:$($('.url-id')[index]).html(),
        name:$($('.url-name')[index]).html(),
        desc:$($('.url-desc')[index]).html(),
        url:$($('.url-url')[index]).html()
    };
    var modal = $("#myModal");
    modal.find('.modal-body').html(template.compile(MODAL_TPL)({
          urlValue:urlValue
    }));
    modal.modal('show');
}


function syncData(params){
  $.ajax({
      url: '/tool/OperOpenUrl',
      type: "POST",
      dataType: 'JSON',
      data:params
  })
  .done(function(response) {
      if(response.status === 0){
          alert("提交成功!");
          location.reload();
      }else{
        alert(response.msg);
      }
  })
  .fail(function() {
      console.log("error!");
  });
}
