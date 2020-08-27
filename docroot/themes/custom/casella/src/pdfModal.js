import tingle from 'tingle.js';

const modal = new tingle.modal({
  closeMethods: ['overlay', 'button', 'escape'],
  closeLabel: 'Close'
});

$('[data-modal-open]').each(function(index, button) {
  initializeModal(button);
});

function initializeModal(button) {
  $(button).click(event => {
    event.preventDefault();

    let closestModal = $(button).siblings('[data-modal-title]');
    let elemForModal = closestModal.length > 0 ? closestModal.get(0) : button.previousElementSibling;

    if (elemForModal) {
      modal.setContent(elemForModal.innerHTML);

      $(modal.modal).find('[data-modal-open]').each(function (index, button) {
        initializeModal(button);
      });
    }

    modal.open();

    if (!button.classList.contains('processed-listener')) {
      button.classList.add('processed-listener');
    }
  });
}
