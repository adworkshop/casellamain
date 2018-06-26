// Accordion / Toggles...
jQuery('.accordion-toggle').on('click', function(event){
	"use strict";
	event.preventDefault();
	var accordion = jQuery(this);
	var accordionContent = accordion.next('.accordion-content');
	var accordionToggleIcon = jQuery(this).children('.toggle-icon');
	var accordionOutContainer = jQuery(this).parent('.accordion-container');
	accordion.toggleClass("open");
	accordionContent.slideToggle(250);
	if (accordion.hasClass("open")) {
		accordionToggleIcon.html("<i class='fa fa-chevron-up'></i>");
		accordionOutContainer.addClass( "selected" );
	} else {
		accordionToggleIcon.html("<i class='fa fa-chevron-down'></i>");
		accordionOutContainer.removeClass( "selected" );
	}
});

jQuery(document).ready(function() {
  "use strict";
  var originalSlider = jQuery('#home-slider').html();

  picturefill();

  jQuery(window).on('resize', {'originalSlider' : originalSlider}, windowResize);
	// Dont need to send the original html through the first time, it isn't regenerating.
  windowResize();

  jQuery('.home-resourceGrid > .home-resourceGrid-indWrapper').each(function(index, element) {
    jQuery(element).hoverdir();
  });

  jQuery('.home-gridDisplay-leftCol-indContainer').each(markCustomerCare);
});

function markCustomerCare(index, element){
  if (jQuery('.home-gridDisplay-leftCol-titleContainer h2 div', element).html().match(/Customer\sCare/) !== null) {
    jQuery(element).addClass('customerCare');
  }
}

jQuery(window).on("load", function() {
  equalheight('.home-verticalPanel-content');
});

jQuery(window).resize(function(){
  equalheight('.home-verticalPanel-content');
});

function windowResize(event) {
  "use strict";

  var isDesktop = Modernizr.mq('( min-width: 1200px )'),
      slider = jQuery('#home-slider').data('royalSlider'),
      sliderInited = typeof slider !== "undefined" && slider !== null;

  if (isDesktop) {
    if (!sliderInited || slider.st.arrowsNav) {
      if (sliderInited) {
        resetSliderContainer(slider, event.data.originalSlider);
      }
      royalsliderDesktopBind();
      jQuery('.homeCarouselContainer .rsGCaption').appendTo('.home-grandGalleryCaptionWrapper');
      jQuery(".home-gridDisplay-rightCol-container").insertAfter(".home-gridDisplay-leftCol-container");
    }
  }
  else {
	  if (!sliderInited || !slider.st.arrowsNav) {
			if (sliderInited) {
				resetSliderContainer(slider, event.data.originalSlider);
			}
      royalsliderMobileBind();
			jQuery('.homeCarouselContainer .rsGCaption').appendTo('.home-grandGalleryCaptionWrapper');
			jQuery(".home-gridDisplay-rightCol-container").insertBefore(".home-gridDisplay-leftCol-container");
    }
  }
}

function resetSliderContainer(slider, originalSlider) {
	"use strict";
	slider.destroy();
	// Reset the html.
	jQuery('#home-slider').html(originalSlider);
	// Empty the caption holder.
	jQuery('.home-grandGalleryCaptionWrapper').html('');
}

function getWidthAndHeight (){
  "use strict";

  jQuery('.homeCarouselContainer').css({'height': jQuery('.home-gridDisplay-leftCol-container').height()});
}

function royalsliderDesktopBind() {
  "use strict";

  jQuery('#home-slider').royalSlider({
    arrowsNav: false,
    controlNavigation: 'none',
    keyboardNavEnabled: true,
    controlsInside: false,
    imageScaleMode: 'fill',
    imageAlignCenter: true,
    autoScaleSlider: false,
    loop: true,
    numImagesToPreload: 1,
    thumbsFitInViewport: false,
    navigateByClick: true,
    arrowsNavAutoHide:true,
    arrowsNavHideOnTouch:false,
    globalCaption: true,
	  block: {
      fadeEffect: true,
    },
    autoPlay: {
      enabled: true,
      pauseOnHover: true,
      delay: 3000
    },
    transitionType: 'move',
    slidesSpacing: 0,
  });
  jQuery(window).on('resize', getWidthAndHeight).resize();

  if (typeof picturefill == "function") {
    jQuery('#home-slider').data('royalSlider').ev.on('rsBeforeMove', function(event, type, userAction ) {
      picturefill({
        reevaluate: true
      });
    });
  }
}

function royalsliderMobileBind() {
  "use strict";
  jQuery(window).off('resize', getWidthAndHeight);

  jQuery('.homeCarouselContainer').css({'width': '','height': jQuery('.rsImg').first().height()});

  jQuery('#home-slider').royalSlider({
    arrowsNav: true,
    controlNavigation: 'none',
    keyboardNavEnabled: true,
    controlsInside: false,
    imageScaleMode: 'fill',
    imageAlignCenter: true,
    autoScaleSlider: true,
    autoScaleSliderWidth: 1000,
    autoScaleSliderHeight: 800,
    imgWidth: 1000,
    imgHeight: 800,
    loop: true,
    numImagesToPreload: 1,
    thumbsFitInViewport: false,
    navigateByClick: true,
    arrowsNavAutoHide:true,
    arrowsNavHideOnTouch:false,
    autoPlay: {
      enabled: false,
      pauseOnHover: true,
      delay: 10000
    },
    globalCaption: true,
    block: {
      fadeEffect: true,
    },
    transitionType: 'move',
    slidesSpacing: 0,
  });
  /*jQuery('#home-slider').css({'height': ''});*/
}

// Binding
var iconToggled = false;
jQuery(window).bind('resize', function(){
  var printVal = 'in the resize bind';

  if(Modernizr.mq('( max-width: 767px )')) {
    if (!iconToggled) {
      iconToggled = true;
      jQuery(".home-gridDisplay-leftCol-indContainer").each(function (index, element) {
        jQuery("div.home-gridDisplay-leftCol-logoWrapper", element).insertAfter(jQuery("div.home-gridDisplay-leftCol-titleContainer", element));
      });
    }
  }
  else {
    if (iconToggled) {
	    iconToggled = false;
      jQuery(".home-gridDisplay-leftCol-indContainer").each(function (index, element) {
        jQuery("div.home-gridDisplay-leftCol-logoWrapper", element).insertAfter(jQuery("div.home-gridDisplay-leftCol-contentWrapper", element));
      });
    }
  }
}).resize();

function equalheight(container){

  var currentTallest = 0,
    currentRowStart = 0,
    rowDivs = new Array(),
    $el,
    topPosition = 0;
  jQuery(container).each(function() {

    $el = jQuery(this);
    jQuery($el).height('auto')
    topPostion = $el.position().top;

    if (currentRowStart != topPostion) {
      for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
        rowDivs[currentDiv].height(currentTallest);
      }
      rowDivs.length = 0; // empty the array
      currentRowStart = topPostion;
      currentTallest = $el.height();
      rowDivs.push($el);
    } else {
      rowDivs.push($el);
      currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
    }
    for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
      rowDivs[currentDiv].height(currentTallest);
    }
  });
}
