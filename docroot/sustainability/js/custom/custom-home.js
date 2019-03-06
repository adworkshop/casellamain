
// Make sure div stays full width/height on resize// make sure div stays full width/height on resize
  jQuery(window).resize(function() {
	getWidthAndHeight();
  });

  function getWidthAndHeight (){
	var winWidth = jQuery(window).width();
	var winHeight = jQuery(window).height();
	jQuery('.homeTitleContainer').css({'height': winHeight,});
  }

// SCROLLING ELEMENTS
  mainNavWrapper = jQuery('#generalNav');
  mainNav = jQuery('.navContainer');
  navLogo = jQuery('.headerLogo img');
  scrollOverlay = jQuery('.scrollOver');
  scrollOverlayOff = jQuery('.internalTitleOver');
  scrollDown = jQuery('.tabDown');
  var scrollState = 'top';
  jQuery(window).scroll(function(){ 
  var scrollPos = jQuery(window).scrollTop();
	if( ( scrollPos != 0 ) && ( scrollState === 'top' ) ) {
		scrollOverlay.stop().fadeIn(300);
		scrollOverlayOff.stop().fadeOut(300);
		scrollDown.stop().fadeOut(300);
		mainNavWrapper.stop().addClass( "border");
		mainNav.stop().addClass( "scrollIn");
		scrollState = 'scrolled';
		jQuery('.headerLogo img').attr('src', 'i/logo/logo.nav.svg');
		// SVG Fallbacks
		if (!Modernizr.svg) {
		  jQuery(".header-logo img").attr("src", "i/logo/logo.nav.png");
		};
	}       
	else if( ( scrollPos === 0 ) && ( scrollState === 'scrolled' ) ) {
		scrollOverlay.stop().fadeOut(300);
		scrollOverlayOff.stop().fadeIn(300);
		scrollDown.stop().fadeIn(300);
		mainNavWrapper.stop().removeClass( "border");
		mainNav.stop().removeClass("scrollIn");
		scrollState = 'top';
		jQuery('.headerLogo img').attr('src', 'i/logo/logo.navWhite.svg');
		// SVG Fallbacks
		if (!Modernizr.svg) {
		  jQuery(".header-logo img").attr("src", "i/logo/logo.navWhite.png");
		};
	}
  });
  
// CEO Toggle
  jQuery("a.ceo-toggle").click(function () {
	jQuery('.slideTogglebox').slideToggle("slow", function() {
	  // Animation complete.
	  if(Modernizr.mq("(min-width: 1100px)")) jQuery("html, body").stop(true).animate({ scrollTop: jQuery(".homeSectionCEOInnerContainer").offset().top - jQuery(".navContainer").height() - 30 }, 800);
	});
  });