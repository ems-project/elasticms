import { init as initSidebarResize } from './sidebar-resize';
import {Messenger} from "./messenger";

export function initEditor() {
    initSidebarResize();

    const iframe = document.getElementById('preview-iframe') as HTMLIFrameElement;
    const messenger = new Messenger(iframe);

    messenger.on((data) => {
        if (data.type === 'IFRAME_READY') {
            messenger.send({ type: 'EDITOR_INIT' });
        }
    });

    const editButton = document.getElementById('btn-edit') as HTMLIFrameElement;
    editButton.addEventListener('click', function () {
        messenger.send({ type: 'EDITOR_TOGGLE_EDIT' })
    });
}