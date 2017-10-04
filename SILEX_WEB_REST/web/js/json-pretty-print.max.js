// http://stackoverflow.com/questions/4810841/how-can-i-pretty-print-json-using-javascript

function jsonPrettyPrint(jason) {
    function syntaxHighlight(json) {
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
            var cls = 'number';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'key';
                } else {
                    cls = 'string';
                }
            } else if (/true|false/.test(match)) {
                cls = 'boolean';
            } else if (/null/.test(match)) {
                cls = 'null';
                match = 'NULLL';
            }
            return '<span class="' + cls + '">' + match + '</span>';
        });
    }

    if ('string' !== typeof jason && ! (jason instanceof String)) {
        jason = JSON.stringify(jason, null, 4);
    }

    return '<pre>' + syntaxHighlight(jason) + '</pre>';
}

