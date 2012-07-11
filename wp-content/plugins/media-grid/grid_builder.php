<?php require_once(MG_DIR . '/functions.php'); ?>

<div class="wrap lcwp_form">  
	<div class="icon32"><img src="<?php echo MG_URL.'/img/mg_icon.png'; ?>" alt="mediagrid" /><br/></div>
    <?php echo '<h2 class="lcwp_page_title" style="border: none;">' . __( 'Grid Builder', 'lcwp_ml') . "</h2>"; ?>  

	<div id="ajax_mess"></div>
	
    
    <div id="poststuff" class="metabox-holder has-right-sidebar" style="overflow: hidden;">
    	
        <?php // SIDEBAR ?>
        <div id="side-info-column" class="inner-sidebar">
          <form class="form-wrap">	
           
            <div id="add_grid_box" class="postbox lcwp_sidebox_meta">
            	<h3 class="hndle">Add Grid</h3> 
				<div class="inside">
                  <div class="misc-pub-section-last">
					<label>Grid Name</label>
                	<input type="text" name="mg_cells_margin" value="" id="add_grid" maxlenght="100" style="width: 180px;" />
                    <input type="button" name="add_grid_btn" id="add_grid_btn" value="Add" class="button-primary" style="width: 30px; margin-left: 5px;" />
                  </div>  
                </div>
            </div>
            
            <div id="man_grid_box" class="postbox lcwp_sidebox_meta">
            	<h3 class="hndle">Grid List</h3> 
				<div class="inside"></div>
            </div>
            
            <div id="save_grid_box" class="postbox lcwp_sidebox_meta" style="display: none; background: none; border: none;">
            	<input type="button" name="save-grid" value="Save The Grid" class="button-primary" />
                <div style="width: 30px; padding: 0 0 0 7px; float: right;"></div>
            </div>
          </form>	
            
        </div>
    	
        <?php // PAGE CONTENT ?>
        <form class="form-wrap" id="grid_items_list">  
          <div id="post-body">
          <div id="post-body-content" class="mg_grid_content">
              <p>Select a grid ..</p>
          </div>
          </div>
        </form>
        
        <br class="clear">
    </div>
    
</div>  

<?php // SCRIPTS ?>
<script src="<?php echo MG_URL; ?>/js/functions.js" type="text/javascript"></script>
<script src="<?php echo MG_URL; ?>/js/chosen/chosen.jquery.min.js" type="text/javascript"></script>
<script src="<?php echo MG_URL; ?>/js/jquery.masonry.min.js" type="text/javascript"></script>

