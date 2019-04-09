var page = require('webpage').create();
var args = require('system').args;

page.viewportSize = {width: 1024,
    height: 20}

var url =  args[1];
var filename = args[2];
page.open(url, function (status) {
    if (status !== 'success') {
        console.log(false);
        phantom.exit();
    } else {
        window.setTimeout(function () {
            page.render(filename);
            console.log(true);
            phantom.exit();
        }, 4000);
    }
});

page.onResourceReceived = function(resource) {
    if (resource.url == url&&resource.status!=200) {
        console.log('url:'+resource.url+'错误,'+resource.status);
        phantom.exit();
    }
};