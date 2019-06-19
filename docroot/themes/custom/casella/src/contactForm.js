
const contactFormElem = document.getElementById('contact-message-job-application-drivers-form');

if (contactFormElem) {
  contactFormElem.addEventListener('submit', event => {
    console.log(event, 'event');
    let licenseField = contactFormElem.elements['field_licenses[0][subform][field_license_][0][value]'];
    if (licenseField.value === ''  ) {
      event.preventDefault();
    }
  });
}
