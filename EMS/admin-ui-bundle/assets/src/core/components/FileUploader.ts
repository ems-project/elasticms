// @ts-expect-error - @elasticms/file-uploader ships without type declarations
import FileUploaderImpl from '@elasticms/file-uploader'

interface FileUploaderOptions {
    file: File
    algo?: string
    initUrl: string
    onUploaded?: (assetUrl: string, previewUrl: string) => void
    onError?: (message: string, code?: number) => void
}

export const FileUploader = FileUploaderImpl as unknown as new (
    options: FileUploaderOptions
) => void
