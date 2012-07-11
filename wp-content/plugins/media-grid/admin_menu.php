<?php
// delaring menu, custom post type and taxonomy

///////////////////////////////////
// SETTINGS PAGE

function mg_settings_page() {	
	add_submenu_page('edit.php?post_type=mg_items', 'Grid Builder', 'Grid Builder', 'upload_files', 'mg_builder', 'mg_builder');	
	add_submenu_page('edit.php?post_type=mg_items', 'Settings', 'Settings', 'install_plugins', 'mg_settings', 'mg_settings');	
}
add_action('admin_menu', 'mg_settings_page');


function mg_builder() {
	include(MG_DIR . '/grid_builder.php');	
}
function mg_settings() {
	include(MG_DIR . '/settings.php');	
}


//////////////////////
// GRID TAXONOMY

add_action( 'init', 'register_taxonomy_mg_grids' );
function register_taxonomy_mg_grids() {
    $labels = array( 
        'name' => _x( 'Grids', 'mg_grids' ),
        'singular_name' => _x( 'Grid', 'mg_grids' ),
        'search_items' => _x( 'Search Grids', 'mg_grids' ),
        'popular_items' => _x( 'Popular Grids', 'mg_grids' ),
        'all_items' => _x( 'All Grids', 'mg_grids' ),
        'parent_item' => _x( 'Parent Grid', 'mg_grids' ),
        'parent_item_colon' => _x( 'Parent Grid:', 'mg_grids' ),
        'edit_item' => _x( 'Edit Grid', 'mg_grids' ),
        'update_item' => _x( 'Update Grid', 'mg_grids' ),
        'add_new_item' => _x( 'Add New Grid', 'mg_grids' ),
        'new_item_name' => _x( 'New Grid', 'mg_grids' ),
        'separate_items_with_commas' => _x( 'Separate grids with commas', 'mg_grids' ),
        'add_or_remove_items' => _x( 'Add or remove Grids', 'mg_grids' ),
        'choose_from_most_used' => _x( 'Choose from most used Grids', 'mg_grids' ),
        'menu_name' => _x( 'Grids', 'mg_grids' ),
    );

    $args = array( 
        'labels' => $labels,
        'public' => false,
        'show_in_nav_menus' => false,
        'show_ui' => false,
        'show_tagcloud' => false,
        'hierarchical' => false,
        'rewrite' => false,
        'query_var' => true
    );

    register_taxonomy( 'mg_grids', null, $args );
}


//////////////////////////////////////
// ITEM CUSTOM POST TYPE & TAXONOMY

