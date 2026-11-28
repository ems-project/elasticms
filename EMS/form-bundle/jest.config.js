export default {
    extensionsToTreatAsEsm: ['.ts'],
    transform: {
        '^.+\\.(ts|js)$': ['ts-jest', { useESM: true }]
    },
    transformIgnorePatterns: [],
    moduleNameMapper: {
        '^(\\.{1,2}/.*)\\.js$': '$1'
    },
    testEnvironment: 'node'
}
