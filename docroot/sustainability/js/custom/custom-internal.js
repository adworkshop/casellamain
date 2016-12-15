jQuery(document).ready(function() {
	
	// SVG Fallbacks
	if (!Modernizr.svg) {
	  jQuery(".header-logo img").attr("src", "i/logo/logo.nav.png");
	  jQuery(".recycleGraphImgContainer img").attr("src", "img/CommodityPricing.png");
	};
});

// Bind
  jQuery(window).bind('resize', function(){
	  if(jQuery(this).width() < 1085)
		  jQuery('.organicsLowerContainerLeft').insertAfter('.organicsLowerContainerRight');
	  else 
	   jQuery('.organicsLowerContainerLeft').insertBefore('.organicsLowerContainerRight');
	   
	   if(jQuery(this).width() < 1005)
		  jQuery('.carbonTabEmissionCol1').insertBefore('.carbonTabEmissionCol2'),
		  jQuery('#mapKey').insertAfter('#keyMobile');
	  else 
	   jQuery('.carbonTabEmissionCol1').insertAfter('.carbonTabEmissionCol2'),
	   jQuery('#mapKey').insertAfter('#mapTitle');
	   
	   if(jQuery(this).width() < 805)
		  jQuery('.landfillGasEnergyGraphicTxtWrapperCol1Container').insertAfter('.landfillGasEnergyDescriptionCol2'),
		  jQuery('.landfillGasEnergyGraphicTxtWrapperCol1Container2').addClass('mobile'),
		  jQuery('.landfillGasEnergyGraphicTxtWrapperCol1Container2Inner').insertAfter('.landfillGasEnergyDescriptionCol3'),
		  jQuery('.landfillGasEnergyGraphicTxtWrapperCol1Container2Inner').addClass('mobile');
	  else 
	   jQuery('.landfillGasEnergyGraphicTxtWrapperCol1Container').insertAfter('.txt1Space'),
	   jQuery('.landfillGasEnergyGraphicTxtWrapperCol1Container2').removeClass('mobile'),
	   jQuery('.landfillGasEnergyGraphicTxtWrapperCol1Container2Inner').insertAfter('.txt2Space'),
	   jQuery('.landfillGasEnergyGraphicTxtWrapperCol1Container2Inner').removeClass('mobile');
	   
	   
	   
	  if(jQuery(this).width() < 585)
		jQuery('#mapTitle').insertBefore('.internalPageTitleDescriptionNoColor');
	  else 
		jQuery('#mapTitle').insertAfter('#titleSpacer');
	   
  }).resize();



// Make sure div stays full width/height on resize// make sure div stays full width/height on resize
  jQuery(window).resize(function() {
	getHeight();
  });

  function getHeight (){
	var winHeight = jQuery(window).height();
	jQuery('.serviceUpperContainer').css({'min-height': winHeight,});
	jQuery('.serviceContentInnerContainer').css({'min-height': winHeight,});
  }