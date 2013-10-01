<?php
/**
 * Carousel plugin to insert a cnet.com style carousel into Wordpress.
 *
 * @license GPL v2 or later
 * @package Carousel
 *
 * Copyright: Simon Baier <simon@giddyupbizdev.com>, 2009
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/*
Plugin Name: Carousels
Plugin URI: http://baier.com/sandbox/carousel-wordpress-plugin/
Description: Creates carousels that can be inserted any where in a page.
Version: 1.0
Author: Simon Baier
Author URI: http://baier.com
*/

/**
 * $carousel_db_version indicates the database schema version for the carousel
 * plugin. This is mostly used when the plugin is upgraded.
 *
 * @global    string	$carousel_db_version
 * @since 1.0
 */
global $carousel_db_version;
$carousel_db_version = "1.0";

/**
 * Display or submit the carousel edit form when action parameter is passed.
 * Otherwise display a table containing list of carousels.
 *
 * @since 1.0
 * @uses global $wpdb The wordpress database abstraction layer
 * @uses carousel_print_form() To print the actual carousel editing form with
 * 		values filled in.
 * @uses carousel_form_verify() To verify the form fields before submission.
 */
function carousel_manage () {

	global $wpdb;

	$action = stripslashes( $_REQUEST["action"] );

	if ( $action == "editcarousel" ) {
		/* Print the edit form with values filled in */
		carousel_print_form();
	}
	elseif ( $action == 'updatecarousel' &&
		carousel_form_verify() ) {

		/* Update the database with the new values submitted */
		$result = $wpdb->query( $wpdb->prepare( "UPDATE " .
					  $wpdb->prefix .
					  "carousels SET name=%s, description=%s, width=%d, height=%d WHERE carousel_id=%d",
					  stripslashes( $_REQUEST["carousel_name"] ),
					  stripslashes( $_REQUEST["carousel_description"] ),
					  stripslashes( $_REQUEST["carousel_width"] ),
					  stripslashes( $_REQUEST["carousel_height"] ),
					  stripslashes( $_REQUEST["carousel_id"] ) ) );

		$new_carousel_id = ( int ) $wpdb->insert_id;

		for ( $i = 1; $i < 8; $i++ ) {
			$wpdb->query( $wpdb->prepare( "UPDATE " .
					$wpdb->prefix .
					"carousel_slides SET url=%s, header=%s, paragraph=%s, read_more=%s, background=%s, thumbnail=%s WHERE carousel_id=%d AND slide_id=%d",
					stripslashes( $_REQUEST["slide_link$i"] ),
					stripslashes( $_REQUEST["slide_header$i"] ),
					stripslashes( $_REQUEST["slide_paragraph$i"] ),
					stripslashes( $_REQUEST["slide_more$i"] ),
					stripslashes( $_REQUEST["slide_background$i"] ),
					stripslashes( $_REQUEST["slide_thumbnail$i"] ),
					stripslashes( $_REQUEST["carousel_id"] ),
					$i ) );
		}

		echo "<h3>" . __( 'Carousel Updated Successfully', 'carousel' ) . "</h3>";
	}
	else {
		/* No action is given to this page. Simply print a table of existing carousels */
		$carousels = $wpdb->get_results( $wpdb->prepare( "SELECT carousel_id,name,description,width,height FROM " .
						 $wpdb->prefix .
						 "carousels ORDER BY carousel_id" ) );
?>
<h2>Carousels</h2>
<table cellspacing="0" class="widefat">
	<thead>
		<tr>
			<th><?php _e( 'Name', 'carousel' ); ?></th>
			<th><?php _e( 'Dimensions', 'carousel' ); ?></th>
			<th><?php _e( 'Description', 'carousel' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th><?php _e( 'Name', 'carousel' ); ?></th>
			<th><?php _e( 'Dimensions', 'carousel' ); ?></th>
			<th><?php _e( 'Description', 'carousel' ); ?></th>
		</tr>
	</tfoot>

	<tbody>
<?php
	$line = 0;
	foreach ( ( array ) $carousels as $carousel ) {
		$alternate = "";
		if ( $line % 2 == 0 )
			$alternate = " class='alternate'";
		echo "<tr$alternate><td><a href='?page=carousel-manage&action=editcarousel&carousel_id=" . $carousel->carousel_id. "'>" . $carousel->name . "</a></td><td>$carousel->width x $carousel->height</td><td>" . $carousel->description . "</td></tr>\n";
		$line++;
	}
?>
	</tbody>
</table>
</div>
<?php
	}
}

/**
 * Verify the carousel form fields when a add or edit carousel form is
 * submitted.
 *
 * @since 1.0
 * @return bool True: when the form fields are all sane. False: when one of
 *		the form fields is not correct
 */
function carousel_form_verify () {
	$action = stripslashes( $_REQUEST['action'] );
	if ( $action != 'addcarousel' &&
	     $action != 'updatecarousel' )
		return false;

	/* For editing, valid carousel_id is required */
	if ( $action == "updatecarousel" &&
	     !isset($_REQUEST["carousel_id"] ) ) {
		echo "<h3>" .
		     __( 'Invalid Carousel Identifier', 'carousel' ) .
		     "</h3>";
		return false;
	}

	$carousel_name = stripslashes( $_REQUEST["carousel_name"] );

	/* Carousel name is mandatory */
	if ( !$carousel_name ) {
		echo "<h3>" .
		     __( 'Name of the carousel is mandatory', 'carousel' ) .
		     "</h3>";
		return false;
	}

	/* Only smallcase letters, digits, underscore and hyphen are allowed
	 * in a carousel name
	 */
	if ( !preg_match("/^[a-z0-9_-]+$/", $carousel_name ) ) {
		echo "<h3>" .
		      __( 'Carousel name can only contain lowercase alphabets, numbers and hyphens',
			  'carousel' ) .
		      "</h3>";
		return false;
	}

	return true;
}

/**
 * Display or submit the carousel add form
 *
 * @since 1.0
 * @uses global $wpdb The wordpress database abstraction layer
 * @uses carousel_print_form() To print the actual carousel editing form with
 * 		values filled in.
 * @uses carousel_form_verify() To verify the form fields before submission.
 *
 * @return bool True: when the form fields are all sane. False: when one of
 *		the form fields is not correct
 */
function carousel_add () {

	global $wpdb;

	if ( stripslashes( $_REQUEST['action'] ) == 'addcarousel' &&
	    carousel_form_verify() ) {

		/* Add the carousel to database when form is submitted */
		$result = $wpdb->query( $wpdb->prepare( "INSERT INTO " .
					$wpdb->prefix . "carousels (name, description, width, height) VALUES (%s, %s, %d, %d)",
					  stripslashes( $_REQUEST["carousel_name"] ),
					  stripslashes( $_REQUEST["carousel_description"] ),
					  stripslashes( $_REQUEST["carousel_width"] ),
					  stripslashes( $_REQUEST["carousel_height"] ) ) );

		$new_carousel_id = ( int ) $wpdb->insert_id;

		for ( $i = 1; $i < 8; $i++ ) {
			$wpdb->query( $wpdb->prepare( "INSERT INTO " .
					$wpdb->prefix .
					"carousel_slides (slide_id, carousel_id, url, header, paragraph, read_more, background, thumbnail) " .
					"VALUES (%d, %d, %s, %s, %s, %s, %s, %s)",
					$i, $new_carousel_id,
					stripslashes( $_REQUEST["slide_link$i"] ),
					stripslashes( $_REQUEST["slide_header$i"] ),
					stripslashes( $_REQUEST["slide_paragraph$i"] ),
					stripslashes( $_REQUEST["slide_more$i"] ),
					stripslashes( $_REQUEST["slide_background$i"] ),
					stripslashes( $_REQUEST["slide_thumbnail$i"] ) ) );
		}

		echo "<h3>" .
		     __( 'Carousel Added Successfully', 'carousel' ) .
		     "</h3>";
	}
	else {
		/* No action is given, simply print a blank new carousel form */
		carousel_print_form();
	}
}

/**
 * Print the carousel add or edit form. When form values are available, they
 * are filled up in the fields, otherwise it is a fresh add carousel form.
 *
 * @since 1.0
 * @uses global $wpdb The wordpress database abstraction layer
 */
function carousel_print_form () {

	global $wpdb;

	$action = stripslashes( $_REQUEST["action"] );
	$slides = array();

	/* When we are editing a carousel, retrieve the carousel information
	 * from database so that we could fill the form with them
	 */
	if ( $action == "editcarousel" ) {
		if ( !isset( $_REQUEST["carousel_id"] ) ) {
			echo "<h3>" .
			     __( 'Invalid Carousel Identifier', 'carousel' ) .
			     "</h3>";
		}

		$carousel = $wpdb->get_row( $wpdb->prepare( "SELECT carousel_id,name,description,width,height FROM " .
						$wpdb->prefix . "carousels WHERE carousel_id=%d",
						stripslashes( $_REQUEST["carousel_id"] ) ) );

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT slide_id, url, header, paragraph, read_more, background, thumbnail FROM " .
						$wpdb->prefix .
						"carousel_slides WHERE carousel_id=%d",
						stripslashes( $_REQUEST["carousel_id"] ) ) );

		foreach ( $results as $result ) {
			$slides[ $result->slide_id ] = $result;
		}
	}

	/* Print the form */
