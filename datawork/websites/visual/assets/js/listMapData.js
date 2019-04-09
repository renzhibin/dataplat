/* 即时过滤列表页 */
$(function () {
    var dataHandle;
    var currentPageNum = 1, pageSize = 20; // 当前页数,每页信息个数
    var totalPageNum = Math.ceil(data.length / pageSize) || 1; // 总页数
    var $findFilterData = $("#find-filter-data");

    $findFilterData.on({
        keyup: function (e) {
            fnFindFilterData(e);
        }
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
        var listHtml = "";
        if (data && data.length > 0) {
            for (var i = 0; i < data.length; i++) {
                var index = i + 1, dataContent = data[i].map_data;
                if (dataContent === "") {
                    dataContent = "空";
                }
                var liHtml = "<tr title='过滤条件' data-container='body' data-toggle='popover' data-placement='bottom' data-content=' " + dataContent + "'>";
                var editurl = "/tool/MapData?type=edit&mapkey=" + data[i].map_key;
                var viewurl = "/tool/MapData?type=view&mapkey=" + data[i].map_key;
                liHtml += "<td>" + index + "</td><td>" + data[i].map_name + "</td><td>" + data[i].map_key + "</td><td>" + data[i].creater + "</td><td>" + data[i].updater + "</td><td><a href='" + viewurl + "' class='btn btn-default btn-sm button-oprtation'>查看</a>"+"<a href='" + editurl + "' class='btn btn-default btn-sm button-oprtation'>编辑</a>"+"</td>";
                liHtml += "</tr>";
                listHtml += liHtml;
            }
        } else {
            listHtml = "<tr><td colspan='6' style='text-align: center'>没有数据</td></tr>";
        }
        $(".table-list").find(".item-list").html(listHtml);
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
                if ((data[i].map_key.indexOf(inf) > -1) || (data[i].map_name.indexOf(inf) > -1) || (data[i].map_data.indexOf(inf) > -1) || (data[i].updater.indexOf(inf) > -1) || (data[i].creater.indexOf(inf) > -1)) {
                    resultInf.push(data[i]);
                }
            }
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

// 若每次不重新生成页码,要进行的操作也不少,so,每次点击页码还是再次生成
    /*     var pageItem=$(".pagination li a")||[];
     */
});
