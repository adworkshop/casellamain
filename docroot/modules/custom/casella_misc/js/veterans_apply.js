function getCookie(name) {
  var value = "; " + document.cookie;
  var parts = value.split("; " + name + "=");
  if (parts.length == 2) return parts.pop().split(";").shift();
  else return '';
}

document.addEventListener("DOMContentLoaded", function(event) {
  cookie_check = getCookie('Drupal.visitor.apply_recipient_email');
  if ('' != cookie_check) {
    document.getElementById('contact-message-job-application-form').field_recipient_email_wrapper.value=cookie_check;
  }
});