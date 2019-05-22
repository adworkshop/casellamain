/*jslint es6 */
class Calculator {

  private calculatorElement: HTMLFormElement;
  private calculatorResultsElement: HTMLDivElement;
  private calcSizeOfBin: HTMLInputElement;
  private calcSetOutFreq: HTMLInputElement;
  private formIsValid: boolean = true;

  constructor() {
    this.calculatorElement = <HTMLFormElement>document.getElementById('js-calculator-form');
    this.calculatorResultsElement = <HTMLDivElement>document.getElementById('js-calculator-form-results');
    this.calcSizeOfBin = <HTMLInputElement>document.getElementById('calc_size_of_bin');
    this.calcSetOutFreq = <HTMLInputElement>document.getElementById('calc_frequency_setout');

    this.addFormListener();
  }

  addFormListener(): void {
    try {
      this.calculatorElement.addEventListener('submit', (event: Event) => {
        event.preventDefault();
        if (this.validateForm()) {
          this.showResults();
        }
      });
    } catch (e) {
      console.warn(e, 'exception raised');
    }
  }

  showResults(): void {
    this.calculatorResultsElement.classList.remove('is-hidden');
    this.formIsValid = true;
  }

  validateForm(): boolean {
    const {calcSizeOfBin, calcSetOutFreq} = this;
    this.formIsValid = true;

    if (calcSizeOfBin.classList.contains('is-invalid')) {
      calcSizeOfBin.classList.remove('is-invalid');
    }
    if (calcSetOutFreq.classList.contains('is-invalid')) {
      calcSetOutFreq.classList.remove('is-invalid');
    }

    if (calcSizeOfBin.value === '') {
      calcSizeOfBin.classList.add('is-invalid');
      this.formIsValid = false;
    }

    if (calcSetOutFreq.value === '') {
      calcSetOutFreq.classList.add('is-invalid');
      this.formIsValid = false;
    }

    return this.formIsValid;
  }
}

export default new Calculator();
