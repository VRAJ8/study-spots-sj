<?php
/**
 * Study Spots theme setup.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/site.css' );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		// Version by file time so browsers always get the latest after an edit.
		$ver = fn( $f ) => (string) filemtime( get_theme_file_path( $f ) );
		wp_enqueue_style( 'study-spots', get_theme_file_uri( 'assets/css/site.css' ), array(), $ver( 'assets/css/site.css' ) );
		wp_enqueue_script( 'study-spots', get_theme_file_uri( 'assets/js/site.js' ), array(), $ver( 'assets/js/site.js' ), array( 'strategy' => 'defer' ) );
	}
);

/**
 * Apply the saved day/night choice before first paint so there is no flash.
 */
add_action(
	'wp_head',
	function () {
		echo "<script>(function(d){try{var t=localStorage.getItem('ss-theme');if(t)d.dataset.theme=t;}catch(e){}d.dataset.ssMode=d.dataset.theme||(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');})(document.documentElement);</script>\n";
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( get_theme_file_uri( 'assets/fonts/bricolage-grotesque.woff2' ) ) );
		printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( get_theme_file_uri( 'assets/icon.svg' ) ) );
		printf( '<meta name="theme-color" content="#0b0c17" media="(prefers-color-scheme: dark)"><meta name="theme-color" content="#f6f3ec" media="(prefers-color-scheme: light)">' . "\n" );
	},
	1
);

/**
 * Basic SEO: meta description + Open Graph from the excerpt.
 */
add_action(
	'wp_head',
	function () {
		if ( is_singular() && has_excerpt() ) {
			$desc = wp_strip_all_tags( get_the_excerpt() );
		} elseif ( is_post_type_archive( 'spot' ) ) {
			$desc = __( 'Search and filter the best places to study around San José State by Wi-Fi, quiet, outlets and vibe.', 'study-spots' );
		} else {
			$desc = get_bloginfo( 'description' );
		}
		$title = wp_get_document_title();
		// WordPress prints canonicals for single posts/pages; cover the archive too.
		if ( is_post_type_archive( 'spot' ) ) {
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( get_post_type_archive_link( 'spot' ) ) );
		}
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
		printf( '<meta property="og:type" content="%s">' . "\n", is_singular() ? 'article' : 'website' );
		printf( '<meta name="twitter:card" content="summary">' . "\n" );
	}
);

add_action(
	'init',
	function () {
		register_block_style( 'core/button', array( 'name' => 'ss-glow', 'label' => __( 'Glow', 'study-spots' ) ) );
		register_block_style( 'core/button', array( 'name' => 'ss-ghost', 'label' => __( 'Ghost', 'study-spots' ) ) );
		register_block_style( 'core/group', array( 'name' => 'ss-glass', 'label' => __( 'Glass card', 'study-spots' ) ) );
		register_block_pattern_category( 'study-spots', array( 'label' => __( 'Study Spots', 'study-spots' ) ) );
	}
);

// Cleaner front end.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );

// Inline small block stylesheets instead of one request per block.
add_filter( 'styles_inline_size_limit', fn() => 60000 );

// Clearer archive title in the browser tab.
add_filter(
	'document_title_parts',
	function ( $parts ) {
		if ( is_post_type_archive( 'spot' ) ) {
			$parts['title'] = __( 'Explore study spots', 'study-spots' );
		}
		return $parts;
	}
);