?>
<div class="form-wrap">
<h2><?php $action == "editcarousel" ? _e( 'Edit Carousel', 'carousel' ) : _e( 'Add Carousel', 'carousel' ); ?></h2>
<form name="addeditcarousel" id="addeditcarousel" class="add:the-list: validate" method="post" action="">
	<input type="hidden" name="action" value="<?php print( $action == "editcarousel" ? "updatecarousel" : "addcarousel" ); ?>"/>
	<?php if ( $action == "editcarousel" )
			echo "<input type='hidden' name='carousel_id' value='" . stripslashes( $_REQUEST["carousel_id"] ) . "'/>";
	?>

<div class="form-field form-required">
	<label for="carousel_name"><?php _e( 'Carousel name', 'carousel' ) ?></label>
	<input name="carousel_name" id="carousel_name" type="text" value="<?php echo $carousel->name; ?>" size="40" aria-required="true" />
	<p><?php _e( 'The name of the carousel as used in the shortcode for inserting the carousel into a page (like [carousel name="mycarousel"]). It is usually all lowercase and contains only letters, numbers, and hyphens.', 'carousel' ); ?></p>
</div>

<div class="form-field">
	<label for="carousel_description"><?php _e( 'Description', 'carousel' ) ?></label>
	<textarea name="carousel_description" id="description" rows="5" cols="40"><?php echo $carousel->description; ?></textarea>
