function getCookie(name) {
  var value = "; " + document.cookie;
  var parts = value.split("; " + name + "=");
  if (parts.length == 2) return parts.pop().split(";").shift();
  else return '';
}

document.addEventListener("DOMContentLoaded", function(event) {
  document.getElementById('edit-field-recipient-email-wrapper').style.display='none';
  document.getElementById('edit-field-recipient-email-0-value').readOnly = true;
  cookie_check = getCookie('Drupal.visitor.apply_recipient_email');

  if ('' != cookie_check) {
    document.getElementById('edit-field-recipient-email-0-value').value=decodeURIComponent(cookie_check);

  }
});