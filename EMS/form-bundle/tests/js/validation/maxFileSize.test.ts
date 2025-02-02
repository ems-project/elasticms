import {IMockFile, createMockFileList} from './../mocks/mockFile'
import {MaxFileSizeValidator} from './../../../assets/js/validation/maxFileSizeValidator'

describe('Single max-file-size validation', () => {
  const singleFileList: IMockFile[] = createMockFileList([
    { body: 'test', mimeType: 'text/plain', name: 'test.txt', size: 1024 * 1024 },
  ])

  test('success validator', () => {
    let validator = new MaxFileSizeValidator(singleFileList, (1024 * 1024) + 1)
    expect(validator.validate()).toBe(true)
  })

  test('fail validator', () => {
    let validator = new MaxFileSizeValidator(singleFileList, (1024 * 1024) - 1)
    expect(validator.validate()).toBe(false)
  })
})

describe('Multiple max-file-size validation', () => {
  const MultipleFileList: IMockFile[] = createMockFileList([
    { body: 'test', mimeType: 'text/plain', name: 'test.txt', size: 1024 * 1024 },
    { body: 'test', mimeType: 'text/plain', name: 'test.txt', size: 1024 * 1024 },
    { body: 'test', mimeType: 'text/plain', name: 'test.txt', size: 1024 * 1024 },
    { body: 'test', mimeType: 'text/plain', name: 'test.txt', size: 1024 * 1024 },
    { body: 'test', mimeType: 'text/plain', name: 'test.txt', size: 1024 * 1024 },
    { body: 'test', mimeType: 'text/plain', name: 'test.txt', size: 1024 * 1024 },
    { body: 'test', mimeType: 'text/plain', name: 'test.txt', size: 1024 * 1024 },
    { body: 'test', mimeType: 'text/plain', name: 'test.txt', size: 1024 * 1024 },
  ])

  test('success validator', () => {
    let validator = new MaxFileSizeValidator(MultipleFileList, (8 * 1024 * 1024) + 1)
    expect(validator.validate()).toBe(true)
  })

  test('fail validator', () => {
    let validator = new MaxFileSizeValidator(MultipleFileList, (8 * 1024 * 1024) - 1)
    expect(validator.validate()).toBe(false)
  })
})