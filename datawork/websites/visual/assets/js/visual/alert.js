var Alert = {
    tpl: [
        '<div class="weui_dialog_alert ls-alert-dialog" style="display: none;">',
        '<div class="weui_mask"></div>',
        '<div class="weui_dialog">',
        '<div class="weui_dialog_hd"><strong class="weui_dialog_title">提示</strong></div>',
        '<div class="weui_dialog_bd">',
        'alert',
        '</div>',
        '<div class="weui_dialog_ft">',
        '<a class="weui_btn_dialog primary confirm">好</a>',
        '</div>',
        '</div>',
        '</div>'
    ].join(''),
    show: function (content, onConfirm) {
        var that = this;
        var title = '提示';
        var confirmText = '好';
        //参数兼容: 既支持配置html,又支持配置options
        if(content && typeof content == 'object') {
            var options = content;
            onConfirm = options.onConfirm;
            title = options.title || title;
            confirmText = options.confirmText || confirmText;
            content = options.content || '';
        }
        if (!that.$alert) {
            that.$alert = $(this.tpl).appendTo("body");
        }
        that.$alert.off('.alert');   //析构掉
        that.$alert.on('click.alert', '.confirm', function (e) {
            that.$alert.hide();
            that.$alert.off('.alert');   //析构掉
            if(onConfirm && typeof onConfirm == 'function') {
                onConfirm();
            }
        });
        that.$alert.show().find(".weui_dialog_bd").html(content);
        that.$alert.show().find(".weui_dialog_title").html(title);
        that.$alert.show().find(".confirm").html(confirmText);
    },

    hide: function () {
        if (!this.$alert) {
            this.$alert = $(this.tpl).appendTo("body");
        }
        this.$alert.hide();
    }
};
