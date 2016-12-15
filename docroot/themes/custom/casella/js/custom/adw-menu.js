/* ======================================================= */
/*                        ADWORKSHOP MENU                  */
/* ======================================================= */

// Needs to start closed.
jQuery(document).ready(function(){
  closeMobileMenu(jQuery('.mobileBtnWrapper a.mainMenuToggle'), "nav#mainMenu");
  closeMobileMenu(jQuery('.mobileBtnWrapper a.sectionMenuToggle'), "nav#sectionMenu");
});

// Bind the mobile main nav toggle click.
jQuery('.mobileBtnWrapper a.mainMenuToggle').on('click', function(event) {
  // Force the toggle to finish.
  jQuery('nav#mainMenu').stop(true, true);
  if (jQuery(event.currentTarget).hasClass('active')) {
    closeMobileMenu(event.currentTarget, "nav#mainMenu");
  }
  else {
    openMobileMenu(event.currentTarget, "nav#mainMenu");
  }
});

// Bind the mobile section nav toggle click.
jQuery('.mobileBtnWrapper a.sectionMenuToggle').on('click', function(event) {
  // Force the toggle to finish.
  jQuery('nav#sectionMenu').stop(true, true);
  if (jQuery(event.currentTarget).hasClass('active')) {
    closeMobileMenu(event.currentTarget, "nav#sectionMenu");
  }
  else {
    openMobileMenu(event.currentTarget, "nav#sectionMenu");
  }
});

function openMobileMenu(element, selector) {
  jQuery(selector).slideDown(200, 'easeInSine');
  jQuery(element).addClass('active');
}

function closeMobileMenu(element, selector) {
  jQuery(selector).slideUp(200, 'easeOutSine');
  jQuery(element).removeClass('active');
}

jQuery('nav.menuContainer .openChild').on('click', openChildClick);

function openChildClick(event) {
  var $parent = jQuery(event.currentTarget).parent(),
      $targetUl = $parent.find('>ul');

  $targetUl.stop(true, true);
  if ($parent.hasClass('over')) {
    $parent.removeClass('over');
    $targetUl.slideUp(200);

    return;
  }

  $parent.siblings('li.over').each(function(index, element) {
    jQuery(element).find('> ul').slideUp(200);
    jQuery(element).removeClass('over');
  });

  $parent.addClass('over');
  $targetUl.slideDown(200);
}

jQuery('nav#mainMenu ul > li:last-child').addClass('last');
jQuery('nav#sectionMenu ul > li:last-child').addClass('last');