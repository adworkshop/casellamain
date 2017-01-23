/**
Handle google analytics events as outlined by DAC group
 Depends on GTM and dataLayer
**/
function cleanUpLabel(s) {
  return s.replace(/['"]+/g, '');
}

function eventTracking(category,action,label) {
  if (typeof dataLayer != 'undefined') {

    console.log('pushing event ' + category + ' ' + action + ' ' + label);

    dataLayer.push({
      'event':'trackevent',
      'category':category,
      'action':action,
      'label':cleanUpLabel(label)
    });
  }
}
jQuery(document).ready(function() {

  // header search form
  jQuery('form#search').submit(function() {
    eventTracking('search','header submit click',jQuery('form#search .searchFormPanelSearchField input').val());
  });

  // careers table
  jQuery('table#jobListings select.stateInput').change(function() {
    eventTracking('careers search form','view all states click',jQuery('table#jobListings select.stateInput').val());
  });
  jQuery('table#jobListings span.fooicon-remove').click(function() {
    eventTracking('careers search form','clear search click','x');
  });
  jQuery('table#jobListings span.fooicon-search').click(function() {
    eventTracking('careers search form','search submit',jQuery('table#jobListings .input-group input.form-control').val());
  });

  // find my local provider form (lives in 2 places)
  jQuery('form#location-search, form#location-mainSearch').submit(function() {

    eventTracking('find my local provider form','form completion','');
  });











});
