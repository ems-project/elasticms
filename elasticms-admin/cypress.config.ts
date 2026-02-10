import { defineConfig } from 'cypress'

export default defineConfig({
  e2e: {
    baseUrl: 'http://localhost:8881',
    viewportWidth: 1280,
    viewportHeight: 720,
    defaultCommandTimeout: 10000,
    video: false,
    screenshotOnRunFailure: true,
    specPattern: 'cypress/e2e/**/*.cy.ts',
    supportFile: 'cypress/support/e2e.ts',
  },
  env: {
    adminLogin: 'demo',
    adminEmail: 'admin@example.com',
    adminPassword: 'demo',
    userEmail: 'user@example.com',
    userLogin: 'user',
    userPassword: 'password',
  },
})