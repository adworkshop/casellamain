jQuery(document).ready(function ($) {
  jQuery("#tabbed-nav").zozoTabs({
    position: "top-right",
    theme: "flat-nephritis",
    style: "clean",
    shadows: false,
    multiline: true,
    bordered: true,
    minWindowWidth: 945,
    orientation: "vertical",
    size: "small",
    animation: {
      easing: "easeInOutExpo",
      duration: 500,
      effects: "slideH"
    },
  });
});