add_action( 'init', 'register_cpt_mg_item' );
function register_cpt_mg_item() {

    $labels = array( 
        'name' => _x( 'Items', 'mg_item' ),
        'singular_name' => _x( 'Item', 'mg_item' ),
        'add_new' => _x( 'Add New Item', 'mg_item' ),
        'add_new_item' => _x( 'Add New Item', 'mg_item' ),
        'edit_item' => _x( 'Edit Item', 'mg_item' ),
        'new_item' => _x( 'New Item', 'mg_item' ),
        'view_item' => _x( 'View Item', 'mg_item' ),
        'search_items' => _x( 'Search Items', 'mg_item' ),
        'not_found' => _x( 'No items found', 'mg_item' ),
        'not_found_in_trash' => _x( 'No items found in Trash', 'mg_item' ),
        'parent_item_colon' => _x( 'Parent Item:', 'mg_item' ),
        'menu_name' => _x( 'Media Grid', 'mg_item' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,      
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'taxonomies' => array('mg_item_categories'),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
		'menu_icon' => MG_URL . '/img/mg_icon_small.png',
        'menu_position' => 52,
        'show_in_nav_menus' => false,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'has_archive' => false,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => false,
		'supports' => array('title', 'editor', 'thumbnail'),
        'capability_type' => 'post'
    );
    register_post_type( 'mg_items', $args );	

	//////
	
	$labels = array( 
        'name' => _x( 'Item Categories', 'mg_item_categories' ),
        'singular_name' => _x( 'Item Category', 'mg_item_categories' ),
        'search_items' => _x( 'Search Item Categories', 'mg_item_categories' ),
        'popular_items' => NULL,
        'all_items' => _x( 'All Item Categories', 'mg_item_categories' ),
        'parent_item' => _x( 'Parent Item Category', 'mg_item_categories' ),
        'parent_item_colon' => _x( 'Parent Item Category:', 'mg_item_categories' ),
        'edit_item' => _x( 'Edit Item Category', 'mg_item_categories' ),
        'update_item' => _x( 'Update Item Category', 'mg_item_categories' ),
        'add_new_item' => _x( 'Add New Item Category', 'mg_item_categories' ),
        'new_item_name' => _x( 'New Item Category', 'mg_item_categories' ),
        'separate_items_with_commas' => _x( 'Separate item categories with commas', 'mg_item_categories' ),
        'add_or_remove_items' => _x( 'Add or remove Item Categories', 'mg_item_categories' ),
        'choose_from_most_used' => _x( 'Choose from most used Item Categories', 'mg_item_categories' ),
        'menu_name' => _x( 'Item Categories', 'mg_item_categories' ),
    );

    $args = array( 
        'labels' => $labels,
        'public' => false,
        'show_in_nav_menus' => false,
        'show_ui' => true,
        'show_tagcloud' => false,
        'hierarchical' => true,
        'rewrite' => false,
        'query_var' => true
    );
    register_taxonomy( 'mg_item_categories', array('mg_items'), $args );
}


//////////////////////////////
// VIEW CUSTOMIZATORS

function mg_updated_messages( $messages ) {
  global $post;

  $messages['mg_items'] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => __('Item updated'),
    2 => __('Item updated'),
    3 => __('Item deleted'),
    4 => __('Item updated'),
    /* translators: %s: date and time of the revision */
    5 => isset($_GET['revision']) ? sprintf( __('Item restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => __('Item published'),
    7 => __('Item saved'),
    8 => __('Item submitted'),
    9 => sprintf( __('Item scheduled for: <strong>%1$s</strong>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ))),
    10 => __('Item draft updated'),
  );

  return $messages;
}
add_filter('post_updated_messages', 'mg_updated_messages');



// edit submitbox - hide minor submit minor-publishing
add_action('admin_head', 'mg_items_custom_submitbox');
function mg_items_custom_submitbox() {
	global $post_type;

    if ($post_type == 'mg_items') {
		echo '<style type="text/css">
		#minor-publishing {
			display: none;	
		}
		#lcwp_slider_opt_box > .inside {
			padding: 0;	
		}
		#lcwp_slider_creator_box {
			background: none;
			border: none;	
		}
		#lcwp_slider_creator_box > .handlediv {
			display: none;	
		}
		#lcwp_slider_creator_box > h3.hndle {
			background: none;
			border: none;
			padding: 12px 0 6px 0;	
			font-size: 18px;
			border-radius: 0px 0px 0px 0px;
		}
		#add_slide {
			float: left;
			margin-top: -36px;
			margin-left: 132px;
			cursor: pointer;	
		}
		.slide_form_table {
			width: 100%;	
		}
		.slide_form_table td {
			vertical-align: top;	
		}
		.second_col {
			width: 50%;
			border-left: 1px solid #ccc; 
			padding-left: 30px;
		}
		</style>';
	}
}


// customize the grid items custom post type table
add_filter('manage_edit-mg_items_columns', 'mg_edit_pt_table_head', 10, 2);
function mg_edit_pt_table_head($columns) {
	$new_cols = array();
	
	$new_cols['cb'] = '<input type="checkbox" />';
	$new_cols['title'] = _x('Title', 'column name');
	
	$new_cols['mg_cat'] = __('Categories');
	$new_cols['mg_size'] = __('Sizes');
	$new_cols['mg_type'] = __('Type');
	$new_cols['mg_layout'] = __('Layout');
	$new_cols['date'] = _x('Date', 'column name');
	$new_cols['mg_thumb'] = __('Main Image');
	
	return $new_cols;
}


