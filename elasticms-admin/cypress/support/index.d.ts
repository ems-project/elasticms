interface Product {
  name: string
  price: string
  stock: string
  description?: string
  category?: string
}

declare namespace Cypress {
  interface Chainable {
    login(email: string, password: string): Chainable<void>
    loginAsAdmin(): Chainable<void>
    dataTest(value: string): Chainable<JQuery<HTMLElement>>
  }
}