</div>

<div class="form-field">
	<label for="carousel_width"><?php _e( 'Width', 'carousel' ) ?></label>
	<input name="carousel_width" id="carousel_width" type="text" class="small-text" value="<?php echo $carousel->width; ?>" />
	<p><?php _e( 'Width of the carousel. The dimensions of the carousel should match the dimensions of slide background images.', 'carousel' ); ?></p>
</div>

<div class="form-field">
	<label for="carousel_height"><?php _e( 'Height', 'carousel' ) ?></label>
	<input name="carousel_height" id="carousel_height" type="text" class="small-text" value="<?php echo $carousel->height; ?>" />
	<p><?php _e( 'Height of the carousel. The dimensions of the carousel should match the dimensions of slide background images.', 'carousel' ); ?></p>
</div>

<table id="slides-table" width="100%">
<tbody id='the-list' class='list:meta'>
<?php
	for ( $i = 1; $i < 8; $i++ ) {
			?>
			<tr><td colspan="2"><h3><?php printf( __( 'Slide %d' ), $i ); ?></h3></td></tr>
			<tr><td valign="top">
			<div class="form-field">
				<label for="slide_header<?php echo $i; ?>"><?php _e( 'Header', 'carousel' ); ?></label>
				<input name="slide_header<?php echo $i; ?>" id="slide_header<?php echo $i; ?>" type="text" size="40" value="<?php echo $slides[$i]->header; ?>"/>
				<p><?php _e( 'This text appears as the header of the slide', 'carousel' ); ?></p>
			</div>
			<div class="form-field">
				<label for="slide_link<?php echo $i; ?>"><?php _e( 'Link URL', 'carousel' ); ?></label>
				<input name="slide_link<?php echo $i; ?>" id="slide_link<?php echo $i; ?>" type="text" size="40" value="<?php echo $slides[$i]->url; ?>"/>
				<p><?php _e( 'Clicking on the slide will open this URL. This includes clicking on background, heading, read more text and thumbnail.', 'carousel' ); ?></p>
			</div>
			<div class="form-field">
				<label for="slide_paragraph<?php echo $i; ?>"><?php _e( 'Paragraph', 'carousel' ); ?></label>
				<textarea name="slide_paragraph<?php echo $i; ?>" id="slide_paragraph<?php echo $i; ?>" rows="2" cols="40"><?php echo $slides[$i]->paragraph; ?></textarea>
			</div>
			</td><td valign="top">
			<div class="form-field">
				<label for="slide_more<?php echo $i; ?>"><?php _e( 'Read More Text', 'carousel' ); ?></label>
				<input name="slide_more<?php echo $i; ?>" id="slide_more<?php echo $i; ?>" type="text" size="40" value="<?php echo $slides[$i]->read_more; ?>"/>
				<p><?php _e( 'This text apears after the paragraph describing the slide below the horizontal line.', 'carousel' ); ?></p>
			</div>
			<div class="form-field">
				<label for="slide_background<?php echo $i; ?>"><?php _e( 'Background', 'carousel' ); ?></label>
				<input name="slide_background<?php echo $i; ?>" id="slide_background<?php echo $i; ?>" type="text" size="40" value="<?php echo $slides[$i]->background; ?>"/>
				<p><?php _e( 'URL of background image for the slide. This must be of size 460x280 pixels.', 'carousel' ); ?></p>
			</div>
			<div class="form-field">
				<label for="slide_thumbnail<?php echo $i; ?>"><?php _e( 'Thumbnail', 'carousel' ); ?></label>
				<input name="slide_thumbnail<?php echo $i; ?>" id="slide_thumbnail<?php echo $i; ?>" type="text" size="40" value="<?php echo $slides[$i]->thumbnail; ?>"/>
				<p><?php _e( 'URL of the thumbnail image for the slide. This must be of size 60x45 pixels.', 'carousel' ); ?></p>
			</div>
		</td></tr>
			<?php
	}
