// Binding
var midRangeToggled = false;
jQuery(document).ready(function() {
  setupOrganicsResize();

  // Added subsection class to avoid hiding the nav on a non-subsection page with a polluted js include.
  jQuery('.subsection  #generalNav').hide();
  jQuery('.collapsed-headerTrigger').click(collapsedHeaderClickHandler);

  // "scrollTop" plugin
  if ("function" == typeof jQuery.scrollUp) {
    jQuery.scrollUp();
  }

  // "colio" plugin
  if ("function" == typeof jQuery().colio) {
    jQuery('.mainOrganics-lowerContentRegion-grid-wrapper').colio({
      id: 'colio',
      theme: 'white',
      placement: 'before',
    });
  }

  if (typeof jQuery().stick_in_parent == "function") {
    jQuery("#sectionNav").stick_in_parent({
      offset_top: 0,
      parent: ".sectionWrapper",
      spacer: ".sticky-spacer",
    })
      .on("sticky_kit:unstick", resizeStickySpacer);
  }
});

function resizeStickySpacer() {
  var $img = jQuery('.sectionNav-logoContainer img'),
    imgHeight = $img.height(),
    imgWidth = $img.width(),
    $container = jQuery('.sectionNav-logoContainer'),
    imgPadding = parseInt($container.css('padding-top')) + parseInt($container.css('padding-bottom'));

  jQuery('.sticky-spacer').css('height', (imgHeight / imgWidth * 375) + imgPadding + 'px');
}

function setupOrganicsResize() {
  var $organicMain = jQuery('.mainOrganics-upperContentRegion-leftCol-container img'),
    $organicMainBG = jQuery('<div/>', {
      style: 'background-image: url(' + $organicMain.attr('src') + ')',
      class: 'mainOrganics-upperContentRegion-leftCol-container-imageReplace'
    });

  jQuery(window).on('resize', {$img1: $organicMain, $bg1: $organicMainBG, minWidth: '1200px'}, organicsResizeHandler).resize();
}

function organicsResizeHandler(event) {
  if (Modernizr.mq('( min-width:' + event.data.minWidth + ' )')) {
    if (!jQuery('.mainOrganics-upperContentRegion-leftCol-container-imageReplace').length) {
      event.data.$img1.replaceWith(event.data.$bg1);
    }
  }
  else {
    if (jQuery('.mainOrganics-upperContentRegion-leftCol-container-imageReplace').length) {
      event.data.$bg1.replaceWith(event.data.$img1);
    }
  }

  if(Modernizr.mq('( max-width: 1199px )')) {
    if (!midRangeToggled) {
      midRangeToggled = true;
      jQuery("div.mainOrganics-upperContentRegion-rightCol-captionContainer").insertAfter("div.mainOrganics-upperContentRegion-leftCol-container img");
      jQuery("div.mainOrganics-upperContentRegion-leftCol-container").insertAfter("div.mainOrganics-upperContentRegion-rightCol-container");
      jQuery(".organics .searchFormPanelFormContainer").insertAfter("#sectionMenu ul.adwMenu");
    }
  }
  else {
    if (midRangeToggled) {
      midRangeToggled = false;
      jQuery("div.mainOrganics-upperContentRegion-rightCol-captionContainer").insertAfter("div.mainOrganics-upperContentRegion-rightCol-captionSpacer");
      jQuery("div.mainOrganics-upperContentRegion-leftCol-container").insertBefore("div.mainOrganics-upperContentRegion-rightCol-container");
      jQuery(".organics .searchFormPanelFormContainer").insertAfter(".organics .searchFormPanelTxtContainer");
      resizeStickySpacer();
    }
  }
}

function collapsedHeaderClickHandler() {
  jQuery('#collapsedNav').hide();
  jQuery('#generalNav').show();
  jQuery('.sectionMenu-search-wrapper').fadeToggle();
}
