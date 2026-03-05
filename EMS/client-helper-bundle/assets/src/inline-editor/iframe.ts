import "../../css/inline-editor/iframe.css";

import { initIframe } from './iframe/index';
import {init as initGo2editor} from "./iframe/go2editor";

if (window.self === window.top) {
    initGo2editor();
} else {
    initIframe();
}
