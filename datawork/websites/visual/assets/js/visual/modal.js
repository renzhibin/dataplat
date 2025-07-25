/*!
 * Bootstrap v3.3.0 (http://getbootstrap.com)
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 */

/*!
 * Generated using the Bootstrap Customizer (http://v3.bootcss.com/customize/?id=0b771be0ed07b8733867fbde0779da54)
 * Config saved to config.json and https://gist.github.com/0b771be0ed07b8733867fbde0779da54
 */
if ("undefined" == typeof jQuery) throw new Error("Bootstrap's JavaScript requires jQuery"); +
function(t) {
  var e = t.fn.jquery.split(" ")[0].split(".");
  if (e[0] < 2 && e[1] < 9 || 1 == e[0] && 9 == e[1] && e[2] < 1) throw new Error(
    "Bootstrap's JavaScript requires jQuery version 1.9.1 or higher")
}(jQuery), + function(t) {
  "use strict";

  function e(e, i) {
    return this.each(function() {
      var s = t(this),
        n = s.data("bs.modal"),
        r = t.extend({}, o.DEFAULTS, s.data(), "object" == typeof e && e);
      n || s.data("bs.modal", n = new o(this, r)), "string" == typeof e ? n[e](i) : r.show && n
        .show(i)
    })
  }
  var o = function(e, o) {
    this.options = o, this.$body = t(document.body), this.$element = t(e), this.$backdrop = this.isShown =
      null, this.scrollbarWidth = 0, this.options.remote && this.$element.find(".modal-content").load(
        this.options.remote, t.proxy(function() {
          this.$element.trigger("loaded.bs.modal")
        }, this))
  };
  o.VERSION = "3.3.0", o.TRANSITION_DURATION = 300, o.BACKDROP_TRANSITION_DURATION = 150, o.DEFAULTS = {
    backdrop: !0,
    keyboard: !0,
    show: !0
  }, o.prototype.toggle = function(t) {
    return this.isShown ? this.hide() : this.show(t)
  }, o.prototype.show = function(e) {
    var i = this,
      s = t.Event("show.bs.modal", {
        relatedTarget: e
      });
    this.$element.trigger(s), this.isShown || s.isDefaultPrevented() || (this.isShown = !0, this.checkScrollbar(),
      this.$body.addClass("modal-open"), this.setScrollbar(), this.escape(), this.$element.on(
        "click.dismiss.bs.modal", '[data-dismiss="modal"]', t.proxy(this.hide, this)), this.backdrop(
        function() {
          var s = t.support.transition && i.$element.hasClass("fade");
          i.$element.parent().length || i.$element.appendTo(i.$body), i.$element.show().scrollTop(
            0), s && i.$element[0].offsetWidth, i.$element.addClass("in").attr("aria-hidden", !
            1), i.enforceFocus();
          var n = t.Event("shown.bs.modal", {
            relatedTarget: e
          });
          s ? i.$element.find(".modal-dialog").one("bsTransitionEnd", function() {
            i.$element.trigger("focus").trigger(n)
          }).emulateTransitionEnd(o.TRANSITION_DURATION) : i.$element.trigger("focus").trigger(
            n)
        }))
  }, o.prototype.hide = function(e) {
    e && e.preventDefault(), e = t.Event("hide.bs.modal"), this.$element.trigger(e), this.isShown &&
      !e.isDefaultPrevented() && (this.isShown = !1, this.escape(), t(document).off(
          "focusin.bs.modal"), this.$element.removeClass("in").attr("aria-hidden", !0).off(
          "click.dismiss.bs.modal"), t.support.transition && this.$element.hasClass("fade") ?
        this.$element.one("bsTransitionEnd", t.proxy(this.hideModal, this)).emulateTransitionEnd(
          o.TRANSITION_DURATION) : this.hideModal())
  }, o.prototype.enforceFocus = function() {
    t(document).off("focusin.bs.modal").on("focusin.bs.modal", t.proxy(function(t) {
      this.$element[0] === t.target || this.$element.has(t.target).length || this.$element.trigger(
        "focus")
    }, this))
  }, o.prototype.escape = function() {
    this.isShown && this.options.keyboard ? this.$element.on("keydown.dismiss.bs.modal", t.proxy(
      function(t) {
        27 == t.which && this.hide()
      }, this)) : this.isShown || this.$element.off("keydown.dismiss.bs.modal")
  }, o.prototype.hideModal = function() {
    var t = this;
    this.$element.hide(), this.backdrop(function() {
      t.$body.removeClass("modal-open"), t.resetScrollbar(), t.$element.trigger(
        "hidden.bs.modal")
    })
  }, o.prototype.removeBackdrop = function() {
    this.$backdrop && this.$backdrop.remove(), this.$backdrop = null
  }, o.prototype.backdrop = function(e) {
    var i = this,
      s = this.$element.hasClass("fade") ? "fade" : "";
    if (this.isShown && this.options.backdrop) {
      var n = t.support.transition && s;
      if (this.$backdrop = t('<div class="modal-backdrop ' + s + '" />').prependTo(this.$element)
        .on("click.dismiss.bs.modal", t.proxy(function(t) {
          t.target === t.currentTarget && ("static" == this.options.backdrop ? this.$element[
            0].focus.call(this.$element[0]) : this.hide.call(this))
        }, this)), n && this.$backdrop[0].offsetWidth, this.$backdrop.addClass("in"), !e) return;
      n ? this.$backdrop.one("bsTransitionEnd", e).emulateTransitionEnd(o.BACKDROP_TRANSITION_DURATION) :
        e()
    } else if (!this.isShown && this.$backdrop) {
      this.$backdrop.removeClass("in");
      var r = function() {
        i.removeBackdrop(), e && e()
      };
      t.support.transition && this.$element.hasClass("fade") ? this.$backdrop.one(
        "bsTransitionEnd", r).emulateTransitionEnd(o.BACKDROP_TRANSITION_DURATION) : r()
    } else e && e()
  }, o.prototype.checkScrollbar = function() {
    this.scrollbarWidth = this.measureScrollbar()
  }, o.prototype.setScrollbar = function() {
    var t = parseInt(this.$body.css("padding-right") || 0, 10);
    this.scrollbarWidth && this.$body.css("padding-right", t + this.scrollbarWidth)
  }, o.prototype.resetScrollbar = function() {
    this.$body.css("padding-right", "")
  }, o.prototype.measureScrollbar = function() {
    if (document.body.clientWidth >= window.innerWidth) return 0;
    var t = document.createElement("div");
    t.className = "modal-scrollbar-measure", this.$body.append(t);
    var e = t.offsetWidth - t.clientWidth;
    return this.$body[0].removeChild(t), e
  };
  var i = t.fn.modal;
  t.fn.modal = e, t.fn.modal.Constructor = o, t.fn.modal.noConflict = function() {
    return t.fn.modal = i, this
  }, t(document).on("click.bs.modal.data-api", '[data-toggle="modal"]', function(o) {
    var i = t(this),
      s = i.attr("href"),
      n = t(i.attr("data-target") || s && s.replace(/.*(?=#[^\s]+$)/, "")),
      r = n.data("bs.modal") ? "toggle" : t.extend({
        remote: !/#/.test(s) && s
      }, n.data(), i.data());
    i.is("a") && o.preventDefault(), n.one("show.bs.modal", function(t) {
      t.isDefaultPrevented() || n.one("hidden.bs.modal", function() {
        i.is(":visible") && i.trigger("focus")
      })
    }), e.call(n, r, this)
  })
}(jQuery);