?>
</tbody>
</table>


<p class="submit">
	<input type="submit" class="button" name="submit" value="<?php $action == "editcarousel" ? _e( 'Edit Carousel', 'carousel' ) : _e( 'Add Carousel', 'carousel' ); ?>" />
</p>
</form>
</div>

<?php
}

/**
 * Hook to carousel shortcode handling to place the shortcode into Wordpress
 * pages/posts.
 *
 * Shortcode for carousel is [carousel] and only one pararamer "name" is
 * accepted. The name paramer should be the same as the carousel name that
 * needs to be inserted.
 *
 * Example:
 * <code>
 * [carousel name="my-carousel"]
 * </code>
 *
 * @since 1.0
 * @uses global $wpdb The wordpress database abstraction layer
 * @param array $attributes List of attributes sent by shortcode parser from
 * 		the attributes given given by the user.
 *
 * @return string The output carousel code that is the replace from the
 * 		shortcode text.
 */
function carousel_shortcode ( $attributes ) {

	global $wpdb;

	extract( shortcode_atts( array (
		'name' => ''
		), $attributes ) );

	if ( !isset( $name ) )
		return __( 'Carousel shortcode should have "name" parameter set',
			   'carousel' );

	/* Retrive information of the carousel slides */
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT width, height, slide_id, url, header, paragraph, read_more, background, thumbnail FROM " .
					 $wpdb->prefix . "carousel_slides as s, " .
					 $wpdb->prefix . "carousels as c WHERE c.carousel_id=s.carousel_id AND c.name=%s ORDER BY s.slide_id", $name ) );

	/* Print the carousel wrapper */
	$width = 0;
	$height = 0;
	if ( count( $results ) ) {
		$width = $results[0]->width;
		$height = $results[0]->height;
	}
	if ( $width <= 0 )
		$width = 460;
	if ( $height <= 0 )
		$height = 280;
	$output = "<div id='carouselMain' style='width: " . $width . "px; height: "
		    . $height . "px;'>\n";

	/* Print the slides */
	$slide = 0;
	$text_width = ( int ) ( $width * 0.43 );
	$thumb_container_top = $height - 50;
	foreach ( $results as $result ) {
		if ( !$result->header )
			continue;

		$output .=
			"<div class='slide' section='smallCarouselArea.$slide'><a href='$result->url'><img src='$result->background' alt='' ></a>
				<div class='slideText' style='width: " . $text_width . "px;'><h3><a href='$result->url'>$result->header</a></h3><p style='width: " . $text_width . "px;'>$result->paragraph</p>
					<a href='$result->url' class='readMore'>$result->read_more</a>
				</div>
			</div>\n";

		$slide++;
	}

	/* Print the thumbnails */
	$slide = 0;
	$output .= "<div class='thumbContainer' style='top: " . $thumb_container_top . "px; width: " . $width . "px;'>";
	foreach ( $results as $result ) {
		if ( !$result->header )
			continue;

		$output .=
			"<div class='featureButton' section='smallCarouselArea.$slide'>
				<a href='$result->url'><img src='$result->thumbnail' class='reflect rheight45 ropacity40' /></a>
			</div>\n";
		$slide++;
	}

	/* Print the left/right navigation buttons */
	$output .= "<div class='controls'>";
	$output .= '<a onclick="goToPrev()" href="javascript:void(0)">';
	$output .= '<img src="' . path_join( WP_PLUGIN_URL, basename( dirname( __FILE__ ) ) ) . '/left.gif" /></a>';
	$output .= '<a onclick="goToNext()" href="javascript:void(0)">';
	$output .= '<img src="' . path_join( WP_PLUGIN_URL, basename( dirname( __FILE__ ) ) ) . '/right.gif" /></a>';
	$output .= "</div>\n</div></div>\n";

	/* Print the required javascript */
	$output .= <<<EOT
