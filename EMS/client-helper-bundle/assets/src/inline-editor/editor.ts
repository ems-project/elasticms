import '@fortawesome/fontawesome-free/css/all.min.css';
import "../../css/inline-editor/editor.css";
import { initSidebarResize } from './editor/sidebar-resize';
import { EditorBridge } from './editor/editor-bridge';

initSidebarResize();

const iframe = document.getElementById('preview-iframe') as HTMLIFrameElement;
const editBtn = document.getElementById('edit-btn') as HTMLButtonElement;

const editorBridge = new EditorBridge(iframe);

editBtn.addEventListener('click', () => {
    editorBridge.toggleInlineEdit();
});