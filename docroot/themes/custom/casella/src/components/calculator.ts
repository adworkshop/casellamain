/*jslint es6 */
class Calculator {

  private calculatorElement: HTMLFormElement;
  private calculatorResultsElement: HTMLDivElement;
  private calculatorResultsBodyElement: HTMLDivElement;
  private calcSizeOfBin: HTMLInputElement;
  private calcSetOutFreq: HTMLInputElement;
  private formIsValid: boolean = true;

  constructor() {
    this.calculatorElement = <HTMLFormElement>document.getElementById('js-calculator-form');
    this.calculatorResultsElement = <HTMLDivElement>document.getElementById('js-calculator-form-results');
    this.calculatorResultsBodyElement = <HTMLDivElement>document.getElementById('js-result-body-container');
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

  static calculateValues(sizeOfBin: number, setOutFreq: number): string {
    return ( ( +sizeOfBin * setOutFreq ) / 100 ).toFixed(4);
  }

  showResults(): void {
    const {calcSizeOfBin, calcSetOutFreq} = this;
    this.calculatorResultsElement.classList.remove('is-hidden');

    let calcString = `<p><strong>Size of Bin (in gallons)</strong>: ${calcSizeOfBin.value}<br />`;
    calcString += `<strong>Frequency of Set-out (per week)</strong>: ${calcSetOutFreq.value}</p>`;
    calcString += `<p>${calcSizeOfBin.value} * ${calcSetOutFreq.value} / 100 = ${Calculator.calculateValues(+calcSizeOfBin.value, +calcSetOutFreq.value)} megaWattJoules`;
    this.calculatorResultsBodyElement.innerHTML = calcString;
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