add_action('manage_mg_items_posts_custom_column', 'mg_edit_pt_table_body', 10, 2);
function mg_edit_pt_table_body($column_name, $id) {
	require_once(MG_DIR . '/functions.php');
	
	switch ($column_name) {
		case 'mg_cat' :
			$cats = get_the_terms($id, 'mg_item_categories');
            if (is_array($cats)) {
				$item_cats = array();
				foreach($cats as $cat) { $item_cats[] = $cat->name;}
				echo implode(', ', $item_cats);
			}
			else {echo '';}
			break;
		
		case 'mg_size' : 
			if(get_post_meta($id, 'mg_width', true) && get_post_meta($id, 'mg_height', true)) {
				echo str_replace('_', '/', get_post_meta($id, 'mg_width', true)) .' x '. str_replace('_', '/', get_post_meta($id, 'mg_height', true));
			}
			else {echo 'not specified';}
			break;
		
		case 'mg_type' :
			if(get_post_meta($id, 'mg_main_type', true)) { echo item_slug_to_name(get_post_meta($id, 'mg_main_type', true)); }
			else {echo '';}
			break;
			
		case 'mg_layout' :
			if(
				get_post_meta($id, 'mg_main_type', true) && 
				get_post_meta($id, 'mg_main_type', true) != 'simple_img' &&
				get_post_meta($id, 'mg_main_type', true) != 'link'
			) { 
				if(get_post_meta($id, 'mg_layout', true)) {
					if(get_post_meta($id, 'mg_layout', true) == 'full') {echo 'Full Width';}
					else {echo 'Text on side';}	
				}
				else {echo '';}
			}
			else {echo '';}
			break;	
		
		case 'mg_thumb' :
			echo get_the_post_thumbnail( $id, array(110, 110));
			break;
	
		default:
			break;
	}
	return true;
}


//////////////////////////////////////
// ENABLE CPT FILTER BY TAXONOMY

add_action('restrict_manage_posts','mg_items_filter_by_cat');
function mg_items_filter_by_cat() {
    global $typenow;
    global $wp_query;
	
    if ($typenow=='mg_items') {
        $taxonomy = 'mg_item_categories';
		
		isset($wp_query->query['mg_item_categories']) ? $sel = $wp_query->query['mg_item_categories'] : $sel = ''; 
		
        wp_dropdown_categories(array(
            'show_option_all' =>  __("Show all the categories"),
            'taxonomy'        =>  $taxonomy,
            'name'            =>  'mg_item_categories',
            'orderby'         =>  'name',
            'selected'        =>  $sel,
            'hierarchical'    =>  false,
            'depth'           =>  1,
            'show_count'      =>  false,
            'hide_empty'      =>  true
        ));
    }
}

add_filter('parse_query','mg_cat_id_to_cat_term');
function mg_cat_id_to_cat_term($query) {
	global $pagenow;
    global $typenow;
	
	$filters = get_object_taxonomies($typenow);
	foreach ($filters as $tax_slug) {
		$var = &$query->query_vars[$tax_slug];
		if (isset($var) && (int)$var > 0) {
			$term = get_term_by('id',$var,$tax_slug);
			$var = $term->slug;
		}
	}
}



///////////////////////////////////////////////////////
// FIX FOR THEMES THAT DON'T SUPPOR FEATURED IMAGE
function mg_add_thumb_support() {
    $supportedTypes = get_theme_support( 'post-thumbnails' );

    if( $supportedTypes === false ) {
        add_theme_support( 'post-thumbnails', array( 'mg_items' ) ); 
    }
    elseif( is_array( $supportedTypes ) ) {
        $supportedTypes[0][] = 'mg_items';
        add_theme_support( 'post-thumbnails', $supportedTypes[0] );
    }
}
add_action( 'after_setup_theme', 'mg_add_thumb_support', 11 );

?>