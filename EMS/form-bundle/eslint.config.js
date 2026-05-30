import tsParser from '@typescript-eslint/parser'

export default [
    {
        files: ['**/*.{js,ts}'],
        ignores: ['public/**', 'node_modules/**'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            parser: tsParser
        },
        rules: {}
    }
]