<script type="text/javascript">
    \$moo('carouselMain').mooStore('carousel', new SimpleCarousel('carouselMain', $\$moo('#carouselMain .slide'), $\$moo('#carouselMain .featureButton'), {
slideInterval: 7500,
rotateAction: 'mouseenter',
onShowSlide: function(index) {
    this.buttons.mooEach(function(aButton, i) {
	if (i == index) {
	    aButton.mooMorph({'top':-10});
	    aButton.mooGetElement('img').mooMorph({'margin-bottom': 20});
	} else {
	    aButton.mooMorph({'top':0});
	    aButton.mooGetElement('img').mooMorph({'margin-bottom': 0});
	}
    })
}
    }));

    window.mooAddEvent('blur', function() {
	\$moo('carouselMain').mooRetrieve('carousel').stop();
    });

    var goToNext = function() {
	var carousel = \$moo('carouselMain').mooRetrieve('carousel');
	\$mooClear(carousel.slideshowInt);
	carousel.rotate('next');
    };

    var goToPrev = function() {
	var carousel = \$moo('carouselMain').mooRetrieve('carousel');
	\$mooClear(carousel.slideshowInt);
	carousel.rotate('prev');
    };

    var showVideo = function(videoDiv){
	var carousel = \$moo('carouselMain').mooRetrieve('carousel');
	\$mooClear(carousel.slideshowInt);
	carousel.showVideo(videoDiv);
    };

    var hideVideo = function(videoDiv){
	var carousel = \$moo('carouselMain').mooRetrieve('carousel');
	\$mooClear(carousel.slideshowInt);
	carousel.hideVideo(videoDiv);
    };

</script>
EOT;

return $output;
}

/* Hook to shortcode handling of "carousel" shortcode. The shortcode in any
 * page will be replaced by what ever is returned by this hook.
 */
add_shortcode( 'carousel', 'carousel_shortcode' );

/**
 * Add Carousel items to the admin menu
 *
 * @since 1.0
 * @uses add_menu_page() To add a top-level menu
 * @uses add_submenu_page() To add submenus to the top-level carousel menu
 */
function carousel_admin_menu () {
	add_menu_page( __( 'Manage Carousels', 'carousel' ),
		       __( 'Carousels', 'carousel' ), 8, 'carousel-manage',
		       'carousel_manage' );
	add_submenu_page( 'carousel-manage', __( 'Edit Carousel', 'carousel' ),
			  'Edit', 8, 'carousel-manage', 'carousel_manage' );
	add_submenu_page( 'carousel-manage',
			  __( 'Add New Carousel', 'carousel' ), 'Add New', 8,
			 'carousel-add-update' , 'carousel_add' );
}

