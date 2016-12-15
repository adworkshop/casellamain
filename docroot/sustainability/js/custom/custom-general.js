  jQuery(document).ready(function() {
	// ui to top
	jQuery().UItoTop({ easingType:'easeOutQuart'});
  });

// Clean function to remove unwanted nodes from inside a DOM node (support for inline-block grid system)
  var utils = {};
  utils.clean = function(node) {
	var child, i, len = node.childNodes.length;
	if (len === 0) { return; }
	// iterate backwards, as we are removing unwanted nodes
	for (i = len; i > 0; i -= 1) {
	  child = node.childNodes[i - 1];
	  // comment node? or empty text node
	  if (child.nodeType === 8 || (child.nodeType === 3 && !/\S/.test(child.nodeValue) )) {
		node.removeChild(child);
	  } else {
		if (child.nodeType === 1) {
		  utils.clean(child);
		}
	  }
	}
  };

// Add .inlineBlockParent class to webforms where needed
  jQuery('.recycleGrid, .indAFCContainer, .keyMobileContainer .mapKey, .homeSection2Container').addClass('inlineBlockGridParent');
	// Run divs with class .inlineBlockParent through clean function
	jQuery(".inlineBlockGridParent").each( function(){ 
	  utils.clean(this);
  });

// BINDS
jQuery(window).bind('resize', function(){
	if(Modernizr.mq('(min-width: 1100px)')){
	  jQuery( window ).scroll( function() {
		var scroll = jQuery(window).scrollTop(), slowScroll = scroll / 1.5;
		jQuery( ".parallax" ).css({ transform: "translateY(" + slowScroll + "px)" });
	  });
	  
	  if (typeof jQuery(window).fadeThis != 'undefined'){
		  jQuery(window).fadeThis({reverse: false});
	  };
	}
}).resize();

//Service Icon Replcements
  function onHoverRecycle() {jQuery("#menuRecycleImg").attr('src', 'i/general/recycleIcon.png');}
  function offHoverRecycle() {jQuery("#menuRecycleImg").attr('src', 'i/general/recycleIcon-nav.png');}
  
  function onHoverCollection() {jQuery("#menuCollectionImg").attr('src', 'i/general/collectionIcon.png');}
  function offHoverCollection() {jQuery("#menuCollectionImg").attr('src', 'i/general/collectionIcon-nav.png');}
  
  function onHoverOrganics() {jQuery("#menuOrganicsImg").attr('src', 'i/general/organicsIcon.png');}
  function offHoverOrganics() {jQuery("#menuOrganicsImg").attr('src', 'i/general/organicsIcon-nav.png');}
  
  function onHoverEnergy() {jQuery("#menuEnergyImg").attr('src', 'i/general/EnergyIcon.png');}
  function offHoverEnergy() {jQuery("#menuEnergyImg").attr('src', 'i/general/EnergyIcon-nav.png');}
  
  function onHoverLandfills() {jQuery("#menuLandfillsImg").attr('src', 'i/general/landfillsIcon.png');}
  function offHoverLandfills() {jQuery("#menuLandfillsImg").attr('src', 'i/general/landfillsIcon-nav.png');}