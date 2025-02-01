export class MaxFileSizeValidator
{
  constructor(files, maxAllowedSize) {
    this.files = files
    this.maxAllowedSize = maxAllowedSize
  }

  validate() {
    if (this.files && this.files.length > 0) {
      let currentFileSize = 0

      Array.from(this.files).forEach(function (file) {
        currentFileSize += file.size
      });

      if (currentFileSize > this.maxAllowedSize) {
        return false
      }
    }
    return true
  }

  hasMultipleFiles() {
    return this.files.length > 1
  }
}