/* This hook is called so that a plugin could add menu items to the
 * administration page.
 */
add_action( 'admin_menu', 'carousel_admin_menu', 15, 0 );

/**
 * Add carousel stylesheet to Wordpress output pages
 *
 * @since 1.0
 * @uses wp_enqueue_style() To add stylesheet tag to Wordpress output page
 */
function carousel_print_styles () {
	wp_enqueue_style( 'carousel_style',
			  path_join( WP_PLUGIN_URL,
				     basename( dirname( __FILE__ ) ) .
				     '/carousel.css' ) );
}

/* This hook is called so that a plugin could add its own stylesheets to the
 * HTML output.
 */
add_action( 'wp_print_styles', 'carousel_print_styles' );

/**
 * Add carousel scripts to Wordpress output pages
 *
 * @since 1.0
 * @uses wp_register_script() To add script to Wordpress list of scripts
 * @uses wp_print_scripts() To print the script tags
 */
function carousel_print_scripts () {
	wp_register_script( 'carousel_mootools_script',
			   path_join( WP_PLUGIN_URL,
				      basename( dirname( __FILE__ ) ) .
				      '/mootools.js' ) , false, '1.0.0' );
	wp_register_script( 'carousel_mootools_more_script',
			   path_join( WP_PLUGIN_URL,
				      basename( dirname( __FILE__ ) ) .
				      '/mootools-more.js' ) , false, '1.0.0' );
	wp_register_script( 'carousel_simple_carousel_script',
			   path_join( WP_PLUGIN_URL,
				      basename( dirname( __FILE__ ) ) .
				      '/SimpleCarousel.js' ), false, '1.0.0' );
	wp_print_scripts( array( 'carousel_mootools_script', 'carousel_mootools_more_script', 'carousel_simple_carousel_script' ) );
}

/* We hook to wp_head so that we can print our scripts at the very end with
 * predictable priority. This should be so because mootools inserted early
 * before jQuery will not work.
 */
add_action( 'wp_head', 'carousel_print_scripts' );

/**
 * Called when the plugin is activated. Performs automatic database
 * create/updation.
 *
 * @since 1.0
 * @uses global $wpdb The wordpress database abstraction layer
 * @uses dbDelta() To automatically create a new table or upgrade an
 * 		existing one.
 */
function carousel_activate () {

	global $wpdb;
	global $carousel_db_version;

	$table1 = $wpdb->prefix . "carousels";
	$table2 = $wpdb->prefix . "carousel_slides";
	/* Check if the carousel table already exists */
	if ( $wpdb->get_var( "show tables like '$table1'" ) != $table1 || 
	     $wpdb->get_var( "show tables like '$table2'" ) != $table2) {

		/* No tables, create one */
		$sql = array(
			"CREATE TABLE $table1 (
				carousel_id mediumint(9) NOT NULL AUTO_INCREMENT,
				name tinytext NOT NULL,
				description text NOT NULL,
				width smallint NOT NULL,
				height smallint NOT NULL,
				UNIQUE KEY carousel_id (carousel_id)
			);",
			"CREATE TABLE $table2 (
				slide_id mediumint(9) NOT NULL,
				carousel_id mediumint(9) NOT NULL,
				url varchar(255),
				header text,
				paragraph text,
				read_more text,
				background varchar(255),
				thumbnail varchar(255),
				PRIMARY KEY (slide_id,carousel_id)
			);" );

		/**
		 * This file contains the database table upgrade function
		 * dbDelta
		 */
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_option( "carousel_db_version", $carousel_db_version );
	}

	/* In future versions of the plugin, code will be written here to
	 * check the database version and perform an upgrade as required.
	 */
}

/* This hook is called when the plugin is activated in the plugin manager of
 * admin control panel
 */
register_activation_hook( __FILE__, "carousel_activate" );

/**
 * Called when carousel plugin is initialized. Currently, only sets up
 * internationalization.
 *
 * @since 1.0
 * @uses load_plugin_textdomain() To initialize internationalization framework
 * 		for the plugin
 */
function carousel_init () {
	$plugin_dir = basename( dirname( __FILE__ ) );
	load_plugin_textdomain( 'carousel' );
}

/* This hook is called when the plugin is initialized
 */
add_action( 'init', carousel_init );

?>
