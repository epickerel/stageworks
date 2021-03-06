(function ($) {
  $.pageQuery = function () {
    var arr, kvp = {}, i, kv;
    arr = window.location.search.substring(1).split('&');
    for (i=0; i<arr.length; i++) {
      kv = arr[i].split('=');
      kvp[decodeURIComponent(kv[0])] = decodeURIComponent(kv[1]);
    }
    return kvp;
  };
}(jQuery));


(function($,sr){

  // debouncing function from John Hann
  // http://unscriptable.com/index.php/2009/03/20/debouncing-javascript-methods/
  var debounce = function (func, threshold, execAsap) {
      var timeout;

      return function debounced () {
          var obj = this, args = arguments;
          function delayed () {
              if (!execAsap)
                  func.apply(obj, args);
              timeout = null;
          };

          if (timeout)
              clearTimeout(timeout);
          else if (execAsap)
              func.apply(obj, args);

          timeout = setTimeout(delayed, threshold || 100);
      };
  }
  // smartresize 
  jQuery.fn[sr] = function(fn){  return fn ? this.bind('resize', debounce(fn)) : this.trigger(sr); };

}(jQuery,'smartresize'));

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


(function ($) {
  // Facebook album covers
  var defaults, mergeAlbumsAndPhotos;
  defaults = {
    outerTemplate: '<ul class="rslides"></ul>' +
      '<span class="chrome1"><b></b><b></b><b></b><b></b></span>',
    template: '{{#items}}\n<li><a href="galleries?aid={{aid}}">' +
      '<span class="imgwrap"><img src="{{src_big}}" alt="{{name}}"></span>' +
      '<span class="title">{{name}}</span></a></li>\n{{/items}}',
    albumFilter: function(albums){
      var i, album, newAlbums = [], re = /\*\*([0-9]*\.?[0-9]*)$/, res, desc;
      for (i=0; i<albums.length; i++) {
        album = albums[i];
        desc = album.description;
        res = re.exec(desc);
        if (res) {
          album.sortOrder = (!res[1].length) ? 100 : Number(res[1]);
          album.description = desc.substring(0, desc.length - res[0].length);
          newAlbums.push(album);
        }
      }
      newAlbums.sort(function(a, b) {
          return a.sortOrder - b.sortOrder;
      });
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
    $el.html(Mustache.to_html(opts.outerTemplate, {}));
    $.ajax({
        url: 'http://graph.facebook.com/fql',
        data: {
          q: JSON.stringify({
            query1: 'SELECT src_big, src, pid FROM photo WHERE pid in (select cover_pid from album where owner = "119186754764635")',
            query2: 'SELECT name, aid, owner, name, description, cover_pid, modified, size FROM album WHERE owner = "119186754764635" ORDER BY created desc'
          })
        },
        success: function (o) {
          var photos, albums, html, $images, firstImg, $ul;
          photos = o.data[0].fql_result_set;
          albums = o.data[1].fql_result_set;
          albums = mergeAlbumsAndPhotos(albums, photos);
          albums = opts.albumFilter(albums);
          html = Mustache.to_html(opts.template, {
            items: albums
          });
          $ul = $el.find('ul:first').html(html);
          $images = $ul.find('img').fitImageTo($ul.width(), $ul.outerHeight());
          firstImg = $images[0];

          $.doWhen(function () {
            return !!firstImg.complete;
          }, function () {
            $el.children('ul:first').responsiveSlides();
            $(window).smartresize(function () {
              $images.fitImageTo($ul.width(), $ul.outerHeight());
            });
          });
        },
        dataType: 'jsonp'
    });
    return this;
  };
}(jQuery));


(function ($) {
  //
  var defaults = {
    template:
      '<div class="fbalbum"><h1>{{name}}</h1>' +
        '<p>{{{description}}}</p>' +
        '<ul>\n{{#photos}}<li>' +
          '<a href="{{src_big}}" style="background-image:url({{thumbnail.source}});">' +
            '<img src="{{thumbnail.source}}" ref="{{src_big}}">' +
          '</a>' +
          '<p style="display:none;">{{caption}}</p>' +
        '</li>{{/photos}}\n</ul>' +
      '</div>'
  };

  function filterImageMaxSize(arr, maxW, maxH) {
    var best, i, img;
    for (i = 0; i < arr.length; i += 1) {
      img = arr[i];
      if (img.width <= maxW && img.height <= maxH) {
        if (!best || best.width < img.width || best.height < img.height) {
          best = img;
        }
      }
    }
    return best;
  }

  $.fn.fbAlbum = function (aid, opts) {
    var $el = this;
    opts = $.extend({}, defaults, opts);
    $('body').addClass('fb-album-page');
    $.ajax({
        url: 'http://graph.facebook.com/fql',
        data: {
          q: JSON.stringify({
            query1: 'SELECT pid, images, ' +
              'src_big, src_big_width, src_big_height, ' +
              'src, src_width, src_height, caption, position ' +
              'FROM photo WHERE aid="' + aid + '"',
            query2: 'SELECT name, photo_count, owner, name, description, cover_pid, ' +
              'modified, size, description FROM album WHERE aid="' + aid + '"'
          })
        },
        success: function(o){
          var album, html, $images, $firstImgContext,
            re = /\*\*([0-9]*\.?[0-9]*)$/;
          album = o.data[1].fql_result_set[0];
          album.description = album.description.substr(0, album.description.search(re));
          album.photos = o.data[0].fql_result_set.sort(function(a, b){
            return a.position - b.position;
          });
          $.each(album.photos, function () {
            this.thumbnail = filterImageMaxSize(this.images, 320, 320);
          });
          album.description = album.description.replace('\n', '<br>');
          html = Mustache.to_html(opts.template, album);
          $el.html(html);
          $images = $el.find('img');
          $firstImgContext = $images.eq(0).closest('a');
          //$images.fitImageTo($firstImgContext.width(), $firstImgContext.height());
          $el.find('li > a').photoSwipe();
        },
        dataType: 'jsonp'
    });
  };
}(jQuery));

