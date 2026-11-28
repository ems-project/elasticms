// @ts-expect-error - @elasticms/file-uploader ships without type declarations
import FileUploaderImpl from '@elasticms/file-uploader'

interface FileUploaderOptions {
    file: File
    algo?: string
    initUrl: string
    onHashAvailable?: (hash: string, type?: string, name?: string) => void
    onProgress?: (status: string, progress: number, remaining: string) => void
    onUploaded?: (assetUrl: string, previewUrl: string) => void
    onError?: (message: string, code?: number) => void
}

export const FileUploader = FileUploaderImpl as unknown as new (
    options: FileUploaderOptions
) => void
