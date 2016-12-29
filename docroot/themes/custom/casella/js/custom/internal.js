"use strict";

// ROYAL SLIDER - TOP/BOTTOM LARGE
jQuery(document).ready(function() {
  //LARGE - TOP/BOTTOM SLIDER
  if (jQuery('#internal-slider').length && typeof jQuery().royalSlider == "function") {
    initFullWidthSlider();
  }

  //IN CONTENT - NESTED TOP/BOTTOM SLIDER
  if (jQuery('#content-slider').length && typeof jQuery().royalSlider == "function") {
    initNestedSlider();
  }

  if (jQuery('.internal-fullWidthPromo-imageContainer img').length) {
    setupFullWidthPromo();
  }

  if ("function" == typeof jQuery().footable) {
    jQuery('#providerListings').footable();
  }

  jQuery(".job-indListing-applyToggle a").click(jobApplyToggleHandler);

  jQuery('.productGrid-filter-container input[type="checkbox"]').click(function(event){
    jQuery('#' + jQuery(event.currentTarget).val()).toggle();
  });

  // Check the sidebar for emptyness.
  checkSidebarContents();

  if (jQuery('#edit-field-service-service-request').length) {
    checkServiceRequestPreset();
  }
});

function checkSidebarContents() {
  var $sidebar = jQuery('.internal-mainBody-sidebarContainer');
  if (!$sidebar.length) {
    return;
  }

  if ('' == $sidebar.html().replace(/<!--((?!-->).)*-->/g, '').trim()) {
    $sidebar.remove();
    jQuery('.internal-mainBody-container').removeClass('withSidebar').removeClass('noSidebarNav');
  }
}

function jobApplyToggleHandler(){
  jQuery(".job-indListing-formToggle-container").fadeToggle("slow");
  jQuery(".job-indListing-applyToggle a").fadeToggle("slow");
  jQuery('html, body').animate({ scrollTop: jQuery('.job-indListing-formToggle-container').offset().top - 50}, 1000);
}

function initFullWidthSlider() {
  jQuery('#internal-slider').royalSlider({
    arrowsNav: true,
    controlNavigation: 'none',
    keyboardNavEnabled: true,
    controlsInside: false,
    imageScaleMode: 'fill',
    imageAlignCenter: true,
    autoScaleSlider: true,
    autoScaleSliderWidth: 1500,
    autoScaleSliderHeight: 675,
    imgWidth: 1500,
    imgHeight: 675,
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
      moveEffect: 'none',
      speed: 500,
      moveOffset: 100,
      easing: 'easeOutSine',
      delay: 5000,
    },
    transitionType: 'move',
    slidesSpacing: 0
  });

  relocateCaptions();
}

function initNestedSlider() {
  //IN CONTENT - NESTED TOP/BOTTOM SLIDER
  jQuery('#content-slider').royalSlider({
    autoHeight: true,
    arrowsNav: true,
    fadeinLoadedSlide: false,
    controlsInside: false,
    arrowsNavAutoHide:true,
    arrowsNavHideOnTouch:false,
    controlNavigation: 'none',
    navigateByClick: true,
    imageScaleMode: 'none',
    imageAlignCenter:false,
    loop: false,
    loopRewind: true,
    numImagesToPreload: 1,
    keyboardNavEnabled: true,
    usePreloader: true,
    slidesSpacing: 30,
    globalCaption: true,
    block: {
      fadeEffect: true,
      moveEffect: 'none',
      speed: 500,
      moveOffset: 100,
      easing: 'easeOutSine',
      delay: 5000,
    },
  });

  relocateCaptions();
}

function relocateCaptions() {
  //CAPTION RELOCATION
  jQuery('.rsGCaption').appendTo('.internal-slider-caption-wrapper');
}


function setupFullWidthPromo() {
  var $img1 = jQuery('.internal-fullWidthPromo-imageContainer img.background'),
      $bg1 = jQuery('<div/>', {
        style: 'background-image: url('+$img1.attr('src')+')',
        class: 'internal-fullWidthPromo-imageContainer-imageReplace'
      });

  jQuery(window).on('resize', {$img1: $img1, $bg1: $bg1}, fullWidthPromoImageManager).resize();
}

function fullWidthPromoImageManager(event) {
  if (Modernizr.mq('( min-width:768px )')) {
    if (!jQuery('.internal-fullWidthPromo-imageContainer-imageReplace').length) {
      event.data.$img1.replaceWith(event.data.$bg1);
    }
    return;
  }

  if (jQuery('.internal-fullWidthPromo-imageContainer-imageReplace').length) {
    event.data.$bg1.replaceWith(event.data.$img1);
  }
}

// Window Resize Functions
jQuery(window).resize(function() {
  getWidthAndHeight();
});
  
function getWidthAndHeight (){
  var winHeight = jQuery(window).height();
  jQuery('.internal-fullWidthPromo-imageContainer').css({'min-height': winHeight * (1/2)});
  jQuery('.locationMap-contentCol-wrapper').css({'min-height': winHeight * (1/2)});
}

// Binding
var mapToggled = false;
jQuery(window).bind('resize', function(){
  var printVal = 'in the resize bind';  

  if(Modernizr.mq('( max-width: 1023px )')) {
    if (!mapToggled) {
      mapToggled = true;
      jQuery(".panelControllers").insertAfter(".locationMap-contentCol-container");
    }
  }
  else {
    if (mapToggled) {
      mapToggled = false;
      jQuery(".panelControllers").insertAfter(".locationMap-mapSpacer");
    }
  }
}).resize();

// FancyBox Gallery...
if (typeof jQuery.fancybox != 'undefined'){
  jQuery(".fancybox-button").fancybox({
    prevEffect : 'fade',
    nextEffect : 'fade',
    closeBtn : false,
    padding:0,
    helpers   : {
      title  : { type: 'outside' },
      buttons  : {},
      overlay : {locked : false}
    }
  });
  
  jQuery(".various").fancybox({
    fitToView  : true,
    width    : '80%',
    height    : '80%',
    autoSize  : false,
    closeClick  : false,
    openEffect  : 'none',
    closeEffect  : 'none'
  });
}

/**
 * Check the URL for a service request variable and use it to prepopulate the form field.
 */
function checkServiceRequestPreset() {
  var selection = location.search.match(/(?:\?|&)service_request=([^&]+)(?:&|$)/);

  if (selection) {
    jQuery('#edit-field-service-service-request').val(selection[1]);
  }
}
