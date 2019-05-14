import 'core-js/features/array/some';
import 'core-js/features/array/from';
import * as autoComplete from './auto-complete';
import './pdfModal';

const defaultOptions = {
  minChars: 1,
  delay: 150,
  offsetLeft: 0,
  offsetTop: 1,
  cache: true,
  menuClass: ''
};

class Autocompleter {
  constructor(selector = 'input[type="search"]') {
    this.selector = selector;
    this.valid = this.checkValidity();
    this.addListener();

    this.choices = [
      'Cardboard', 'Boxboard', 'Dry-food boxes', 'Egg Cartons', 'Paper Rolls', 'Envelopes', 'Paper Bags', 'Office Paper',
      'Catalogs', 'Junk Mail', 'Periodicals', 'Plastic Bottles', 'Plastic Jugs', 'Plastic Tubs', 'Plastic Lids',
      'Aluminum Foil', 'Aluminum Cans', 'Glass Jars', 'Glass Bottles'
    ];
    this.matches = [];
    this.options = Object.assign({}, defaultOptions);
  }

  checkValidity() {
    return ( document.querySelector(this.selector) instanceof HTMLElement );
  }

  addListener() {
    if ( ! this.valid ) {
      return false;
    }

    let searchElem = document.querySelector(this.selector);

    searchElem.addEventListener('keydown', event => {
      let whichKey = event.keyCode;

      setTimeout(() => {
        let matchesContainer = document.querySelector('.autocomplete-suggestions');
        let hasMatchesContainer = document.querySelector('.autocomplete-has-suggestions');
        let noMatchesContainer = document.querySelector('.autocomplete-no-suggestions');

        if (matchesContainer && matchesContainer.style.display === 'block') {
          if (noMatchesContainer) {
            noMatchesContainer.classList.add('is-hidden');
          }
        } else if (matchesContainer && matchesContainer.style.display === 'none' && searchElem.value !== '') {
          if (noMatchesContainer && noMatchesContainer.classList.contains('is-hidden')) {
            noMatchesContainer.classList.remove('is-hidden');

          }
        } else if (searchElem.value === '') {
          if (noMatchesContainer) {
            noMatchesContainer.classList.add('is-hidden');
          }
        }

      }, (this.options.delay + 100));

    });

    searchElem.addEventListener('blur', () => Autocompleter.hideElementById('ac-no-matches' ));
  }

  setOption(optionName, optionValue) {
    if (defaultOptions.hasOwnProperty(optionName)) {
      this.options[optionName] = optionValue;
    }
  }

  renderNoMatchesAlert() {
    let
      selectorElem = document.querySelector(this.selector),
      position = 'afterend',
      markup;

    markup = `<div id="ac-no-matches" class="autocomplete-no-suggestions no-suggestions"><span class="fa fa-ban" aria-hidden="true"></span><p>Sorry, this is not allowed, but <a href="/contact-us">click here</a> for more information</p></div>`;

    selectorElem.insertAdjacentHTML(position, markup);
  }

  renderMatchesAlert() {
    let
      selectorElem = document.querySelector('.autocomplete-suggestions'),
      position = 'afterbegin',
      markup;

    markup = `<div id="ac-has-matches" class="autocomplete-has-suggestions"><span class="fa fa-check-circle" aria-hidden="true"></span><p>These are good to go!</p></div>`;

    setTimeout(() => {
      selectorElem.insertAdjacentHTML(position, markup);
    }, 500);

    console.log(selectorElem, 'sugg');
  }

  static hideElementById(elemId) {
    if (document.getElementById(elemId)) {
      document.getElementById(elemId).classList.add('is-hidden');
    }
  }

  makeAutoComplete() {
    const _ = this;

    new autoComplete({
      selector: this.selector,
      minChars: 1,
      source(term, suggest) {
        term = term.toLowerCase();

        if (_.choices.some(choice => choice.toLowerCase().indexOf(term) !== -1)) {
          Autocompleter.hideElementById('ac-no-matches');
          _.matches = _.choices.filter(choice => choice.toLowerCase().indexOf(term) !== -1);
        } else {
          _.renderNoMatchesAlert();
        }
        suggest(_.matches);
        _.matches = [];
      },
      renderItem(item, search) {
        search = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        let re = new RegExp("(" + search.split(' ').join('|') + ")", "gi");
        return `<div class="autocomplete-suggestion" data-val="{$item}">${item.replace(re, '<b>$1</b>')}</div>`;
      }
    });
  }
}

new Autocompleter().makeAutoComplete();
