  jQuery(document).ready(function() {
	resizeContentContainerTriangles();
  });

// Window Resize Functions
  jQuery(window).resize(function() {
	resizeContentContainerTriangles();
  });

// Resize Content Container Triangles
  function resizeContentContainerTriangles() {
	var windowWidthR = jQuery(window).width();
	jQuery(".upper-content-container-triangle-left").css({ "border-top": windowWidthR / 6 + 'px solid #00263e' });
	jQuery(".upper-content-container-triangle-left").css({ "border-right": windowWidthR / 1.5 + 'px solid transparent' });
	jQuery(".upper-content-container-triangle-right").css({ "border-top": windowWidthR / 6 + 'px solid #00263e' });
	jQuery(".upper-content-container-triangle-right").css({ "border-left": windowWidthR / 1.5 + 'px solid transparent' });
  }
 
// Make sure div stays full width/height on resize// make sure div stays full width/height on resize
  jQuery(window).resize(function() {
	getWidthAndHeight();
  });

  function getWidthAndHeight (){
	var winWidth = jQuery(window).width();
	var winHeight = jQuery(window).height();
	jQuery('.homeTitleContainer').css({'width': winWidth,'height': winHeight,});
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
	  if(Modernizr.mq("(min-width: 1100px)")) jQuery("html, body").stop(true).animate({ scrollTop: jQuery(".homeSection5Container").offset().top - jQuery(".navContainer").height() }, 800);
	});
  });

// Goals Toggle
  jQuery("a.homeGoalsTriggerCol2Wrapper").click(function () {
	jQuery('.homeGoalsPanel').slideToggle("slow", function() {
	  // Animation complete.
	  if(Modernizr.mq("(min-width: 1100px)")) jQuery("html, body").stop(true).animate({ scrollTop: jQuery(".homeGoalsTriggerCol2Wrapper").offset().top - jQuery(".navContainer").height() }, 800);
	});
  });