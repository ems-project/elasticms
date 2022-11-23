import { IMockFile } from './mockFile.types'
import { Blob } from 'blob-polyfill'

const createFileFromMockFile = (file: IMockFile): File => {
  const blob = new Blob([file.body], { type: file.mimeType }) as any
  blob['lastModifiedDate'] = new Date()
  blob['name'] = file.name
  blob['size'] = file.size
  return blob as File
}

export const createMockFileList = (files: IMockFile[]) => {
  const fileList: FileList = {
    length: files.length,
    item(index: number): File {
      return fileList[index]
    }
  }
  files.forEach((file, index) => fileList[index] = createFileFromMockFile(file))

  return fileList
}