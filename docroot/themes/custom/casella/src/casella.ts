import 'core-js/features/array/some';
import 'core-js/features/array/from';
import autoComplete from './auto-complete';
import './components/calculator';
import { range } from './util/utils';
import './pdfModal';

const defaultOptions = {
  minChars: 1,
  delay: 150,
  offsetLeft: 0,
  offsetTop: 1,
  cache: true,
  menuClass: ''
};

const stateOptions = {
  hiddenClass: 'is-hidden'
};

class Autocompleter {
  // private selector: string = 'input[type="search"]';
  private readonly valid: boolean;
  private searchItemsForm: HTMLFormElement;
  public choices: string[];
  public matches: string[] = [];
  public options: any;

  constructor(public selector: string) {
    this.selector = selector;
    this.valid = this.checkValidity();

    this.searchItemsForm = <HTMLFormElement>document.getElementById('sidebar-items-search-form-recycle');
    this.addListener();
    this.addFormListener();

    this.renderNoMatchesAlert();
    this.renderMatchesAlert();

    this.choices = [
      'Cardboard', 'Boxboard', 'Dry-food boxes', 'Egg Cartons', 'Paper Rolls', 'Envelopes', 'Paper Bags', 'Office Paper',
      'Catalogs', 'Junk Mail', 'Periodicals', 'Plastic Bottles', 'Plastic Jugs', 'Plastic Tubs', 'Plastic Lids',
      'Aluminum Foil', 'Aluminum Cans', 'Glass Jars', 'Glass Bottles'
    ];
    // this.matches = [];
    this.options = Object.assign({}, defaultOptions);
  }

  checkValidity(): boolean {
    return ( document.querySelector(this.selector) instanceof HTMLElement );
  }

  addFormListener(): void {
    const {searchItemsForm} = this;
    if (searchItemsForm instanceof HTMLFormElement) {
      searchItemsForm.addEventListener('submit', (event: Event) => {
        event.preventDefault();
      })
    }
  }

  addListener(): boolean | void {
    if ( ! this.valid ) {
      return false;
    }

    let searchElem = <HTMLInputElement>document.querySelector(this.selector);

    searchElem.addEventListener('blur', (event: any) => {
      event.target.value = '';
    });

    searchElem.addEventListener('input', (event: any) => {
      if (event.target.value.length === 0) {
        Autocompleter.hideElementById('ac-no-matches');
      } else {
        Autocompleter.hideElementById('ac-has-matches');
      }
    });

    searchElem.addEventListener('keydown', event => {

      // let whichKey = event.keyCode;

      // let allowedKeysRange = range(65, 90);
      /* if ( !allowedKeysRange.includes(whichKey) ) {
        return false;
      } */

      setTimeout(() => {
        let matchesContainer = <HTMLElement>document.querySelector('.autocomplete-suggestions');
        let hasMatchesContainer = <HTMLElement>document.querySelector('.autocomplete-has-suggestions');
        let noMatchesContainer = <HTMLElement>document.querySelector('.autocomplete-no-suggestions');

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

  }

  setOption(optionName: string, optionValue: string): void {
    if (defaultOptions.hasOwnProperty(optionName)) {
      this.options[optionName] = optionValue;
    }
  }

  renderNoMatchesAlert(): void {
    let
      selectorElem = <HTMLInputElement>document.querySelector(this.selector),
      position = <InsertPosition>'afterend',
      markup;

    markup = `<div id="ac-no-matches" class="autocomplete-no-suggestions no-suggestions is-hidden"><span class="fa fa-ban" aria-hidden="true"></span><p>Sorry, this is not allowed, but <a href="/contact-us">click here</a> for more information</p></div>`;

    selectorElem.insertAdjacentHTML(position, markup);
  }

  renderMatchesAlert(): void {
    let
      selectorElem = <HTMLDivElement>document.querySelector('.form-actions'),
      position = <InsertPosition>'afterbegin',
      markup: any;

    markup = `<div id="ac-has-matches" class="autocomplete-has-suggestions is-hidden"><span class="fa fa-check-circle" aria-hidden="true"></span><p>These are good to go!</p></div>`;

    setTimeout(() => {
      selectorElem.insertAdjacentHTML(position, markup);
    }, 500);

  }

  static hideElementById(elemId: string) {
    if (document.getElementById(elemId)) {
      let elem = <HTMLElement>document.getElementById(elemId);
      if ( !elem.classList.contains(stateOptions.hiddenClass) ) {
        elem.classList.add(stateOptions.hiddenClass);
      }
    }
  }

  static showElementById(elemId: string, term: null | string = null) {
    if (document.getElementById(elemId)) {
      let elem = <HTMLElement>document.getElementById(elemId);
      if (term) {
        elem.innerHTML = `<span class="fa fa-check-circle" aria-hidden="true"></span><p><strong>${term}</strong> can be recycled!</p>`;
      }
      elem.classList.remove(stateOptions.hiddenClass);
    }
  }

  makeAutoComplete() {
    const _ = this;

    return new autoComplete({
      selector: this.selector,
      minChars: 1,
      source(term: string, suggest: Function): void {
        term = term.toLowerCase();

        if (_.choices.some(choice => choice.toLowerCase().indexOf(term) !== -1)) {
          Autocompleter.hideElementById('ac-no-matches');
          _.matches = _.choices.filter(choice => choice.toLowerCase().indexOf(term) !== -1);
        } else {
          Autocompleter.showElementById('ac-no-matches');
        }
        suggest(_.matches);
        _.matches = [];
      },
      renderItem(item: string, search: string): string {
        search = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        let re = new RegExp("(" + search.split(' ').join('|') + ")", "gi");
        return `<div class="autocomplete-suggestion" data-val="${item}">${item.replace(re, '<b>$1</b>')}</div>`;
      },
      onSelect(event: Event, term: string, item: HTMLElement ) {
        Autocompleter.showElementById('ac-has-matches', term);
      }
    });
  }
}

new Autocompleter('input[type="search"]').makeAutoComplete();
