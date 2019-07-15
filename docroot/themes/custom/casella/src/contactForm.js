import 'core-js/features/array/from';
import tingle from 'tingle.js';

const modal = new tingle.modal({
  closeMethods: ['overlay', 'button', 'escape'],
  closeLabel: 'Close'
});

const contactFormElem = document.getElementById('contact-message-job-application-drivers-form');

if (modal) {
  modal.setContent('<p>Please fill out license field.</p>');
}

if (contactFormElem) {

  const getRequiredFields = () => {
    return Array.from(contactFormElem.elements).filter(elem => elem.hasAttribute('required') || elem.classList.contains('required'));
  };

  const toggleFieldClass = (field, validity) => {
    if (field.classList.contains('is-invalid')) {
      if (validity) {
        field.classList.remove('is-invalid');
      }
    } else {
      if (! field.classList.contains('is-invalid')) {
        field.classList.add('is-invalid');
      }
    }
  };

  contactFormElem.addEventListener('submit', event => {
    /*console.log(event, 'event');
    let valid = true;
    let reqFields = getRequiredFields();

    reqFields.forEach(field => {
      if (field.nodeName === 'INPUT' || field.nodeName === 'SELECT') {
        if (field.value === '') {
          valid = false;
          toggleFieldClass(field, valid);
        } else {
          toggleFieldClass(field, true);
        }
      }
    });

    if (!valid) {
      event.preventDefault();
      if (modal) {
        modal.open();
      }
    }*/

    let licenseField = contactFormElem.elements['field_licenses[0][subform][field_license_][0][value]'];
    if (licenseField.value === '') {
      event.preventDefault();
      if (modal) {
        modal.open();
      }
    } else {
      if (licenseField && licenseField.value) {
        if (licenseField.value.length < 3) {
          event.preventDefault();
          if (modal) {
            modal.open();
          }
        }
      }
    }
  });
}
