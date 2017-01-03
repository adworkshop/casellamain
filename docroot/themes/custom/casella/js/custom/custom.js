jQuery(document).ready(function() {
  "use strict";

  var lowerNavContainer = jQuery(".footer-lowerNav-container"),
    newSelect = jQuery("<select />");
  // Footer Navigation Convert to Select Box
  jQuery("<option />", {
    "selected": "selected",
    "value"   : "",
    "text"    : "QuickLinks..."
  }).appendTo(newSelect);
  jQuery("a", lowerNavContainer).each(function(index, element) {
    var $el = jQuery(element);
    jQuery("<option />", {
      "value"   : $el.attr("href"),
      "text"    : $el.text()
    }).appendTo(newSelect);
  });

  newSelect.appendTo(lowerNavContainer);

  jQuery("div.hpr-lowerNavBarWrapper select").change(function() {
    window.location = jQuery(this).find("option:selected").val();
  });

  // Search Box
  jQuery(".searchPanel_open").click(function() {
    jQuery("#searchFormModulePanel").fadeIn("fast");
  });

  jQuery(".searchPanel_close").click(function() {
    jQuery("#searchFormModulePanel").fadeOut("fast");
  });

  if (typeof jQuery().fitVids == "function") {
    jQuery(".videoContainer").fitVids();
  }

  if (typeof jQuery().zozoTabs == "function") {
    initZozoTabs();
  }

  // Locations init.
  initLocationHandlers();

  // Services tab init.
  if ("function" == typeof jQuery().zozoTabs) {
    initNestedTabs();
  }

  // Add the required class to the *s in field element labels.
  jQuery('label.form-required').each(function(index, element){
    jQuery(element).html(jQuery(element).html().replace('*', '<span class="form-required">*</span>'));
  });

  // make sure addThis picks up any hash changes from tab deep-linking
  /*
  jQuery(function(){
      jQuery(window).hashchange(function(){
          if(jQuery('div.addThis-container').length){
              addthis.ost = 0;
              addthis.update('share', 'url', window.location.href); // new url
              addthis.update('share', 'title', window.document.title); // new title.
              addthis.ready(); // this will re-render the buttons.
          }
      });
  });
  */
});

function initZozoTabs() {
  jQuery("#tabbed-nav").zozoTabs({
    position: "top-left",
    size: "xxlarge",
    multiline: false,
    style: "contained",
    theme: "flat-nephritis",
    spaced: false,
    rounded: false,
    bordered: false,
    responsive: true,
    minWindowWidth: 1199,
    responsiveDelay: 0,
    animation: {
      easing: "easeInOutExpo",
      duration: 600,
      effects: "slideH"
    }
  });
}

/**
 * Get the url variables.
 *
 * @returns {{}}
 */
function getUrlVars() {
  var args = location.search, i=0, tempArg, retVal = {};

  if ('' == args) {
    return {};
  }

  args = args.replace(/^\?/, '').split('&');
  for (i = 0; i < args.length; i++) {
    tempArg = args[i].split('=');
    if (2 == tempArg.length) {
      retVal[tempArg[0]] = tempArg[1];
    }
  }

  return retVal;
}

// Binding
var midToggled = false;
jQuery(window).bind('resize', function(){
  var printVal = 'in the resize bind';

  if(Modernizr.mq('( max-width: 1199px )')) {
    if (!midToggled) {
      midToggled = true;
      jQuery("div.searchFormPanelFormContainer").insertAfter("#mainMenu ul.adwMenu");
      jQuery("div.navGlobal-container").insertBefore("div.searchFormPanelFormContainer");
    }
  }
  else {
    if (midToggled) {
      midToggled = false;
      jQuery("div.searchFormPanelFormContainer").insertAfter("div.searchFormPanelTxtContainer");
      jQuery("div.navGlobal-container").insertBefore("div.navContainer");
    }
  }
}).resize();

// Clean function to remove unwatned nodes from inside a DOM node (Support for inline-block grid layout)
var utils = {};
utils.clean = function(node) {
  "use strict";
  var child, i, len = node.childNodes.length;
  if (len === 0) { return; }
  // iterate backwards, as we are removing unwanted nodes
  for (i = len; i > 0; i -= 1) {
    child = node.childNodes[i - 1];
    // comment node? or empty text node
    if (child.nodeType === 8 || (child.nodeType === 3 && !/\S/.test(child.nodeValue) )) {
      node.removeChild(child);
    }
    else {
      if (child.nodeType === 1) {
        utils.clean(child);
      }
    }
  }
};
// Add .inline-block-grid-parent class where needed
jQuery("").addClass("inline-block-grid-parent");
// Run divs with class .inline-block-grid-parent through clean function
jQuery(".inline-block-grid-parent").each( function(){
  "use strict";
  utils.clean(this);
});


// SCROLLING ELEMENTS
var mainNavWrapper = jQuery('#generalNav');
var mainNav = jQuery('.navContainer');
var scrollState = 'top';

jQuery(window).scroll(function(){
  "use strict";
  var scrollPos = jQuery(window).scrollTop();
  if (scrollPos !== 0) {
    if (scrollState === 'top') {
      scrollState = 'scrolled';
      mainNav.addClass('scrollIn');
      mainNavWrapper.addClass('scrollIn');
    }

  }
  else if(scrollPos === 0) {
    if (scrollState === 'scrolled'){
      scrollState = 'top';
      mainNav.removeClass('scrollIn');
      mainNavWrapper.removeClass('scrollIn');
    }
  }
});

function initLocationHandlers() {
  jQuery('.holidayPanelTrigger').click(function() {
    jQuery('.indLocation-holidayPanel').slideToggle('slow');
    jQuery('.holidayPanelTrigger').addClass('hiddenBtn');
  });

  jQuery('.indLocation-holidayPanel-close').click(function() {
    jQuery('.indLocation-holidayPanel').slideToggle('slow');
    jQuery('.holidayPanelTrigger').removeClass('hiddenBtn');
  });

  jQuery('.indLocation-contactPanel-trigger').click(function() {
    jQuery('.indLocation-contactPanel').slideToggle('slow');
    jQuery('.indLocation-contactPanel-trigger').toggleClass('active');
  });

  if ("function" == typeof jQuery().footable) {
    jQuery('#holidaySchedule').footable();
  }
}

/**
 * Checks for the nested zozotab structure in the services tab and inits if it exists.
 */
function initNestedTabs() {
  var nestedWrapper = jQuery(".nestedTabs-wrapper");
  if (!nestedWrapper.length) {
    return;
  }
  nestedWrapper.zozoTabs({
    position: "top-left",
    orientation: "vertical",
    multiline: false,
    style: "contained",
    theme: "flat-nephritis",
    spaced: true,
    bordered: false,
    rounded: false,
    responsive: true,
    minWindowWidth: 1199,
    animation: {
      easing: "easeInOutExpo",
      duration: 450,
      effects: "slideH"
    }
  });
}
