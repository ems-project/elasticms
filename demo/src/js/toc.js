/**
 * Generate a table of content
 */
export default function toc(contentElementId="main-content" , tocElementId="toc", listTag="ul", topLevelClass="section-nav", tocEntryClass="toc-entry toc-h__level__") {

    if (document.getElementById(contentElementId) && document.getElementById(tocElementId)) {
        let toc = "";
        let level = 0;

        document.getElementById(contentElementId).innerHTML =
            document.getElementById(contentElementId).innerHTML.replace(
                /<h([\d])>([^<]+)<\/h([\d])>/gi,
                function (str, openLevel, titleText, closeLevel) {
                    if (openLevel !== closeLevel) {
                        return str + ' - ' + openLevel;
                    }

                    if (openLevel > level) {
                        if (level === 0) {
                            toc += "<" + listTag + " class=\"" + topLevelClass + "\">";
                        }
                        else {
                            toc += (new Array(openLevel - level + 1)).join("<" + listTag + ">");
                        }
                    } else if (openLevel < level) {
                        if (level === 0) {
                            toc += "</li></" + listTag + ">";
                        }
                        else {
                            toc += (new Array(level - openLevel + 1)).join("</li></" + listTag + ">");
                        }
                    }
                    else {
                        toc += "</li>"
                    }

                    level = parseInt(openLevel);

                    const anchor = titleText.replace(/ /g, "_");
                    toc += "<li class=\"" + tocEntryClass.replace('__level__', level) + "\"><a href=\"#" + anchor + "\">" + titleText + "</a>";

                    return "<h" + openLevel + "><a name=\"" + anchor + "\">" + titleText + "</a></h" + closeLevel + ">";
                }
            );

        if (level) {
            toc += (new Array(level + 1)).join("</" + listTag + ">");
        }
        document.getElementById(tocElementId).innerHTML += toc;
    }
}