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

  jQuery(".footer-lowerNav-container select").change(function() {
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

  // add file download markup to job application
  jQuery('#edit-field-application-0-upload').append('<div class="file-download"><span><a href="pdf/HR-Application-English-v09-FILL.pdf" target="_blank" title="Download the Application">Download the application (PDF)</a>,<br>once completed and saved, click "Choose File" to submit.</span></div></div>');
  document.getElementById('edit-field-application-0-upload--description').innerHTML = 'This pdf must be completed and submitted in its entirety to be considered for employment.'+document.getElementById('edit-field-application-0-upload--description').innerHTML.replace(/<br\s*[\/]?>/gi, "\n");

});

function initZozoTabs() {
  // Check if this is supposed to be deeplinked.
  var args = getUrlVars(), defaultTab = 'tab1', tabTitles = jQuery('#tabbed-nav li'), i = 0;

  if (typeof args.tab != "undefined" && jQuery('li#' + args.tab).length) {
    for (i = 0; i < tabTitles.length; i++) {
      if (args.tab == jQuery(tabTitles[i]).attr('id')) {
        defaultTab = 'tab' + (i + 1);
        break;
      }
    }
  }

  jQuery("#tabbed-nav").zozoTabs({
    defaultTab: defaultTab,
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
    },
    select: updateUrlArgs
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

/**
 * Update the current Url's arguments.
 * @param event
 */
function updateUrlArgs(event, item) {
  if ("function" != typeof window.history.replaceState) {
    return;
  }

  if (!jQuery("#tabbed-nav").hasClass('inited')) {
    jQuery("#tabbed-nav").addClass('inited');
    return;
  }

  var newArgs = '', propt, args = getUrlVars();
  args.tab = jQuery(item.tab[0]).attr('id');

  for (propt in args) {
    if (args.hasOwnProperty(propt)) {
      if ('' == newArgs) {
        newArgs = '?';
      }
      else {
        newArgs = newArgs + '&';
      }

      newArgs = newArgs + propt + '=' + args[propt];
    }
  }

  window.history.replaceState('', '', location.pathname + newArgs + '#tabs');

  // Make sure addThis picks up any hash changes from tab deep-linking.
  if(jQuery('div.addThis-container').length) {
    addthis.ost = 0;
    addthis.update('share', 'url', location.pathname + newArgs + '#tabs'); // new url
  }
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
    eventTracking('location holiday schedule','expand click','');
  });

  jQuery('.indLocation-holidayPanel-close').click(function() {
    jQuery('.indLocation-holidayPanel').slideToggle('slow');
    jQuery('.holidayPanelTrigger').removeClass('hiddenBtn');
    eventTracking('location holiday schedule','collapse click','');
  });

  jQuery('.indLocation-contactPanel-trigger').click(function() {
    jQuery('.indLocation-contactPanel').slideToggle('slow');
    jQuery('.indLocation-contactPanel-trigger').toggleClass('active');

    event_action = 'collapse click';
    event_label = jQuery(this).text();
    if (jQuery(this).hasClass('active')) event_action = 'expand click';
    eventTracking('contact us form',event_action,event_label);

  });

  if ("function" == typeof jQuery().footable) {
    jQuery('#holidaySchedule').footable();
  }
}

/**
 * Checks for the nested zozotab structure in the services tab and inits if it
 * exists.
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
