export function preventCopyPaste(element) {
    element.addEventListener('paste', function(e) {
        e.preventDefault();
    });
}
