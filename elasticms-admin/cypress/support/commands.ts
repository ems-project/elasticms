// ─── Interfaces ──────────────────────────────────────────────────────

interface Product {
  name: string
  price: string
  stock: string
  description?: string
  category?: string
}

// ─── Commands ────────────────────────────────────────────────────────

Cypress.Commands.add('login', (login: string, password: string): void => {
  cy.visit('/')
  cy.dataTest('username').type(login)
  cy.dataTest('password').type(password)
  cy.dataTest('login').click()

  cy.dataTest('user-menu').should('be.visible')
})

Cypress.Commands.add('loginAsAdmin', (): void => {
  cy.login(Cypress.env('adminLogin'), Cypress.env('adminPassword'))
})

Cypress.Commands.add('dataTest', (value: string): Cypress.Chainable<JQuery<HTMLElement>> => {
  return cy.get(`[data-testid="${value}"]`)
})
