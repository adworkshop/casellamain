/* ======================================================= */
/*                        ADWORKSHOP MENU                  */
/* ======================================================= */
jQuery('.mobileBtnWrapper a.mainMenuToggle').toggle(function() { 
jQuery("nav#mainMenu").slideDown(200, 'easeInSine');
jQuery(this).addClass('active');
},
 function() { 
 jQuery("nav#mainMenu").slideUp(200, 'easeOutSine');
 jQuery(this).removeClass('active');
});

jQuery('nav.menuContainer .openChild').toggle(function(){   
jQuery(this).parent().parent().find(".over").find('.openChild').click();
jQuery(this).parent().addClass('over').find('>ul').slideDown(200);
},function(){
	jQuery(this).parent().removeClass('over').find('>ul').slideUp(200);
});


jQuery('nav#mainMenu ul > li:last-child').addClass('last');