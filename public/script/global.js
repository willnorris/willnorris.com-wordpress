function externalLinks() {
    host = (document.location + "").replace(/^(https?:\/\/)([^\/]+).*/i, "$2");

    if (!document.getElementsByTagName) return;
    var anchors = document.getElementsByTagName("a");
    for (var i=0; i<anchors.length; i++) {
        var anchor = anchors[i];
        if (anchor.getAttribute("href")) {
            if (anchor.getAttribute("rel") != "local") {
                if (anchor.getAttribute("rel") == "external") {
                    anchor.target = "_blank";
                } else {
                    hrefData = anchor.getAttribute("href").match(/^https?:\/\/([^\/]+).*/i);
                    if (hrefData && hrefData[1] != host)
                        anchor.target = "_blank";
                }
            }
        }
    }
}

