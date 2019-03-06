
/*---------------------------------------------------------------*/
/*-- Global DOC READY -------------------------------------------*/
/*---------------------------------------------------------------*/

(function($){
  "use strict";
  $(document).ready(function(){
    
    
    /* ADW Menu
    -------------------------------------------------------------------*/
    closeMobileMenu($('.mobileBtnWrapper a.mainMenuToggle'), "nav#mainMenu");

      // Bind the mobile main nav toggle click.
      $('.mobileBtnWrapper__icon').on('click', function(event) {
        // Force the toggle to finish.
        $('nav#mainMenu').stop(true, true);
        if ($(event.currentTarget).hasClass('open')) {
          closeMobileMenu(event.currentTarget, "nav#mainMenu");
        }
        else {
          openMobileMenu(event.currentTarget, "nav#mainMenu");
        }
      });

      //Allow non-touch devices to get the hover on rollover for nav tree
      $('.no-touchevents nav#mainMenu .tierMenu').mouseenter(openChildHover).mouseleave(openChildHover);

    });

    function openMobileMenu(element, selector) {
      $(selector).slideDown(200, 'easeInSine');
      $(element).addClass('open');
    }

    function closeMobileMenu(element, selector) {
      $(selector).slideUp(200, 'easeOutSine');
      $(element).removeClass('open');
    }

    function openChildClick(event) {
      var $parent = $(event.currentTarget).parent(),
          $targetUl = $parent.find('>ul');

      $targetUl.stop(true, true);
      if ($parent.hasClass('over')) {
        $parent.removeClass('over');
        $targetUl.slideUp(200);
        return;
      }

      $parent.siblings('li.over').each(function(index, element) {
        $(element).find('> ul').slideUp(200);
        $(element).removeClass('over');
      });

      $parent.addClass('over');
      $targetUl.slideDown(200);
    }
  
    //Click to open nav tree
    $('nav.menuContainer .openChild').on('click', openChildClick);

    // Track current width
    var width = $(window).width();
    $(window).resize(function() {
      width = $(window).width();
    });


    function openChildHover(event) {
      if (width > 1184) {
        var $parenthover = $(event.currentTarget),
            $targetUlhover = $parenthover.find('>ul');

        $targetUlhover.stop(true, true);
        if ($parenthover.hasClass('over')) {
          $parenthover.removeClass('over');
          $targetUlhover.slideUp(200);
          return;
        }

        $parenthover.siblings('li.over').each(function(index, element) {
          $(element).find('> ul').slideUp(200);
          $(element).removeClass('over');
        });

        $parenthover.addClass('over');
        $targetUlhover.slideDown(200);
      }
    }
  
})(jQuery);


//Aria assignments
document.addEventListener("DOMContentLoaded", function () {
  "use strict";
  var menuItems = document.querySelectorAll('.openChild');
  
  var myFunc = function(){ 
    if (this.parentNode.classList.contains('over')) {
        this.setAttribute('aria-expanded', "true");
        this.querySelector('.accessibility-hidden').innerText = 'hide submenu';
    } else {
        this.querySelector('.accessibility-hidden').innerText = 'show submenu';
        this.setAttribute('aria-expanded', "false");
    }
  };

  for (var z = 0; z < menuItems.length; z++) {
    var el = menuItems[z];
    el.addEventListener('click', myFunc);
    el.addEventListener('mouseenter', myFunc);
    el.addEventListener('mouseleave', myFunc);
  }
});