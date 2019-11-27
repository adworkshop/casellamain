jQuery(document).ready(function() {

  jQuery('form#views-exposed-form-location-towns-page-2 .form-autocomplete.form-text').autocomplete({
    select: (event, ui) => {
      jQuery('form#views-exposed-form-location-towns-page-2 .form-autocomplete.form-text').val(ui.item.value);
      jQuery('form#views-exposed-form-location-towns-page-2 .button.form-submit').click();
    }
  });
});
