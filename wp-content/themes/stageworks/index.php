<?php get_header(); ?>

<div id="container">

  <div id="content">
<?php while ( have_posts() ) : the_post() ?>

    <h2 class="entry-title"><a href="<?php the_permalink(); ?>"
      title="<?php printf( __('Permalink to %s', 'stageworks'), the_title_attribute('echo=0') ); ?>"
      rel="bookmark"><?php the_title(); ?></a></h2>
    <div class="entry-content">   
<?php the_content( __( 'Continue reading <span class="meta-nav">&raquo;</span>', 'stageworks' )  ); ?>
<?php wp_link_pages('before=<div class="page-link">' . __( 'Pages:', 'stageworks' ) . '&after=</div>') ?>
    </div><!-- .entry-content -->

<?php endwhile; ?>   
  </div><!-- #content -->

</div><!-- #container -->

<div id="primary" class="widget-area">
</div><!-- #primary .widget-area -->

<div id="secondary" class="widget-area">
</div><!-- #secondary -->

<?php get_footer(); ?>


