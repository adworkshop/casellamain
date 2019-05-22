import tingle from 'tingle.js';

const modal = new tingle.modal({
  closeMethods: ['overlay', 'button', 'escape'],
  closeLabel: 'Close'
});

let modalTriggers = document.querySelectorAll('[data-modal-open]');

if (modalTriggers) {
  Array.from(modalTriggers).forEach(button => {
    button.addEventListener('click', event => {
      event.preventDefault();

      let elemForModal = button.previousElementSibling;
      if (elemForModal) {
        modal.setContent(elemForModal.innerHTML);
      }
      modal.open();
      if ( !button.classList.contains('processed-listener') ) {
        button.classList.add('processed-listener');
      }
    });
  });
}
