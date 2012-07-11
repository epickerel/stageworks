<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <title><?php
    if ( is_single() ) { single_post_title(); }
    elseif ( is_home() || is_front_page() ) { bloginfo('name'); print ' | '; bloginfo('description'); get_page_number(); }
    elseif ( is_page() ) { single_post_title(''); }
    elseif ( is_search() ) { bloginfo('name'); print ' | Search results for ' . wp_specialchars($s); get_page_number(); }
    elseif ( is_404() ) { bloginfo('name'); print ' | Not Found'; }
    else { bloginfo('name'); wp_title('|'); get_page_number(); }
  ?></title>

  <meta http-equiv="content-type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>">

  <link rel="stylesheet" type="text/css" href="<?php bloginfo('stylesheet_url'); ?>">

  <?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>

  <?php wp_head(); ?>

  <link rel="alternate" type="application/rss+xml" href="<?php bloginfo('rss2_url'); ?>" title="<?php printf( __( '%s latest posts', 'stageworks' ), wp_specialchars( get_bloginfo('name'), 1 ) ); ?>">
  <link rel="alternate" type="application/rss+xml" href="<?php bloginfo('comments_rss2_url') ?>" title="<?php printf( __( '%s latest comments', 'stageworks' ), wp_specialchars( get_bloginfo('name'), 1 ) ); ?>">
  <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
</head>

<body>
<div id="wrapper" class="hfeed">
  <header id="header">
    <div id="masthead">

      <div id="branding">
        <span class="chrome1"></span>
        <div id="blog-title"><span><a href="<?php bloginfo( 'url' ) ?>/" title="<?php bloginfo( 'name' ) ?>" rel="home"><?php bloginfo( 'name' ) ?></a></span></div>
<?php if ( is_home() || is_front_page() ) { ?>
        <h1 id="blog-description"><?php bloginfo( 'description' ) ?></h1>
<?php } else { ?>
        <div id="blog-description"><?php bloginfo( 'description' ) ?></div>
<?php } ?>
    </div><!-- #branding -->

      <div id="access">
<?php /* ?>
        <div class="skip-link"><a href="#content" title="<?php _e( 'Skip to content', 'your-theme' ) ?>"><?php _e( 'Skip to content', 'your-theme' ) ?></a></div>
<?php wp_page_menu( 'sort_column=menu_order' ); ?>
<?php */ ?>

      </div><!-- #access -->

    </div><!-- #masthead -->
  </header><!-- #header -->

  <div id="main">
a
