jQuery(document).ready(function () {
  initLocationsMap();
});

function initLocationsMap() {
  // set content on click
  jQuery('.panelControlBtn').click(panelControlBtnClickHandler);

  // set content on load
  setContent(jQuery('.panelControlBtn.active'));

  jQuery('.locationMap-marker-accordion-toggle').on('click', locationgMapMarkerClickHandler);
}

function panelControlBtnClickHandler(event) {
  var $element = jQuery(event.currentTarget);

  if ($element.hasClass('active')) {
    return false;
  }

  event.preventDefault();
  setContent($element);
}

function setContent($el) {
  if (!$el.length) {
    return;
  }

  var $locationsMapPanel = jQuery('.locationsMap-panel');

  jQuery('.panelControlBtn').removeClass('active');
  $locationsMapPanel.fadeOut(300);

  $locationsMapPanel.promise().done(function () {
    $el.addClass('active');
    jQuery($el.data('rel')).fadeIn(300).promise().done(function() {
        jQuery.fn.matchHeight._update();
    });
  });

}

function locationgMapMarkerClickHandler(event) {
  event.preventDefault();
  var accordion = jQuery(event.currentTarget),
    accordionContent = accordion.next('.locationMap-marker-accordion-content'),
    accordionToggleIcon = accordion.children('.locationMap-marker-toggle-icon');

  accordion.toggleClass("open");
  accordionContent.slideToggle(250);

  if (accordion.hasClass("open")) {
    accordionToggleIcon.html("<i class='fa fa-minus-circle'></i>");
  }
  else {
    accordionToggleIcon.html("<i class='fa fa-plus-circle'></i>");
  }
}
