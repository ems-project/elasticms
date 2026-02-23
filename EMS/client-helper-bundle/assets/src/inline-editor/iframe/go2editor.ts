export function init(): void {
    if (window.self !== window.top) {
        return;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const go2editor = document.getElementById('go2editor') as HTMLElement | null;
        if (!go2editor) return;

        go2editor.style.display = 'flex';
    });
}