import { IMockFile } from './mockFile.types'
import { Blob } from 'blob-polyfill'

const createFileFromMockFile = (file: IMockFile): File => {
    const blob = new Blob([file.body], { type: file.mimeType }) as any
    blob['lastModifiedDate'] = new Date()
    blob['name'] = file.name
    Object.defineProperty(blob, 'size', { value: file.size, writable: false, configurable: true })
    return blob as File
}

export const createMockFileList = (files: IMockFile[]) => {
    const fileList = {
        length: files.length,
        item(index: number): File {
            return fileList[index] as File
        },
        [Symbol.iterator](): Iterator<File> {
            let index = 0
            return {
                next(): IteratorResult<File> {
                    return index < fileList.length
                        ? { value: fileList[index++] as File, done: false }
                        : { value: undefined as any, done: true }
                }
            }
        }
    } as FileList
    files.forEach((file, index) => (fileList[index] = createFileFromMockFile(file)))

    return fileList
}
