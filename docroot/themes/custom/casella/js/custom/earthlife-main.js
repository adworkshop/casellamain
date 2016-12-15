var earthlifeMidRangeToggled = false;

jQuery(document).ready(function() {
  jQuery(window).bind('resize', earthlifeResizeBind).resize();

  if (typeof jQuery().stick_in_parent == "function") {
    jQuery("#sectionNav").stick_in_parent({
      offset_top: 0,
      parent: ".sectionWrapper",
      spacer: ".sticky-spacer",
      no_recalc: true
    });
  }

  jQuery(window).load(function() {
    equalheight('.productGrid-indProduct-title');
    equalheight('.mainEarthlife-featuredProducts-txtContainer');
  });

  jQuery(window).resize(function(){
    equalheight('.productGrid-indProduct-title');
    equalheight('.mainEarthlife-featuredProducts-txtContainer');
  });

  jQuery('#generalNav').hide();
  jQuery('.collapsed-headerTrigger').click(collapsedHeaderClickHandler);
});

function earthlifeResizeBind() {
  if(Modernizr.mq('( max-width: 1199px )')) {
    if (!earthlifeMidRangeToggled) {
      earthlifeMidRangeToggled = true;
      jQuery(".earthlife .searchFormPanelFormContainer").insertAfter("#sectionMenu ul.adwMenu");
      jQuery(".earthlife.withProductSidebar .internal-mainBody-sidebarContainer").insertAfter(".internal-mainBody-contentContainer");
      jQuery(".indProduct-lowerContent-rightCol-image-container").insertAfter(".internal-headerPanel-container");
    }
  }
  else {
    if (earthlifeMidRangeToggled) {
      earthlifeMidRangeToggled = false;
      jQuery(".earthlife .searchFormPanelFormContainer").insertAfter(".earthlife .searchFormPanelTxtContainer");
      jQuery(".earthlife.withProductSidebar .internal-mainBody-sidebarContainer").insertBefore(".internal-mainBody-contentContainer");
      jQuery(".indProduct-lowerContent-rightCol-image-container").insertAfter(".indProduct-lowerContent-rightCol-image-spacer");
    }
  }
}

function equalheight(container){

  var currentTallest = 0,
    currentRowStart = 0,
    rowDivs = new Array(),
    jQueryel,
    topPosition = 0;
  jQuery(container).each(function() {

    jQueryel = jQuery(this);
    jQuery(jQueryel).height('auto')
    topPostion = jQueryel.position().top;

    if (currentRowStart != topPostion) {
      for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
        rowDivs[currentDiv].height(currentTallest);
      }
      rowDivs.length = 0; // empty the array
      currentRowStart = topPostion;
      currentTallest = jQueryel.height();
      rowDivs.push(jQueryel);
    } else {
      rowDivs.push(jQueryel);
      currentTallest = (currentTallest < jQueryel.height()) ? (jQueryel.height()) : (currentTallest);
    }
    for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
      rowDivs[currentDiv].height(currentTallest);
    }
  });
}

function collapsedHeaderClickHandler() {
  jQuery('#collapsedNav').hide();
  jQuery('#generalNav').show();
  jQuery('.sectionMenu-search-wrapper').fadeToggle();
}

/*
jQuery(document).ready(function(){
  jQuery('input[type="checkbox"]').click(function(){
    if(jQuery(this).attr("value")=="agriculture"){
      jQuery("#agriculture").toggle();
    }
    if(jQuery(this).attr("value")=="soil"){
      jQuery("#customSoil").toggle();
    }
    if(jQuery(this).attr("value")=="landscape"){
      jQuery("#landscapeTurf").toggle();
    }
    if(jQuery(this).attr("value")=="organic"){
      jQuery("#organic").toggle();
    }
  });
});
*/
