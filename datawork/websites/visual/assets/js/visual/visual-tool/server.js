/**
 * 异步获取数据方法
 * @param {string} url - 接口地址
 * @param {string} type - 请求类型 "GET" or "POST"
 * @param {object} opts.params - 请求参数
 * @param {function} opts.succCall - 请求成功时的回调函数
 * @param {function} opts.errCall - 请求失败时的回调函数，选填
 * @param {object} opts.others - 请求需要用到的其他参数
 * @param {function} opts.others.params - 请求完成后需要传递的参数，选填
 * @param {function} opts.others.context - 回调上下文，选填
 */
function $http(url, type, opts) {
  $.ajax({
      url: url,
      type: type,
      dataType: 'JSON',
      data: opts.params || {}
    })
    .done(function(response) {
      if (response && response.status === 0) {
        opts.succCall(response.data, opts.others);
      } else if (response && response.resultList) {
        opts.succCall(response.resultList, opts.others);
      } else {
        errorHandler(opts.errCall, response);
      }
    })
    .fail(function() {
      errorHandler(opts.errCall);
    });
}

function errorHandler(errCall, response) {
  if ($.isFunction(errCall)) {
    errCall(response);
  } else {
    Alert.show("数据获取失败!");
  }
}