<script type="text/javascript" charset="utf8" >
jQuery(document).ready(function($) {
	
	// var for the selected grid
	mg_sel_grid = 0;
	
	mg_load_grids();

	boxMargin = 0;
	imgPadding = 0;
	imgBorder = 0;
	
	
	// items dropdown thumbnails toggle
	jQuery('#mh_grid_item').live("change", function() {
		var sel = jQuery(this).val();
		
		jQuery('.mg_dd_items_preview img').hide();
		jQuery('.mg_dd_items_preview img').each(function() {
			if( jQuery(this).attr('alt') == sel ) {jQuery(this).fadeIn();}
		});	
	});
	
	
	// add item
	jQuery('#add_item_btn').live("click", function() {
		var new_item_id = jQuery('#mh_grid_item').val();	
		
		var data = {
			action: 'mg_add_item_to_builder',
			item_id: new_item_id
		};
		
		jQuery('#add_item_btn div').html('<div style="height: 30px;" class="lcwp_loading"></div>');
		
		jQuery.post(ajaxurl, data, function(response) {
			if( jQuery('#visual_builder_wrap ul .mg_box').size() == 0 ) {jQuery('#visual_builder_wrap ul').empty();}
			
			jQuery('#add_item_btn div').empty();
			jQuery('#visual_builder_wrap ul').prepend( response );

			size_boxes('.mg_box');
			$container.masonry( 'reload' );
		});
	});
	
	
	// remove item
	jQuery('.del_item').live("click", function() {
		if(confirm('Remove the item?')) {
			jQuery(this).parent().parent().fadeOut('fast', function() {
				$container.masonry( 'remove', jQuery(this) );
				$container.masonry( 'reload' );	
			});
		}
	});
	
	
	// items cat choose
	jQuery('#mh_grid_cats').live("change", function() {
		var item_cats = jQuery(this).val();	
		var data = {
			action: 'mg_item_cat_posts',
			item_cats: item_cats
		};
		
		jQuery('.mg_dd_items_preview').remove();
		jQuery('#terms_posts_list').html('<div style="height: 30px;" class="lcwp_loading"></div>');
		
		jQuery.post(ajaxurl, data, function(response) {
			if( jQuery.trim(response) != '0' ) {
			
				var data = jQuery.parseJSON(response);

				jQuery('#terms_posts_list').html(data.dd);
				jQuery('#add_item_btn').parent().prepend(data.img);
				
				jQuery('#add_item_btn').fadeIn();
				
				mg_live_chosen();
				masonerize();
			}
			else {
				jQuery('#terms_posts_list').html('<span>No items found ..</span>');
				jQuery('#add_item_btn').fadeOut();	
				
				if( jQuery('.mg_dd_items_preview').size() > 0 ) {
					jQuery('.mg_dd_items_preview').fadeOut(function() {
						jQuery(this).remove();	
					});
				}
			}
		});	
	});
	
	
	// save the grid
	jQuery('#save_grid_box input').live('click', function() {
		var items_list = jQuery.makeArray();
		var items_width = jQuery.makeArray();
		var items_height = jQuery.makeArray();
		
		// catch data
		jQuery('#visual_builder_wrap .mg_box').each(function() {
			var item_id = jQuery(this).children('input').val();
            items_list.push(item_id);
			
			var w = jQuery(this).find('.select_w').val();
            items_width.push(w);
			
			var h = jQuery(this).find('.select_h').val();
            items_height.push(h);
        });
		
		// ajax
		var data = {
			action: 'mg_save_grid',
			grid_id: mg_sel_grid,
			items_list: items_list,
			items_width: items_width,
			items_height: items_height
		};
		
		jQuery('#save_grid_box div').html('<div style="height: 30px;" class="lcwp_loading"></div>');
		
		jQuery.post(ajaxurl, data, function(response) {
			var resp = jQuery.trim(response); 
			
			jQuery('#save_grid_box div').empty();
			
			if(resp == 'success') {
				jQuery('#ajax_mess').empty().append('<div class="updated"><p><strong>Grid saved</strong></p></div>');	
				mg_hide_wp_alert();
			}
			else {
				jQuery('#ajax_mess').empty().append('<div class="error"><p>'+resp+'</p></div>');
			}
		});	
	});
	
	
	// select the grid
	jQuery('#man_grid_box input[type=radio]').live('click', function() {
		mg_sel_grid = parseInt(jQuery(this).val());
		var grid_title = jQuery(this).parent().siblings('.mg_grid_tit').text();

		jQuery('.mg_grid_content').html('<div style="height: 30px;" class="lcwp_loading"></div>');

		var data = {
			action: 'mg_grid_builder',
			grid_id: mg_sel_grid 
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			jQuery('.mg_grid_content').html(response);
			
			// add the title
			jQuery('.mg_grid_content > h2').html(grid_title);
			
			// savegrid box
			jQuery('#save_grid_box').fadeIn();
			
			mg_live_chosen();
			mg_live_ip_checks();
			
			masonerize();
			size_boxes('.mg_box');
			$container.masonry( 'reload' );	
		});	
	});
	
	
	// add grid
	jQuery('#add_grid_btn').click(function() {
		var grid_name = jQuery('#add_grid').val();
		
		if( jQuery.trim(grid_name) != '' ) {
			var data = {
				action: 'mg_add_grid',
				grid_name: grid_name
			};
			
			jQuery.post(ajaxurl, data, function(response) {
				var resp = jQuery.trim(response); 
				
				if(resp == 'success') {
					jQuery('#ajax_mess').empty().append('<div class="updated"><p><strong>Grid added</strong></p></div>');	
					jQuery('#add_grid').val('');
					
					mg_load_grids();
					mg_hide_wp_alert();
				}
				else {
					jQuery('#ajax_mess').empty().append('<div class="error"><p>'+resp+'</p></div>');
				}
			});	
		}
	});
	
	
	// load grid list
	function mg_load_grids() {
		jQuery('#man_grid_box .inside').html('<div style="height: 30px;" class="lcwp_loading"></div>');
		
		jQuery.ajax({
			type: "POST",
			url: ajaxurl,
			data: "action=mg_get_grids",
			dataType: "json",
			success: function(response){	
				jQuery('#man_grid_box .inside').empty();
				
				var a = 0;
				jQuery.each(response, function(k, v) {	
					if( mg_sel_grid == v.id) {var sel = 'checked="checked"';}
					else {var sel = '';}
				
					jQuery('#man_grid_box .inside').append('<div class="misc-pub-section-last">\
						<span><input type="radio" name="gl" value="'+ v.id +'" '+ sel +' /></span>\
						<span class="mg_grid_tit" style="padding-left: 7px;">'+ v.name +'</span>\
						<span class="mg_del_grid" id="gdel_'+ v.id +'"></span>\
					</div>');
					
					a = a + 1;
				});
				
				if(a == 0) {jQuery('#man_grid_box .inside').html('<p>No existing grids</p>');}
			}
		});	
	}
	
	
	// delete grid
	jQuery('.mg_del_grid').live('click', function() {
		$target_grid_wrap = jQuery(this).parent(); 
		var grid_id  = jQuery(this).attr('id').substr(5);
		
		if(confirm('Delete definitively the grid?')) {
			var data = {
				action: 'mg_del_grid',
				grid_id: grid_id
			};
			
			jQuery.post(ajaxurl, data, function(response) {
				var resp = jQuery.trim(response); 
				
				if(resp == 'success') {
					// if is this one opened
					if(mg_sel_grid == grid_id) {
						jQuery('.mg_grid_content').html('<p>Select a grid ..</p>');
						mg_sel_grid = 0;
						
						// savegrid box
						jQuery('#save_grid_box').fadeOut();
					}
					
					$target_grid_wrap.slideUp(function() {
						jQuery(this).remove();
						
						if( jQuery('#man_grid_box .inside .misc-pub-section-last').size() == 0) {
							jQuery('#man_grid_box .inside').html('<p>No existing grids</p>');
						}
					});	
				}
				else {alert(resp);}
			});
		}
	});
	
	
	<!-- masonerize the preview -->
	
	// masonry init
	function masonerize() {
		$container = jQuery('#visual_builder_wrap');
		
		$cont_width = $container.width();
		$container.css('min-height', $cont_width+'px').css('height', 'auto');
		
		$container.masonry({
			isAnimated: true,
			columnWidth: 1,
			itemSelector: '.mg_box'
		});
		
		sortable_masonry();
		
		return true;	
	}
	
	// functions to re-resize
	function get_size(shape) {
		switch(shape) {
		  case '5_6': var perc = 0.83; break;
		  case '1_6': var perc = 0.166; break;
		  
		  case '4_5': var perc = 0.80; break;
		  case '3_5': var perc = 0.60; break;
		  case '2_5': var perc = 0.40; break;
		  case '1_5': var perc = 0.20; break;
		  
		  case '3_4': var perc = 0.75; break;
		  case '1_4': var perc = 0.25; break;
		  
		  case '2_3': var perc = 0.666; break;
		  case '1_3': var perc = 0.333; break;
		  
		  case '1_2': var perc = 0.50; break;
		  default   : var perc = 1; break;
		}
		return perc; 	
	}
	
	
	function reresize_w() {
		<?php foreach(mg_sizes() as $size) : ?> 
		if( $target.hasClass('col<?php echo $size ?>') ) { var wsize = ($container.width() - 0.2) * get_size('<?php echo $size ?>');}
		<?php endforeach; ?>	
		
		var wsize = Math.floor(parseFloat(wsize)) - (boxMargin * 2);
		return wsize;
	}
	
	
	function reresize_h() {
		<?php foreach(mg_sizes() as $size) : ?> 
		if( $target.hasClass('row<?php echo $size ?>') ) { var hsize = ($container.width() - 0.2) * get_size('<?php echo $size ?>');}
		<?php endforeach; ?>	 
		
		var hsize = Math.floor(parseFloat(hsize)) - (boxMargin * 2); 
		return hsize;
	}
	
	
	function get_box_perc(axis) {
		if(axis == 'w') {var aclass = 'col';}
		else {var aclass = 'row';}
		
		<?php foreach(mg_sizes() as $size) : ?> 
		if( $target.hasClass(aclass + '<?php echo $size ?>') ) { return (get_size('<?php echo $size ?>') * 100);}
		<?php endforeach; ?>
	}	

	
	function perc_to_px(size, with_other) {
		var px = parseFloat( (get_size(size) * $container.width() ) );
		
		if( with_other === undefined ) { return px; }		
		else { return px - (imgPadding * 2) - (imgBorder * 2) - (boxMargin * 2); }
	}
	
	
	function img_wrap_rs(axis) {
		if(axis == 'w') {
			var size = reresize_w() - (imgPadding * 2) - (imgBorder * 2);
		}		
		else {
			var size = reresize_h() - (imgPadding * 2) - (imgBorder * 2);
		}
		return parseFloat(size).toFixed(3); 
	}


	function size_boxes(target) {
		jQuery(target).each(function(index) {
			$target = jQuery(this);
			
			// boxes
			jQuery(this).css('width', Math.floor(reresize_w()) + 'px');
			jQuery(this).css('height', Math.floor(reresize_h()) + 'px');
			
			// boxes content
			jQuery(this).children('div').css('width', (Math.floor(reresize_w()) - 2) + 'px');
			jQuery(this).children('div').css('height', (Math.floor(reresize_h()) - 2) + 'px');
		});
		return true;	
	}
	
	
	// box resize width - live
	jQuery('.select_w').live('change', function() {
		$focus_box = jQuery(this).parents('.mg_box');
		
		var orig_w = $focus_box.attr('mg-width');
		var new_w = jQuery(this).val();
		
		$focus_box.removeClass('col'+orig_w);
		$focus_box.addClass('col'+new_w);
		$focus_box.attr('mg-width', new_w);
		
		size_boxes('.mg_box');
		$container.masonry( 'reload' );
	});
	
	
	// box resize height - live
	jQuery('.select_h').live('change', function() {
		$focus_box = jQuery(this).parents('.mg_box');
		
		var orig_h = $focus_box.attr('mg-height');
		var new_h = jQuery(this).val();
		
		$focus_box.removeClass('row'+orig_h);
		$focus_box.addClass('row'+new_h);
		$focus_box.attr('mg-height', new_h);
		
		size_boxes('.mg_box');
		$container.masonry( 'reload' );
	});
	
	
	// sortable masonry
	function sortable_masonry() {
		
		jQuery('#mg_sortable').sortable({
			placeholder: {
		        element: function(currentItem) {
					return jQuery("<li class='mg_box masonry mg_placeholder' style='height: " + (currentItem.height()) + "px; width: " + (currentItem.width()) +"px; background-color: #97dd52;'></li>")[0];
		        },
		        update: function(container, p) {
					return;
		        }
		    },
			tolerance: 'intersect',
			items: 'li',
			handle: 'h3',
			opacity: 0.8,
			scrollSensivity: 50,
			helper: function(event, element) {
				var clone = $(element).clone();
				clone.removeClass('mg_box');
				element.removeClass('mg_box');
				return clone;
			},
			start: function() {
				$container.masonry( 'reload' );
			},
			stop: function(event,ui){
				ui.item.addClass("mg_box");
				$container.masonry( 'reload' );
			},
			change: function(){
				$container.masonry( 'reload' );
			}
		}).disableSelection();
                                          
	};
	
	<!-- other -->
	
	// init chosen for live elements
	function mg_live_chosen() {
		jQuery('.chzn-select').each(function() {
			jQuery(".chzn-select").chosen(); 
			jQuery(".chzn-select-deselect").chosen({allow_single_deselect:true});
		});
	}
	
	// init iphone checkbox
	function mg_live_ip_checks() {
		jQuery('.ip-checkbox').each(function() {
			jQuery(this).iphoneStyle({
			  checkedLabel: 'ON',
			  uncheckedLabel: 'OFF'
			});
		});	
	}
	
	// hide message after 3 sec
	function mg_hide_wp_alert() {
		setTimeout(function() {
		 jQuery('#ajax_mess').empty();
		}, 3500);	
	}
	
});
</script>
