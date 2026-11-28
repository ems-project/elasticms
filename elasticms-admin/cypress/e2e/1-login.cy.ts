describe('Login form', () => {
  beforeEach(() => {
    cy.visit('/')
  })

  it('Test 1 : check the login', () => {
    cy.dataTest('logo').should('be.visible')
    cy.dataTest('username').should('be.visible')
    cy.dataTest('password').should('be.visible')
    cy.dataTest('login').should('be.visible')
  })

  it('Test 2 : Login as demo admin', () => {
    cy.dataTest('username').type(Cypress.env('adminLogin'))
    cy.dataTest('password').type(Cypress.env('adminPassword'))
    cy.dataTest('login').click()
    cy.dataTest('user-menu').should('be.visible').should('contain', Cypress.env('adminLogin'))
  })

  it('Test 3 : Logout', () => {
    cy.loginAsAdmin()
    cy.dataTest('user-menu').click()
    cy.dataTest('logout').click()
    cy.dataTest('login').should('be.visible')
  })
})
