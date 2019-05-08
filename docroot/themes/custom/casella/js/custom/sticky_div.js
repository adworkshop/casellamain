/*global jQuery, Modernizr */
(function($) {

  $(function() {
    var $sidebarItemsSearch = $('.sidebar-items-search');

    if ( ! $sidebarItemsSearch ) {
      return false;
    }

    $(window).bind('resize', function () {
      $sidebarItemsSearch.stick_in_parent({offset_top: 75, spacer: false, recalc_every: 1});
      if (Modernizr.mq('(min-width: 1200px)')) {
        $sidebarItemsSearch.stick_in_parent({offset_top: 75, spacer: false, recalc_every: 1});
      } else {
        $sidebarItemsSearch.trigger("sticky_kit:detach");
      }
    }).resize();
  });

})(jQuery);
