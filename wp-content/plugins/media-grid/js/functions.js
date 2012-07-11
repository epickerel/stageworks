jQuery(document).ready(function($) {
	
	// switch theme menu pages
	jQuery('.lcwp_opt_menu').click(function() {
		curr_opt = jQuery('.curr_item').attr('id').substr(5);
		var opt_id = jQuery(this).attr('id').substr(5);
		
		if(!jQuery('#form_'+opt_id).is(':visible')) {
			// remove curr
			jQuery('.curr_item').removeClass('curr_item');
			jQuery('#form_'+curr_opt).hide();
			
			// show selected
			jQuery(this).addClass('curr_item');
			jQuery('#form_'+opt_id).show();	
		}
	});
	
	
	// sliders
	var a = 0; 
	$('.lcwp_slider').each(function(idx, elm) {
		var sid = 'slider'+a;
		jQuery(this).attr('id', sid);	
	
		svalue = parseInt(jQuery("#"+sid).next('input').val());
		minv = parseInt(jQuery("#"+sid).attr('min'));
		maxv = parseInt(jQuery("#"+sid).attr('max'));
		stepv = parseInt(jQuery("#"+sid).attr('step'));
		
		jQuery('#' + sid).slider({
			range: "min",
			value: svalue,
			min: minv,
			max: maxv,
			step: stepv,
			slide: function(event, ui) {
				jQuery('#' + sid).next().val(ui.value);
			}
		});
		jQuery('#'+sid).next('input').change(function() {
			var val = parseInt(jQuery(this).val());
			var minv = parseInt(jQuery("#"+sid).attr('min'));
			var maxv = parseInt(jQuery("#"+sid).attr('max'));
			
			if(val <= maxv && val >= minv) {
				jQuery('#'+sid).slider('option', 'value', val);
			}
			else {
				if(val <= maxv) {jQuery('#'+sid).next('input').val(minv);}
				else {jQuery('#'+sid).next('input').val(maxv);}
			}
		});
		
		a = a + 1;
	});
	
	// iphone checks
	jQuery('.ip-checkbox').each(function() {
		jQuery(this).iphoneStyle({
		  checkedLabel: 'ON',
		  uncheckedLabel: 'OFF'
		});
	});
	
	// chosen
	jQuery('.chzn-select').each(function() {
		jQuery(".chzn-select").chosen(); 
		jQuery(".chzn-select-deselect").chosen({allow_single_deselect:true});
	});
	
	
	// tinymce btn

	// resize and center
	mg_H = 270;
	mg_W = 555;
	
	jQuery('#mg_editor_btn').live("click", function () {
		setTimeout(function() {
			tb_show( 'Media Grid', '#TB_inline?height='+mg_H+'&width='+mg_W+'&inlineId=mg_popup_container' );
			
			jQuery('#TB_window').css("height", mg_H);
			jQuery('#TB_window').css("width", mg_W);	
			
			jQuery('#TB_window').css("top", ((jQuery(window).height() - mg_H) / 4) + 'px');
			jQuery('#TB_window').css("left", ((jQuery(window).width() - mg_W) / 4) + 'px');
			jQuery('#TB_window').css("margin-top", ((jQuery(window).height() - mg_H) / 4) + 'px');
			jQuery('#TB_window').css("margin-left", ((jQuery(window).width() - mg_W) / 4) + 'px');
		
		
			jQuery('.mg_popup_ip').iphoneStyle({
			  checkedLabel: 'YES',
			  uncheckedLabel: 'NO'
			});
		
		}, 1);	
		
		jQuery('#mg_test input').iphoneStyle({
		  checkedLabel: 'YES',
		  uncheckedLabel: 'NO'
		});
	});
	
	jQuery(window).resize(function() {
		if(jQuery('#lcwp_tinymce_table').is(':visible')) {
			jQuery('#lcwp_tinymce_table').parent().parent().css("height", mg_H);
			jQuery('#lcwp_tinymce_table').parent().parent().css("width", mg_W);	
			
			jQuery('#lcwp_tinymce_table').parent().parent().css("top", ((jQuery(window).height() - mg_H) / 4) + 'px');
			jQuery('#lcwp_tinymce_table').parent().parent().css("left", ((jQuery(window).width() - mg_W) / 4) + 'px');
			jQuery('#lcwp_tinymce_table').parent().parent().css("margin-top", ((jQuery(window).height() - mg_H) / 4) + 'px');
			jQuery('#lcwp_tinymce_table').parent().parent().css("margin-left", ((jQuery(window).width() - mg_W) / 4) + 'px');
		}
	});
	
	
	// add the shortcode to the grid
	jQuery('#mg_insert_grid').live("click", function () {
		var gid = jQuery('#mg_grid_choose').val();
		var sc = '[mediagrid cat="'+gid+'"';
		
		// filter
		if( jQuery('#mg_filter_grid').is(':checked') ) {var filter = 1;}
		else {var filter = 0;}
		sc = sc + ' filter="'+filter+'"';
		
		// relative width
		if( jQuery.trim(jQuery('#mg_grid_w').val()) == '' ) {var r_width = 'auto';}
		else {var r_width = jQuery('#mg_grid_w').val();}
		sc = sc + ' r_width="'+r_width+'"';
		
		sc = sc + ']';
		
		// inserts the shortcode into the active editor
		if( jQuery('#wp-content-editor-container > textarea').is(':visible') ) {
			var val = jQuery('#wp-content-editor-container > textarea').val() + sc;
			jQuery('#wp-content-editor-container > textarea').val(val);	
		}
		else {tinyMCE.activeEditor.execCommand('mceInsertContent', 0, sc); }
		
		// closes Thickbox
		tb_remove();
	});
	
});