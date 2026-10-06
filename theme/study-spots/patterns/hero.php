<?php
/**
 * Title: Home hero
 * Slug: study-spots/hero
 * Categories: study-spots, banner
 * Description: Animated hero with headline, search and live stats.
 */
?>
<!-- wp:group {"tagName":"section","align":"full","className":"ss-hero","layout":{"type":"constrained","contentSize":"1240px"}} -->
<section class="wp-block-group alignfull ss-hero">
<!-- wp:html -->
<div class="ss-hero__bg" aria-hidden="true"><span class="ss-blob ss-blob--1"></span><span class="ss-blob ss-blob--2"></span><span class="ss-blob ss-blob--3"></span><span class="ss-grid-lines"></span><span class="ss-grain"></span></div>
<!-- /wp:html -->

<!-- wp:columns {"verticalAlignment":"center","className":"ss-hero__cols"} -->
<div class="wp-block-columns are-vertically-aligned-center ss-hero__cols">
<!-- wp:column {"verticalAlignment":"center","width":"58%","className":"ss-hero__copy"} -->
<div class="wp-block-column is-vertically-aligned-center ss-hero__copy" style="flex-basis:58%">
<!-- wp:paragraph {"className":"ss-pill"} -->
<p class="ss-pill"><span class="ss-pill__dot" aria-hidden="true"></span>Student-reviewed · San José State</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"ss-hero__title"} -->
<h1 class="wp-block-heading ss-hero__title">Find your <span class="ss-rotator" data-ss-rotator="quiet,cozy,late-night,sunny,group">quiet</span> <em>study spot.</em></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ss-hero__lead"} -->
<p class="ss-hero__lead">Libraries, cafés, lawns and late-night tables around SJSU, rated by students on the three things that matter: Wi-Fi, quiet and outlets.</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
<form class="ss-hero__search" role="search" action="/spots/" method="get">
	<label for="ss-hero-q" class="screen-reader-text">Search study spots</label>
	<svg aria-hidden="true" viewBox="0 0 24 24" width="22" height="22"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
	<input id="ss-hero-q" name="q" type="search" placeholder="Try “library”, “Japantown”, “late”…" autocomplete="off">
	<button type="submit" class="ss-button ss-button--primary">Find spots</button>
</form>
<!-- /wp:html -->

<!-- wp:study-spots/spot-stats /-->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"42%","className":"ss-hero__visual"} -->
<div class="wp-block-column is-vertically-aligned-center ss-hero__visual" style="flex-basis:42%">
<!-- wp:html -->
<div class="ss-stack" aria-hidden="true" data-ss-tilt>
	<div class="ss-float ss-float--a"><span class="ss-float__k">Wi-Fi</span><span class="ss-float__bar" style="--v:5"></span></div>
	<div class="ss-mock">
		<div class="ss-mock__cover"><span class="ss-mock__score">4.3</span></div>
		<div class="ss-mock__body">
			<span class="ss-mock__area">On Campus</span>
			<span class="ss-mock__title">King Library</span>
			<span class="ss-mock__row"><span>Wi-Fi</span><i style="--v:5"></i></span>
			<span class="ss-mock__row"><span>Quiet</span><i style="--v:4"></i></span>
			<span class="ss-mock__row"><span>Outlets</span><i style="--v:4"></i></span>
			<span class="ss-mock__tags"><b style="--ss-hue:205">Silent focus</b><b style="--ss-hue:25">Group-friendly</b></span>
		</div>
	</div>
	<div class="ss-float ss-float--b"><span class="ss-float__dot"></span>Open after 9pm</div>
	<div class="ss-float ss-float--c">☕ <span>Cozy café near you</span></div>
</div>
<!-- /wp:html -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</section>
<!-- /wp:group -->
