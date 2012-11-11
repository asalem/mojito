var ReceiverId = "searchbox";
var ResultListId = "resultlist";
var SuggestListId = "suggestlist";

var requestHandler = "main.php";
var QueryName = "lchar";

function createXHR() {
    if (typeof XMLHttpRequest != "undefined") {
        return new XMLHttpRequest();
    } else if (typeof ActiveXObject != "undefined") {
        return new ActiveXObject("Microsoft.XMLHTTP");
    } else {
        throw new Error("no XHR object available.");
    }
}

function appendURLParam(url, name, value) {
    url += (url.indexOf("?") == -1 ? "?" : "&");
    url += encodeURIComponent(name) + "=" + encodeURIComponent(value);
    return url;
}

function processResponse(responseJSON) {
    var suggestlist = document.getElementById(SuggestListId);
    var resultList = document.getElementById(ResultListId);

    var results = JSON.parse(responseJSON);

    var index = 0;
    var resultCount = results.length;

    var suggestCount = (resultCount < 3) ? resultCount : 3;

    for (index = 0; index < suggestCount; index++) {
        var suggestItem = document.createElement("li");
        suggestItem.innerHTML = results[index].wordName;

        suggestlist.appendChild(suggestItem);
    }

    for (index = 0; index < resultCount; index++) {
        var titleItem = document.createElement("dt");
        var descriptionItem = document.createElement("dd");

        titleItem.innerHTML = results[index].wordName;
        descriptionItem.innerHTML = results[index].wordDesc;

        resultList.appendChild(titleItem);
        resultList.appendChild(descriptionItem);        
    }
}

function sendRequest(queryValue) {
    var xhr = createXHR();

    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4) {
            if ((xhr.status >= 200 && xhr.status < 300) || xhr.status == 304) {
                processResponse(xhr.responseText);
            } else {
                // TODO: handle AJAX failure
            }
        }
    }

    requestHandler = appendURLParam(requestHandler, QueryName, queryValue);

    xhr.open("get", requestHandler, true);
    xhr.send(null);
}

function flushOutput() {
    var suggestList = document.getElementById(SuggestListId);
    var resultList = document.getElementById(ResultListId);    
    
    suggestList.innerHTML = "";
    resultList.innerHTML = "";

    // handle situations when setting innerHTML to empty string fails
    while (suggestlist.hasChildNodes()) {
        suggestList.removeChild(suggestList.lastChild);
    }
    while (resultlist.hasChildNodes()) {
        resultList.removeChild(resultList.lastChild);
    }    
}

function processInput(oldValue, newValue) {

    if (oldValue.length == 0) {
        sendRequest(newValue);
    } else if (newValue.length == 0) {
        flushOutput();
    } else if (oldValue.length < newValue.length) {
        // TODO
        alert("filter out");
    } else {
        // TODO
        alert("loosen");
    }
}

var queryProcessor = function() {

    var currentValue = this.value;

    if (typeof queryProcessor.lastValue == "undefined") {
        sendRequest(currentValue);
    } else if (queryProcessor.lastValue == currentValue) {
        // content not changed, do nothing?
    } else {
        processInput(queryProcessor.lastValue, currentValue);
    }

    queryProcessor.lastValue = this.value;
};

var EventManager = {
    
    addHandler: function(element, type, handler) {
        if (element.addEventListener) {
            element.addEventListener(type, handler, false);
        } else if (element.attachEvent) {
            element.attachEvent("on" + type, handler);
        } else {
            element["on" + type] = handler;
        }
    },

    removeHandler: function(element, type, handler) {
        if (element.removeEventListener) {
            element.removeEventListener(type, handler, false);
        } else if (element.detachEvent) {
            element.detachEvent("on" + type, handler);
        } else {
            element["on", + type] = null;
        }
    }

};

function main() {
    var inputField = document.getElementById(ReceiverId);
    EventManager.addHandler(inputField, "keyup", queryProcessor);
}