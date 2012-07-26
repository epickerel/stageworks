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
    return this;
  };
}(jQuery));

(function ($) {
  $.makeCounter = function(max, loop){
    var count = 0;
    return {
      next: function(testOnly){
        var c = count + 1;
        if (c > max) {
          if (loop) {
            c = 0;
          } else {
            return false;
          }
        }
        if (!testOnly) {
          count = c;
        }
        return c;
      },
      prev: function(testOnly){
        var c = count - 1;
        if (c < 0) {
          if (loop) {
            c = max;
          } else {
            return false;
          }
        }
        if (!testOnly) {
          count = c;
        }
        return c;
      },
      left: function(){
        return max - count;
      }
    };
  }
  $.makeTimer = function(interval, iterations, fn, onCompleteOrLoop){
    //if (typeof window.console !== 'undefined') {window.console.log('timer(', period, iterations, fn, onCompleteOrLoop, ')');}
    var paused = false, start,
      onComplete, loop = onCompleteOrLoop === true,
      counter = $.makeCounter(iterations-1, loop),
      currentStartTs;
    if (!loop) {
      onComplete = onCompleteOrLoop;
    }
    start = function(){
      var ts = new Date().getTime();
      currentStartTs = ts;
      setTimeout(function(){
        var count;
        if (!paused && ts === currentStartTs) {
          count = counter.next();
          //console.log('tick', count);
          if (count === false) {
            if (onComplete) {
              onComplete();
            }
          } else {
            fn(count);
            start();
          }
        }
      }, interval);
    };
    start();
    return {
      pause: function(){
        paused = true;
      },
      resume: function(){
        if (paused) {
          paused = false;
          start(true);
        }
      },
      prev: function(iteration){
        this.pause();
        var count = counter.prev();
        //console.log('count', count);
        this.resume();
        return count;
      },
      next: function(iteration){
        this.pause();
        var count = counter.next();
        //console.log('count', count);
        this.resume();
        return count;
      }
    };
  }
}(jQuery));


(function($){
  var defaults, mergeAlbumsAndPhotos;
  defaults = {
    template: '<ul>{{#items}}\n<li><a href="galleries?aid={{aid}}">' +
      '<span class="imgwrap"><img src="{{src_big}}" alt="{{title}}"></span>' +
      '<span class="title">{{name}}</span></a></li>\n{{/items}}</ul>' +
      '<span class="chrome1"><b></b><b></b><b></b><b></b></span>',
    albumFilter: function(albums){
      var i, album, newAlbums = [];
      for (i=0; i<albums.length; i++) {
        album = albums[i];
        if (album.name !== 'Cover Photos' && album.name !== 'Wall Photos'
            && album.name.indexOf('Crew') === -1) {
          newAlbums.push(album);
        }
      }
      return newAlbums;
    }
  };
  mergeAlbumsAndPhotos = function(albums, photos){
    var i, photosByPid = {}, albumsMerged = [], album, albumMerged;
    for (i=0; i<photos.length; i++) {
      photosByPid[photos[i].pid] = photos[i];
    }
    for (i=0; i<albums.length; i++) {
      album = albums[i];
      if (photosByPid[album.cover_pid]) {
        albumMerged = $.extend({}, album, photosByPid[album.cover_pid]);
        albumMerged.src_big = albumMerged.src_big || albumMerged.src;
        albumsMerged.push(albumMerged);
      }
    }
    return albumsMerged;
  };
  $.fn.FBGallery = function (opts) {
    var $el = this;
    opts = $.extend({}, defaults, opts);
    $.ajax({
        url: 'http://graph.facebook.com/fql',
        data: {
          q: JSON.stringify({
            query1: 'SELECT src_big, src, pid FROM photo WHERE pid in (select cover_pid from album where owner = "119186754764635")',
            query2: 'SELECT name, aid, owner, name, description, cover_pid, modified, size FROM album WHERE owner = "119186754764635"'
          })
        },
        success: function(o){
          var photos, albums, html, $images, firstImg;
          photos = o.data[0].fql_result_set;
          albums = o.data[1].fql_result_set;
          albums = mergeAlbumsAndPhotos(albums, photos);
          albums = opts.albumFilter(albums);
          html = Mustache.to_html(opts.template, {
            items: albums
          });
          $el.html(html);
          $images = $el.find('img').fitImageTo(600, 300);
          firstImg = $images[0];
          $.doWhen(function () {
            return !!firstImg.complete;
          }, function () {
            var lastCount = 0, timer,
              $lis = $el.find('li');
            $lis.eq(0).addClass('active');
            timer = $.makeTimer(6000, $lis.length,
              function (count) {
                var $lastLi = $lis.eq(lastCount),
                  $li = $lis.eq(count);
                lastCount = count;
                $li.addClass('active');
                $lastLi.addClass('fading').removeClass('active');
                setTimeout(function(){
                  $lastLi.removeClass('fading');
                }, 1000);
              }, true);
          });
        },
        dataType: 'jsonp'
    });
  };
}(jQuery));
