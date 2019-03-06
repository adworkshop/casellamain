jQuery(document).ready(function ($) {
  jQuery("#tabbed-nav").zozoTabs({
    position: "top-left",
    style: "clean",
    theme: "flat-nephritis",
    spaced: true,
    rounded: false,
    minWindowWidth: 960,
    animation: {
      easing: "easeInOutExpo",
      duration: 600,
      effects: "slideH"
    },
    size:"large"
  });
});
