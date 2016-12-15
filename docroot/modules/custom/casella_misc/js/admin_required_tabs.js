jQuery(document).ready(function() {
  jQuery('.vertical-tabs').each(addRequiredToVerts);
});

function addRequiredToVerts(index, element) {
  var tabs = jQuery('.vertical-tabs__pane', element),
      navs = jQuery('.vertical-tabs__menu-item', element);
  console.log(tabs);
  console.log(navs);

  tabs.each(function(tabIndex, tabElement){
    if (jQuery('.form-required', tabElement).length) {
      console.log(navs[tabIndex]);
      jQuery('.vertical-tabs__menu-item-title', navs[tabIndex]).addClass('form-required');
    }
  });
}
