export function init() {
    const editorBody = document.querySelector('.editor-body') as HTMLElement | null;
    const editorSidebar = document.querySelector('.editor-sidebar') as HTMLElement | null;
    const handle = editorSidebar?.querySelector('.editor-sidebar-resize-handle') as HTMLElement | null;

    let isResizing = false;
    let lastWidth = 0;
    let animationFrame: number | null = null;
    let overlay: HTMLDivElement | null = null;

    if (handle && editorBody && editorSidebar) {
        const min = 200;

        handle.addEventListener('pointerdown', (e) => {
            isResizing = true;
            document.body.style.cursor = 'col-resize';

            overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.cursor = 'col-resize';
            overlay.style.zIndex = '9999';
            overlay.style.background = 'transparent';
            document.body.appendChild(overlay);

            e.preventDefault();
        });

        document.addEventListener('pointerup', () => {
            isResizing = false;
            document.body.style.cursor = 'default';

            if (overlay) {
                document.body.removeChild(overlay);
                overlay = null;
            }
        });

        document.addEventListener('pointermove', (e) => {
            if (!isResizing) return;

            const editorRect = editorBody.getBoundingClientRect();
            const max = editorRect.width - 100;

            const width = editorRect.right - e.clientX;
            lastWidth = Math.max(min, Math.min(max, width));

            if (!animationFrame) {
                animationFrame = requestAnimationFrame(() => {
                    editorBody.style.gridTemplateColumns = `1fr ${lastWidth}px`;
                    localStorage.setItem('sidebar-width', lastWidth.toString());
                    animationFrame = null;
                });
            }
        });

        const savedWidth = localStorage.getItem('sidebar-width');
        if (savedWidth) {
            editorBody.style.gridTemplateColumns = `1fr ${savedWidth}px`;
        }
    }
}