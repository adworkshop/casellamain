sliderSwap = {
	curSlider: '',
	originalStructure: '',
}

/**
 * Add custom slider init
 **/
function init_slider(target, defaults, override, callback){
	if (jQuery(target).length !== 1) return false;
	if (typeof defaults == "undefined") return false;
	override = typeof override != "undefined" ? override : {};
	
	jQuery(target).royalSlider(jQuery.extend({}, defaults, override))
	if (typeof callback == "function") callback();
}

/**
 * pulled this into a function to allow customization on destroy.
 **/
function destroy_slider(target){
	if (typeof jQuery(target).data('royalSlider') != "undefined")	jQuery(target).data('royalSlider').destroy()
}

/**
 * @arguments
 *  target:			jQuery selector that targets the slider div
 *  widthBreak: integer in pixels at or below which the smaller slider function will be called
 *  callback:		optional - function to pass to the inits
 **/
function initSliderSwap(target, widthBreak, defaults, largeOverride, smallOverride, callback){
	//make sure the target is valid
	if (jQuery(target).length !== 1) return false; 
	//we need it to be a number, and it shouldnt be a float
	if (Number(widthBreak) !== widthBreak || widthBreak%1!==0) return false;
	
	//grab the html for the reset
	sliderSwap.originalStructure = jQuery(target).html();
	
	//bind the resize
	jQuery(window).bind('resize', function(){
		// <= widthBreak = mobile
		if(Modernizr.mq('(min-width: ' + widthBreak + 'px)')) {
			if (sliderSwap.curSlider != 'large'){
				if (sliderSwap.curSlider != ''){
					destroy_slider(target);
					jQuery(target).html(sliderSwap.originalStructure);
				}
				//set the flag
				sliderSwap.curSlider = 'large';
				//run the init
				init_slider(target, defaults, largeOverride, callback)
				//this pokes the royalslider to make sure it resizes correctly
				jQuery(window).resize()
			}
		}
		else {
			if (sliderSwap.curSlider != 'small'){
				if (sliderSwap.curSlider != ''){
					destroy_slider(target);
					jQuery(target).html(sliderSwap.originalStructure);
				}
				//set the flag
				sliderSwap.curSlider = 'small';
				//run the init
				init_slider(target, defaults, smallOverride, callback)
				//this pokes the royalslider to make sure it resizes correctly
				jQuery(window).resize()
			}
		}
	}).resize();
}

jQuery(document).ready(function royalSliderSwapDocReady(){
	//arguments: target, widthbreak, optional function callback to fire after every init
	var defaults, largeOverride, smallOverride;
	defaults = {
		arrowsNav: false,
		controlNavigation: 'none',
		keyboardNavEnabled: true,
		controlsInside: false,
		imageScaleMode: 'fill',
		imageAlignCenter: true,
		loop: false,
		numImagesToPreload: 0, 
		thumbsFitInViewport: false,
		navigateByClick: true,
		arrowsNavAutoHide:false,
		arrowsNavHideOnTouch:false,
		autoPlay: {
		// autoplay options go gere
		enabled: false,
		pauseOnHover: false,
		delay: 6000
		},
		transitionType: 'fade',
		slidesSpacing: 0
	}
	
	largeOverride = {
		autoScaleSlider: false,
	}
	
	smallOverride = {
	  autoScaleSlider: true,
	}
	
	retFunc = function(){
		jQuery('#service-slider').css('height', 'auto');
	}
	initSliderSwap('#service-slider', 1100, defaults, largeOverride, smallOverride, retFunc)
});