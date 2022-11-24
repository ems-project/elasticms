export class BelgiumPhoneNumberValidator
{
  constructor(value) {
    this.value = value;
    this.numbers = this.value.match(/\d+/g);
  }

  validate() {
    if (this.numbers === null) {
      return false;
    }

    this.phone = this.transform();

    const typeNumber = this.getTypeNumber();

    if (this.validateNumberOfDigit(typeNumber) && this.validateCountryCode(typeNumber) && this.validateLongDistanceCode(typeNumber)) {
      return true;
    }

    return false;
  }

  validateNumberOfDigit(typeNumber) {
    if (typeNumber === 'zeros') {
      return (this.phone.length === 13) || (this.phone.length === 12);
    }

    if (typeNumber === 'plus') {
      return (this.phone.length === 12) || (this.phone.length === 11);
    }

    if (typeNumber === 'local') {
      return (this.phone.length === 10) || (this.phone.length === 9);
    }

    return false;
  }

  validateCountryCode(typeNumber) {
    if (typeNumber === 'zeros') {
      return this.phone.startsWith('32', 2);
    }

    if (typeNumber === 'plus') {
      return this.phone.startsWith('32', 1);
    }

    return typeNumber === 'local';
  }

  validateLongDistanceCode(typeNumber) {
    if (typeNumber === 'zeros') {
      return !this.phone.startsWith('0', 4);
    }

    if (typeNumber === 'plus') {
      return !this.phone.startsWith('0', 3);
    }

    if (typeNumber === 'local') {
      return this.phone.startsWith('0');
    }

    return false;
  }

  getTypeNumber() {
    if (this.phone.startsWith('+')) {
      return 'plus';
    }

    if (this.phone.startsWith('00')) {
      return 'zeros';
    }

    return 'local';
  }

  transform() {
    let phone = this.numbers.map(String).join('');

    if (this.value.startsWith('+')) {
      phone = ('+').concat(phone);
    }

    return phone;
  }
}