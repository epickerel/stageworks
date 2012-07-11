jQuery(function(){

	boxMargin = parseInt( jQuery('.mg_box').css('margin-left') );
	boxBorder = parseInt( jQuery('.mg_box').css('border-left-width') );
	imgPadding = parseInt( jQuery('.img_wrap').css('padding-left') );

	$item_content = jQuery('#mg_overlay_content');

	mg_sizes = new Array(
		'1_1',
		'1_2',
		
		'1_3',
		'2_3',
		
		'1_4',
		'3_4',
		
		'1_5',
		'2_5',
		'3_5',
		'4_5',
		
		'1_6',
		'5_6' 
	);
	
	// first init
	jQuery(document).ready(function() {
		jQuery('.mg_container').each(function() {
			var cont_id = jQuery(this).attr('id');
			
			$container = jQuery( '#' + cont_id );
			size_boxes('.mg_box');
			masonerize(cont_id);
			
			// fallback for IE
			if(	!Modernizr.csstransitions ) {
				mg_ie_fallback();	
			}
		});
		
		// when img loaded, display
		jQuery('.mg_container').addClass('lcwp_loading');
		jQuery('.mg_container .thumb').imagesLoaded(function() {
			var a = 0;
			jQuery('.mg_container > div').each(function() {
				jQuery(this).delay(150*a).animate({opacity: 1}, 400);	
				a = a+1;
			});
			jQuery('.mg_container').removeClass('lcwp_loading');
		});
	});
	
	// dynamic $container
	jQuery('.mg_grid_wrap').live('click', function(){
		$container = jQuery(this).children();
	});


	// masonry init
	window.masonerize = function(cont_id) {
		$container = jQuery( '#' + cont_id );
		
		$container.masonry({
			isAnimated: true,
			columnWidth: 1,
			itemSelector: '.mg_box:visible'
		});
		return true;	
	};


	// functions to re-resize
	window.get_size = function(shape) {
		switch(shape) {
		  case '5_6': var perc = 0.83; break;
		  case '1_6': var perc = 0.166; break;
		  
		  case '4_5': var perc = 0.80; break;
		  case '3_5': var perc = 0.60; break;
		  case '2_5': var perc = 0.40; break;
		  case '1_5': var perc = 0.20; break;
		  
		  case '3_4': var perc = 0.75; break;
		  case '1_4': var perc = 0.25; break;
		  
		  case '2_3': var perc = 0.6666666; break;
		  case '1_3': var perc = 0.3333333; break;
		  
		  case '1_2': var perc = 0.50; break;
		  default : var perc = 1; break;
		}
		return perc; 	
	};
	
	
	window.reresize_w = function() {
		jQuery.each(mg_sizes, function(key, val) {
			if( $target.hasClass('col' + val) )	{ wsize = ($mg_r_width - 0.2) * get_size(val);}
		});
		
		var wsize = Math.round(parseFloat(wsize)) - (boxMargin * 2) - (boxBorder * 2);
		if (wsize%2 != 0) {wsize = wsize - 1;}
		
		return wsize;
	};
	
	
	window.reresize_h = function() {
		jQuery.each(mg_sizes, function(key, val) {
			if( $target.hasClass('row' + val) )	{ hsize = ($mg_r_width - 0.2) * get_size(val);}
		});							
		
		var hsize = Math.floor(parseFloat(hsize)) - (boxMargin * 2) - (boxBorder * 2); 
		if (hsize%2 != 0) {hsize = hsize - 1;}
		
		return hsize;
	};
	
	
	window.get_box_perc = function(axis) {
		if(axis == 'w') {var aclass = 'col';}
		else {var aclass = 'row';}
		
		$mg_sizes.each(function(key, val) {
			if( $target.hasClass(aclass + val) ) { return (get_size(val) * 100);}
		});	
	};	

	
	window.perc_to_px = function(size, with_other) {
		var px = parseFloat( (get_size(size) * $container.width()) - (boxBorder * 2) );
		
		if( with_other === undefined ) { return px; }		
		else { return px - (imgPadding * 2) - (boxMargin * 2); }
	};
	
	
	window.img_wrap_rs = function(axis) {
		if(axis == 'w') {
			var size = reresize_w() - (imgPadding * 2);
		}		
		else {
			var size = reresize_h() - (imgPadding * 2);
		}
		return parseFloat(size).toFixed(3); 
	};


	window.size_boxes = function(target) {
		jQuery(target).each(function(index) {
			$target = jQuery(this);

			if( $target.parent().attr('rel') == 'auto' ) {$mg_r_width = $target.parent().width();}
			else {$mg_r_width = parseInt($target.parent().attr('rel'));}

			// boxes
			jQuery(this).css('width', reresize_w() + 'px');
			jQuery(this).css('height', reresize_h() + 'px');
			
			// overlays control
			if( reresize_w() < 90 || reresize_h() < 90 ) { jQuery(this).find('.cell_type').hide(); }
			else {jQuery(this).find('.cell_type').show();}
			
			if( reresize_w() < 60 || reresize_h() < 60 ) { jQuery(this).find('.cell_more').hide(); }
			else {jQuery(this).find('.cell_more').show();}
			
			// image wrappers
			jQuery(this).find('.img_wrap').css('width', (reresize_w() - (imgPadding * 2)) + 'px');
			jQuery(this).find('.img_wrap').css('height', (reresize_h() - (imgPadding * 2)) + 'px');
			
			jQuery(this).find('.img_wrap > div').css('width', (reresize_w() - (imgPadding * 2)) + 'px');
			jQuery(this).find('.img_wrap > div').css('height', (reresize_h() - (imgPadding * 2)) + 'px');
			jQuery(this).find('.img_wrap > div').css('top', imgPadding + 'px').css('left', imgPadding + 'px');
			
			////////////////////////////////////////////
			// hack for the spacer
			if($target.hasClass('mg_spacer') && boxBorder > 0) {
				$target.css('height', $target.height() + 2 + 'px');
				$target.css('width', $target.width() + 2 + 'px');
				$target.css('border', 'none');
			}
			////////////////////////////////////////////
		});
		return true;	
	};
	
	
	// IE transitions fallback
	window.mg_ie_fallback = function() {
		jQuery('.mg_box .overlays').children().hide();
		
		jQuery('.mg_box .img_wrap').hover(
			function() {
				jQuery(this).find('.overlays').children().hide();
				jQuery(this).find('.overlays').children().fadeIn('fast');
			}
		);
	};


	/////////////////////////////


	// open item
	jQuery('.mg_closed').live('click', function(){
		var pid = jQuery(this).attr('rel').substr(4);
		$mg_sel_grid = jQuery(this).parent().attr('id');
		
		jQuery('#mg_full_overlay_wrap, #mg_full_overlay').fadeIn();
		
		mg_get_item_content(pid);
	});
	
	
	// get item content
	function mg_get_item_content(pid) {
		var cur_url = jQuery(location).attr('href');	
		var data = {
			mg_type: 'mg_overlay_layout',
			pid: pid
		};

		jQuery('#mg_full_overlay .mg_item_load').fadeIn();
		jQuery.post(cur_url, data, function(response) {
			jQuery('#mg_full_overlay .mg_item_load').fadeOut();
			$item_content.html(response).fadeIn();
			$item_content.css("margin-top", ( jQuery(window).scrollTop() + 60) + "px");
			
			
			// navigator
			mg_grid_items_nav(pid);
			
			// functions for slider and players
		 	mg_slider();
			mg_resize_video();
			mg_lazyload();
		});

		return true;
	}
	
	
	// create the navigator of visible items of a defined grid for opened items
	function mg_grid_items_nav(selected) {
		mg_grid_items = new Array();
		mg_count = new Array();
		mgc = 0;
		
		jQuery('#'+ $mg_sel_grid +' .mg_transitions:visible').each(function() {
			if( jQuery(this).attr('rel') != undefined ) {
            	var iid = jQuery(this).attr('rel').substr(4);
				
				if(iid == selected) {mg_curr = mgc;}
				
				mg_grid_items.push({
					id: iid, 
					title:  jQuery(this).find('.mg_overlay_tit').text()
				});
				
				mg_count.push(iid);
				mgc = mgc + 1;
			}
        });	
		
		var items_num = mg_count.length;
		
		if(mg_curr == 0) {
			var prev = '';		
			
			if(items_num == 1) {var next = '';}
			else {
				var next = '<div class="mg_nav_next" id="mg_nav_'+ mg_grid_items[1]['id'] +'"><span rel="'+ mg_grid_items[1]['title'] +'"></span></div>';
			}
		}
		else if(mg_curr == (items_num - 1)) {
			var index = mg_curr - 1;
			var prev = '<div class="mg_nav_prev" id="mg_nav_'+ mg_grid_items[index]['id'] +'"><span rel="'+ mg_grid_items[index]['title'] +'"></span></div>';
			
			var next = '';
		}
		else {
			var index = mg_curr - 1;
			var prev = '<div class="mg_nav_prev" id="mg_nav_'+ mg_grid_items[index]['id'] +'"><span rel="'+ mg_grid_items[index]['title'] +'"></span></div>';
			
			var index = mg_curr + 1;
			var next = '<div class="mg_nav_next" id="mg_nav_'+ mg_grid_items[index]['id'] +'"><span rel="'+ mg_grid_items[index]['title'] +'"></span></div>';	
		}
		
		jQuery('#mg_nav').prepend(prev).append(next + '<p><span></span></p>');
	}
	
	
	// next / prev titles show
	jQuery('#mg_nav .mg_nav_next, #mg_nav .mg_nav_prev').live('hover', function(){
		var tit = jQuery(this).children().attr('rel');
		jQuery('#mg_nav p span').fadeIn().html(tit);
	});
	
	
	// switch item
	jQuery('.mg_nav_prev, .mg_nav_next').live('click', function(){
		var pid = jQuery(this).attr('id').substr(7);
		
		jQuery('#mg_overlay_content > div').fadeOut();	
		$item_content.hide().empty();	
		mg_get_item_content(pid);
	});
	
	
	// switch item - keyboards events
	jQuery(document).keydown(function(e){
		if( jQuery('#mg_overlay_content #mg_close').size() > 0 ) {
			var items_num = mg_count.length;

			// prev
			if (e.keyCode == 37) {
				if(items_num > 1 && mg_curr > 0) {
					var ks_id = mg_curr - 1;
					var pid = mg_grid_items[ks_id]['id'];
					
					jQuery('#mg_overlay_content > div').fadeOut();	
					$item_content.hide().empty();	
					mg_get_item_content(pid);
				}
			}
			
			// next 
			if (e.keyCode == 39) {
				if(items_num > 1 && mg_curr < (items_num - 1)) {
					var ks_id = mg_curr + 1;
					var pid = mg_grid_items[ks_id]['id'];
					
					jQuery('#mg_overlay_content > div').fadeOut();	
					$item_content.hide().empty();	
					mg_get_item_content(pid);
				}
			}
		}
	});
	

	// close item
	jQuery('#mg_close').live('click', function(){
		// prevent jPlayer crash
		if( jQuery('.jp-jplayer').size() > 0 ) {
			jQuery('.jp-jplayer').jPlayer("stop");
			jQuery('.jp-jplayer').jPlayer("destroy");
		}
		
		jQuery('#mg_full_overlay_wrap, #mg_full_overlay').fadeOut();
		$item_content.fadeOut().empty();
	});
	

	// slider
	function mg_slider() {
		if( jQuery('.mg_item_featured #mg_slider').size() > 0 ) {
			jQuery('#mg_slider').wmuSlider({
				animation: 'fade',
				slideshow: false
			});	
		}
	}
	
	
	// resize video 
	function mg_resize_video() {
		if( jQuery('.mg_item_featured iframe').size() > 0 ) {	
			var if_w = jQuery('.mg_item_featured').width();
			var if_h = if_w * 0.56;
			jQuery('.mg_item_featured iframe').attr('width', if_w).attr('height', if_h);
		}	
	}
		
		
	// opened item resizing functions
	function mg_item_resize() {
		mg_resize_video();
	}
		
		
	// on resize
	jQuery(window).smartresize(function(){
		mg_item_resize();
	});
	
	
	// on orientation change
	jQuery('body').bind('orientationchange', function() {
		size_boxes('.mg_box');
		$container.masonry( 'reload' );
    });
	
	
	// lazyload
	function mg_lazyload() {
		if( jQuery(".mg_item_featured > img").size() > 0 ) {
			$ll_img = jQuery('.mg_item_featured > img');
			
			$ll_img.hide();			
			$ll_img.imagesLoaded(function() {
				$ll_img.fadeIn();
				jQuery('.mg_item_featured').css('background', 'none');
				
				// for the mp3 player
				if( jQuery('.jp-audio').size() > 0 )  {
					jQuery('.jp-audio').fadeIn();	
				}
			});	
		}
	}

	
	// cat filter
	jQuery('.mg_filter > a').live('click', function() {
		var gid = jQuery(this).parent().attr('id').substr(4);
		var sel = jQuery(this).attr('rel');
		var cont_id = 'mg_grid_' + gid ;
		
		if(sel == 'all') {
			jQuery('#mg_grid_'+gid+' .mg_box').fadeIn('fast');
			jQuery('#' + cont_id).masonry( 'reload' );
		}
		else {
			jQuery('#mg_grid_'+gid+' .mg_box').fadeOut('fast');
			jQuery('#mg_grid_'+gid+' .mgc_'+sel).fadeIn(function() {
				jQuery('#' + cont_id).masonry( 'reload' );
			});
		}

		jQuery('.mg_filter > a').removeClass('mg_cats_selected');
		jQuery(this).addClass('mg_cats_selected');
	});
	
	
	// adjust opened item position on scroll
	jQuery(window).scroll(function () {
		if( jQuery('#mg_overlay_content').size() > 0 ) {		
			var top_scroll = parseInt( jQuery(document).scrollTop() );
			var top_margin = jQuery('#mg_overlay_content').offset();
			
			var full_top_space = parseInt( (top_margin.top + jQuery('#mg_overlay_content').height()) + 90 - top_scroll );
			var diff = jQuery(window).height() - full_top_space;
			
			// top position
			if(top_scroll < (top_margin.top - 60)) {
				setTimeout(function() {
					$item_content.stop().animate({ marginTop: ( jQuery(window).scrollTop() + 60) + "px" }, 500, 'linear');
				}, 150);
			}
			
			// bottom position for big items
			if(diff > 1 && jQuery(window).height() < (jQuery('#mg_overlay_content').height() + 90) ) {
				setTimeout(function() {
					$item_content.stop().animate({ marginTop: (top_margin.top + diff) + "px" }, 500, 'linear');
				}, 150);
			}
			
			// bottom position for small items
			else if( diff > 1 && jQuery(window).height() > (jQuery('#mg_overlay_content').height() + 90) ) {
				setTimeout(function() {
					$item_content.stop().animate({ marginTop: ( jQuery(window).scrollTop() + 60) + "px" }, 500, 'linear');
				}, 150);
			}
		}
	});
	
});