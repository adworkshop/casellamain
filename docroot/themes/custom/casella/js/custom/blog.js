document.addEventListener("DOMContentLoaded",function() {
  jQuery("ul.archive-links__inner").on("click", ".init", function() {
    jQuery(this).closest("ul.archive-links__inner").children('li:not(.init)').toggle();
  });

  var allOptions = jQuery("ul.archive-links__inner").children('li:not(.init)');
  jQuery("ul.archive-links__inner").on("click", "li:not(.init)", function() {
    allOptions.removeClass('selected');
    jQuery(this).addClass('selected');
    jQuery("ul.archive-links__inner").children('.init').html(jQuery(this).html());
    allOptions.toggle();
  });
});