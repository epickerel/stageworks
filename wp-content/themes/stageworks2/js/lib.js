(function($){
  var defaults, tick, start;
  defaults = {
    interval: 100
  };
  tick = function(iVars){
    if (iVars.test.call(iVars.context || window, iVars.data)) {
      clearInterval(iVars.iid);
      iVars.cb.call(iVars.context || window, iVars.data);
    }
  };
  start = function(iVars){
    iVars.iid = setInterval(function(){
      tick(iVars);
    }, iVars.interval);
  };
  $.doWhen = function(test, cb, cfg){
    start($.extend({
      test: test,
      cb: cb
    }, defaults, cfg));
  };
}(jQuery));

(function ($) {
  $.fn.fitImageTo = function(width, height){
    var i, single, testReady, onReady;
    testReady = function (img) {
      return !!img.complete;
    };
    onReady = function (img) {
      var w = img.width, h = img.height,
        multW = width/w, multH = height/h,
        style = img.style,
        hByW = multW * h,
        wByH = multH * w;
      if (hByW >= height) {
        img.width = width;
        img.height = hByW;
        style.marginTop = Math.round((hByW - height) / -2) + 'px';
      } else {
        img.height = height;
        img.width = wByH;
        style.marginLeft = Math.round((wByH - width) / -2) + 'px';
      }
      $(img).addClass('loaded');
    };
    single = function (img) {
      $.doWhen(testReady, onReady, {
        data: img
      });
    };
    for (i=0; i<this.length; i++) {
      single(this[i]);
    }
  };
}(jQuery));
