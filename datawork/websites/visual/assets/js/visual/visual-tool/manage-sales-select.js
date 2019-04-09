var TPLS = {
  manager: [
    '<div class="visual-tool-manager">',
    '<label>选择主管:',
    '<select class="field-control sm select-manager" name="f_uid">',
    '</select>',
    '</label>',
    '</div>'
  ].join(""),
  sales: [
    '<div class="visual-tool-sales">',
    '<label>选择销售:',
    '<select class="field-control sm select-sales" name="uid">',
    '<option value="all">全部</option>',
    '</select>',
    '</label>',
    '</div>'
  ].join("")
};

/**
 * 主管销售列表渲染
 * @param {Object} domList 主管列表插入节点组
 * @param {jQueryDom} domList.$f_dom 主管列表插入节点组
 * @param {jQueryDom} domList.$dom 销售列表插入节点组
 * @param {Object} mapTool 初始化后的mapTool对象
 * @param {Object} events 需要绑定的事件组
 * @param {Object} events.manager 主管列表需要绑定的事件
 * @param {Object} events.sales 销售列表需要绑定的事件
 * @param {number} type 地域组件类型 0不需要"全部"选项 1需要"全部"选项
 */
var ManagerAndSalesSelect = function(domList, events, mapTool, type) {
  this.domList = domList;
  this.mapTool = mapTool;
  this.events = events;
  // 通过类型判断是否需要附加 "全部" 选项
  this.type = type;
  this.MANAGER_SALES_LIST = null;
  this.init();
  return this;
};
ManagerAndSalesSelect.prototype = {
  init: function() {
    var that = this;
    that.domList.$f_dom.html(TPLS.manager);
    that.domList.$dom.html(TPLS.sales);
    that.getData();
  },
  appendList: function() {
    var that = this;
    var zoneId = $(".select-zone").val() || "all";
    that.appendManagerList(zoneId);
  },
  appendManagerList: function(zoneId) {
    var managerList = [];
    var $manager = $(".select-manager");
    if (zoneId === "all") {
      managerList = ['<option value="all">全部</option>'];
    } else {
      $.each(this.MANAGER_SALES_LIST[zoneId], function(index, item) {
        var option = "<option value='" + item.uid + "'>" + item.sales_name + "</option>";
        managerList.push(option);
      });
      if (this.type === 1) {
        managerList.unshift('<option value="all">全部</option>');
      } else {
        this.appendSalesList(zoneId, this.MANAGER_SALES_LIST[zoneId][0].uid);
      }
    }
    $manager.html(managerList.join(""));
  },
  appendSalesList: function(zoneId, managerId) {
    var that = this;
    var salesList = [];
    var $sales = $(".select-sales");
    if (managerId === "all") {
      salesList.push('<option value="all">全部</option>');
    } else {
      $.each(that.MANAGER_SALES_LIST[zoneId], function(index, item) {
        if (item.uid === managerId) {
          if (item[managerId] && item[managerId].length > 0) {
            $.each(item[managerId], function(index, item) {
              var option = "<option value='" + item.uid + "'>" + item.sales_name +
                "</option>";
              salesList.push(option);
            });
            if (that.type === 1) {
              salesList.unshift('<option value="all">全部</option>');
            }
          } else {
            salesList.push('<option value="all">全部</option>');
          }
          return false;
        }
      });
    }
    $sales.html(salesList.join(""));
  },
  bindEvents: function() {
    var that = this;
    $(".visual-tool-manager").on("change", ".select-manager", function(e) {
      var zoneId = $(".select-zone").val();
      var managerId = $(e.target).val();
      that.appendSalesList(zoneId, managerId);
    });
  },
  getData: function() {
    var that = this;
    $http("/getapi/Getmanagerandsaler", "GET", {
      succCall: this.formatData,
      others: {
        context: that
      }
    });
  },
  formatData: function(list, others) {
    var that = others.context;
    that.MANAGER_SALES_LIST = list;
    that.appendList();
    that.bindEvents();
  }

};
