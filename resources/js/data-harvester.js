(function(w, d){

    w.saveAs = function(filename, mimeType, content){
        var blob = new Blob([ content ], { type: mimeType }),
            link, url;

        if(navigator.msSaveBlob){
            navigator.msSaveBlob(blob, filename);
        } else {
            link = d.createElement('a');

            if(link.download !== undefined){
                url = URL.createObjectURL(blob);

                link.setAttribute('href', url);
                link.setAttribute('download', filename);

                link.style.setProperty('visibility', 'hidden');

                d.body.appendChild(link);

                link.click();

                d.body.removeChild(link);
            }
        }
    };

    w.escapeHTML = (function(){
        var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&#34;',
                "'": '&#39;',
                '/': '&#x2F;'
            },
            replacerFn = function(chr){
                return map[chr];
            };

        return function(s) {
            return String(s).replace(/[&<>'"\/]/g, replacerFn);
        };
    })();

})(window, document);

(function(w, d, csv){

    var C_NONE         = '',
        C_CR           = '\r',
        C_NL           = '\n',
        C_COMMA        = ',',
        C_DOUBLE_QUOTE = '"';

    var RX_SPECIALS     = /[,\r\n"]/,
        RX_DOUBLE_QUOTE = /"/g;

    csv.parse = function(csv, reviver){
        reviver = reviver || function(r, c, v){ return v; };

        var chars = csv.split(C_NONE),
            c     = 0,
            cc    = chars.length,
            table = [],
            start, end, row;

        while(c < cc){
            table.push(row = []);

            while((c < cc) && (C_CR !== chars[c]) && (C_NL !== chars[c])){
                start = end = c;

                if(C_DOUBLE_QUOTE === chars[c]){
                    start = end = ++c;

                    while(c < cc){
                        if(C_DOUBLE_QUOTE === chars[c]){
                            if(C_DOUBLE_QUOTE !== chars[c + 1]){
                                break;
                            } else {
                                chars[++c] = C_NONE;
                            }
                        }

                        end = ++c;
                    }

                    if(C_DOUBLE_QUOTE === chars[c]){
                        ++c;
                    }

                    while((c < cc) && (C_CR !== chars[c]) && (C_NL !== chars[c]) && (C_COMMA !== chars[c])){
                        ++c;
                    }
                } else {
                    while((c < cc) && (C_CR !== chars[c]) && (C_NL !== chars[c]) && (C_COMMA !== chars[c])){
                        end = ++c;
                    }
                }

                row.push(
                    reviver(
                        table.length - 1,
                        row.length,
                        chars.slice(start, end).join(C_NONE)
                    )
                );

                if(C_COMMA === chars[c]){
                    ++c;
                }
            }

            if(C_CR === chars[c]){
                ++c;
            }

            if(C_NL === chars[c]){
                ++c;
            }
        }

        return table;
    };

    csv.stringify = function(table, replacer){
        replacer = replacer || function(r, c, v){ return v; };

        var dd_quote = C_DOUBLE_QUOTE + C_DOUBLE_QUOTE,
            csv      = C_NONE,
            rr       = table.length,
            c,
            cc,
            r,
            cell;

        for(r = 0; r < rr; ++r){
            if(r){
                csv += C_CR + C_NL;
            }

            for(c = 0, cc = table[r].length; c < cc; ++c){
                if(c){
                    csv += C_COMMA;
                }

                cell = replacer(r, c, table[r][c]);

                if(RX_SPECIALS.test(cell)){
                    cell = (
                        C_DOUBLE_QUOTE
                        +
                        cell.replace(RX_DOUBLE_QUOTE, dd_quote)
                        +
                        C_DOUBLE_QUOTE
                    );
                }

                csv += (cell || (0 === cell)) ? cell : C_NONE;
            }
        }

        return csv;
    };

    w.CSV = csv;

})(window, document, {});

(function(w, d, ui){

    ui.elements = {
        divButtons: d.getElementById('divButtons'),
        divDropZone: d.getElementById('divDropZone'),
        divProcessing: d.getElementById('divProcessing'),

        aProcess: d.getElementById('aProcess'),
        aSave: d.getElementById('aSave')
    };

    w.ui = ui;

})(window, document, {});

(function(w, d, ui){
    var templates = {
            waiting: 'Сбросьте файл в формате <b>*.csv</b> здесь.',
            invalid: 'Файл должен иметь формат <b>*.csv</b>.'
        },
        handlers = {
            fileRead: function(e){
                var els  = ui.elements,
                    rows = CSV.parse(e.target.result.replace(/;/g, ',')),
                    rLen = rows.length,
                    tmp,
                    r, row,
                    inn, name;

                els.divProcessing.innerHTML = '';

                els.divButtons.style.visibility    = 'hidden';
                els.divProcessing.style.visibility = 'hidden';

                els.divButtons.style.display    = '';
                els.divProcessing.style.display = '';

                for(r = 0; row = rows[r], r < rLen; r++){
                    inn  = row.shift().trim();
                    name = row.shift().trim();

                    els.divProcessing.innerHTML += [
                        '<div class="row waiting">',
                            '<span class="inn">',
                                '<a href="', w.location.origin, '/index.php?view=requisites&inn=', w.encodeURIComponent(inn), '" target="_blank">',
                                    w.escapeHTML(inn),
                                '</a>',
                            '</span>',
                            '<span class="name">',
                                w.escapeHTML(name) || 'Не указано',
                            '</span>',
                            '<div class="info">',
                                '<i>Ожидание...</i>',
                            '</div>',
                        '</div>'
                    ].join('');
                }

                handlers.fit();

                els.divButtons.style.visibility    = 'visible';
                els.divProcessing.style.visibility = 'visible';
            },
            fileDrop: function(e){
                e.stopPropagation();
                e.preventDefault();

                var types = [ 'text/csv', 'application/vnd.ms-excel' ],
                    els   = ui.elements,
                    files = e.dataTransfer.files,
                    file  = (files && files[0]) || null,
                    reader;

                if(file && (types.indexOf(file.type) > -1)){
                    reader = new FileReader();

                    reader.onload = handlers.fileRead;

                    els.divDropZone.innerHTML = [
                        'Файл <b>"', file.name, '"</b>, ', file.size, ' байт.'
                    ].join('');

                    reader.readAsText(file, 'Windows-1251');
                } else {
                    els.divDropZone.innerHTML = [
                        templates.invalid,
                        '<br><br>',
                        templates.waiting
                    ].join('');

                    els.divProcessing.style.display = 'none';
                    els.divButtons.style.display    = 'none';
                }
            },
            fileDrag: function(e){
                e.stopPropagation();
                e.preventDefault();

                e.dataTransfer.dropEffect = 'copy';
            },
            fit: function(e){
                var div    = ui.elements.divProcessing,
                    body   = d.body,
                    height = Math.min(w.innerHeight, body.clientHeight);

                div.style.height = (height - div.offsetTop - 15) + 'px';
            },
            wndLoad: function(e){
                var els = ui.elements;

                els.divDropZone.innerHTML = templates.waiting;

                els.divDropZone.addEventListener('dragover', handlers.fileDrag);
                els.divDropZone.addEventListener('drop', handlers.fileDrop);

                els.divButtons.style.display    = 'none';
                els.divProcessing.style.display = 'none';

                handlers.fit();
            },
            wndResize: function(e){
                handlers.fit();
            }
        };

    w.addEventListener('load', handlers.wndLoad);
    w.addEventListener('resize', handlers.wndResize);

})(window, document, window.ui);

(function(w, d, ui){

    var consts = {
        REQUEST_INTERVAL: 250,

        REQUEST_TIMEOUT: 10 * 1000,
        REQUEST_METHOD: 'POST',
        REQUEST_ACTION: 'scrap'
    };

    var filler = (function(){
        return {
            sf: function(values){
                var result = [],
                    vLen   = values.length,
                    v, value;

                if(vLen){
                    for(v = 0; value = values[v], v < vLen; v++){
                        result.push(
                            '<table class="values">',
                                '<tr>',
                                    '<th>Наименование</th>',
                                    '<td>', w.escapeHTML(value.PayerName), '</td>',
                                '</tr>',
                                '<tr>',
                                    '<th>Состояние</th>',
                                    '<td>', w.escapeHTML(value.PayerState), '</td>',
                                '</tr>',
                            '</table>'
                        );
                    }
                } else {
                    result.push(
                        '<div class="value">',
                            '<i>Данные не найдены...</i>',
                        '</div>'
                    );
                }

                return result.join('');
            },
            sti: function(value){
                var result = [];

                if(value){
                    result.push(
                        '<table class="values">',
                            '<tr>',
                                '<th>Наименование</th>',
                                '<td>', w.escapeHTML(value[2] || ''), '</td>',
                            '</tr>',
                            '<tr>',
                                '<th>Руководитель</th>',
                                '<td>', w.escapeHTML(value[4] || ''), '</td>',
                            '</tr>'
                    );

                    if(value.length > 5){
                        result.push(
                            '<tr>',
                                '<th>Прочее</th>',
                                '<td>',
                                    w.escapeHTML(value.slice(5).join(', ')),
                                '</td>',
                            '</tr>'
                        );
                    }

                    result.push('</table>');
                } else {
                    result.push(
                        '<div class="value">',
                            '<i>Данные не найдены...</i>',
                        '</div>'
                    );
                }

                return result.join('');
            },
            mj: function(values){
                var result = [],
                    vLen   = values.length,
                    v, value;

                if(vLen){
                    result.push(
                        '<table class="values">',
                            '<tr>',
                                '<th>Наименование</th>',
                                '<th>Cостояние</th>',
                                '<th>Ссылка</th>',
                            '</tr>'
                    );

                    for(v = 0; value = values[v], v < vLen; v++){
                        result.push(
                            '<tr>',
                                '<td>', w.escapeHTML(value[1]), '</td>',
                                '<td>', w.escapeHTML(value[3]), '</td>',
                                '<td>',
                                    '<a href="', value[7] || '#', '" target="_blank">Свидетельство</a>',
                                '</td>',
                            '</tr>'
                        );
                    }

                    result.push('</table>');
                } else {
                    result.push(
                        '<div class="value">',
                            '<i>Данные не найдены...</i>',
                        '</div>'
                    );
                }

                return result.join('');
            },
            error: function(caption, value){
                return [
                    '<div class="section">',
                        caption ? w.escapeHTML(caption) : 'Ошибка',
                    '</div>',
                    '<div class="value">',
                        w.escapeHTML(value),
                    '</div>'
                ].join('');
            }
        };
    })();

    var loader = (function(){
        var sections = [
                {
                    name: 'mj',
                    caption: 'Министерство юстиции'
                }, {
                    name: 'sf',
                    caption: 'Социальный фонд'
                }, {
                    name: 'sti',
                    caption: 'Государственная налоговая служба'
                }
            ],
            handlers = {
                beforeSend: function(){
                    var contentNode = this.lastChild;

                    this.classList.remove('got-results', 'no-results');
                    this.classList.add('pending');

                    this.scrollIntoView();

                    contentNode.innerHTML = '<i>Cбор данных...</i>';
                },
                success: function(response){
                    var contentNode = this.lastChild,
                        sLen        = sections.length,
                        s, section,
                        content;

                    this.classList.remove('pending');
                    this.classList.add('got-results');

                    contentNode.innerHTML = '';

                    for(s = 0; section = sections[s], s < sLen; s++){
                        content = response[section.name];

                        if(content){
                            if(content.message){
                                contentNode.innerHTML += filler.error(
                                    section.caption,
                                    content.message
                                );
                            } else if(section.name in filler) {
                                contentNode.innerHTML += [
                                    '<div class="section ', section.name, '">',
                                        '<div class="caption">',
                                            w.escapeHTML(section.caption),
                                        '</div>',
                                        filler[section.name](content),
                                    '</div>'
                                ].join('');
                            }
                        }
                    }
                },
                failure: function(response){
                    var contentNode = this.lastChild;

                    this.classList.remove('pending');
                    this.classList.add('no-results');

                    contentNode.innerHTML = filler.error(
                        null,
                        response.status == 200
                            ? 'Неверный ответ сервера'
                            : response.status + ' - ' + response.statusText
                    );
                },
                complete: {
                    iterate: function(response){
                        loader.load(this.nextSibling);
                    },
                    finish: function(response){
                        delete ui.isProcessing;

                        if(w.confirm('Сбор окончен. Сохранить данные в файл?')){
                            ui.elements.aSave.click();
                        }
                    }
                }
            };

        return {
            load: function(node){
                if(!node){
                    return;
                }

                var data = {
                        action: consts.REQUEST_ACTION,
                        inn: node.firstChild.firstChild.innerHTML
                    },
                    config = {
                        url: w.location.href,
                        method: consts.REQUEST_METHOD,
                        context: node,
                        timeout: consts.REQUEST_TIMEOUT,

                        data: data,

                        beforeSend: handlers.beforeSend,
                        success: handlers.success,
                        error: handlers.failure,
                        complete: node.nextSibling
                            ? handlers.complete.iterate
                            : handlers.complete.finish
                    },
                    fn = function(){
                        $.ajax(config);
                    };

                w.setTimeout(fn, consts.REQUEST_INTERVAL);
            }
        };
    })();

    var writer = (function(){
        var helpers = {
            vTables: function(node){
                var rRows   = [],
                    headers = [],
                    tables  = node.getElementsByTagName('table'),
                    tLen    = tables.length,
                    t, table,
                    rows, rLen,
                    r, row,
                    cells, cLen,
                    c, cell,
                    rRow;

                for(t = 0; table = tables[t], t < tLen; t++){
                    rRow = [];
                    rows = table.rows;
                    rLen = rows.length;

                    for(r = 0; row = rows[r], r < rLen; r++){
                        cells = row.cells;
                        cLen  = cells.length;

                        for(c = 0; cell = cells[c], c < cLen; c++){
                            if(cell.tagName.toLowerCase() === 'th'){
                                if(headers.indexOf(cell.innerHTML) === -1){
                                    headers.push(cell.innerHTML);
                                }
                            } else {
                                rRow.push(cell.innerHTML);
                            }
                        }
                    }

                    rRows.push(rRow);
                }

                return {
                    headers: headers,
                    rows: rRows
                };
            },
            hTables: function(node){
                var rRows  = [],
                    tables = node.getElementsByTagName('table'),
                    tLen   = tables.length,
                    t, table,
                    rows, rLen,
                    r, row,
                    cells, cLen,
                    c, cell,
                    rRow;

                for(t = 0; table = tables[t], t < tLen; t++){
                    rows = table.rows;
                    rLen = rows.length;

                    for(r = 0; row = rows[r], r < rLen; r++){
                        rRow  = [];
                        cells = row.cells;
                        cLen  = cells.length;

                        for(c = 0; cell = cells[c], c < cLen; c++){
                            rRow.push(cell.innerHTML);
                        }

                        rRows.push(rRow);
                    }
                }

                return {
                    headers: rRows.shift() || [],
                    rows: rRows
                };
            },
            sf: function(node){
                var section = node.getElementsByClassName('sf')[0];

                if(!section){
                    return null;
                }

                var caption = section.firstChild.innerHTML,
                    result  = this.vTables(section);

                result.caption = caption;

                return result;
            },
            sti: function(node){
                var section = node.getElementsByClassName('sti')[0];

                if(!section){
                    return null;
                }

                var caption = section.firstChild.innerHTML,
                    result  = this.vTables(section);

                result.caption = caption;

                return result;
            },
            mj: function(node){
                var section = node.getElementsByClassName('mj')[0];

                if(!section){
                    return null;
                }

                var caption = section.firstChild.innerHTML,
                    result  = this.hTables(section);

                result.caption = caption;

                return result;
            },
            rowMeta: function(node){
                var rows     = [],
                    sections = {
                        'mj': null,
                        'sf': null,
                        'sti': null
                    },
                    s, section;

                for(s in sections){
                    section = this[s](node.lastChild);

                    rows.push(section.rows.length);

                    sections[s] = section;
                }

                return {
                    rowspan: Math.max.apply(null, rows),
                    sections: sections
                };
            }
        };

        return {
            write: function(nodes){
                var result  = [],
                    columns = {},
                    metas   = [],
                    nLen    = nodes.length,
                    n, node,
                    mLen,
                    m, meta,
                    s, section,
                    cLen,
                    r, row,
                    c, column,
                    v, value,
                    isRowStarted;

                result.push(
                    '<!DOCTYPE html>',
                    '<html>',
                        '<head>',
                            '<meta charset="utf-8" />',
                        '</head>',
                        '<body>'
                );

                if(nLen){
                    result.push(
                        '<style type="text/css">',
                            'table { border-collapse: collapse; } ',
                            'table td, table th { border: 1px solid #000; padding: 5px; }',
                        '</style>',
                        '<table>'
                    );

                    for(n = 0; node = nodes[n], n < nLen; n++){
                        meta = helpers.rowMeta(node);

                        for(s in meta.sections){
                            section = meta.sections[s];

                            if(section.headers.length){
                                if(!columns[s]){
                                    columns[s]         = section.headers;
                                    columns[s].caption = section.caption;
                                }

                                delete section.caption;
                                delete section.headers;
                            }
                        }

                        metas.push(meta);
                    }

                    result.push(
                        '<tr>',
                            '<th rowspan="2">ИНН</th>',
                            '<th rowspan="2">Наименование</th>'
                    );

                    for(c in columns){
                        column = columns[c];

                        result.push(
                            '<th colspan="', column.length ,'">',
                                column.caption,
                            '</th>'
                        );
                    }

                    result.push(
                        '</tr>',
                        '<tr>'
                    );

                    for(c in columns){
                        column = columns[c];
                        cLen   = column.length;

                        for(v = 0; value = column[v], v < cLen; v++){
                            result.push('<th>', value, '</th>');
                        }
                    }

                    result.push('</tr>');

                    for(m = 0, mLen = metas.length; meta = metas[m], m < mLen; m++){
                        result.push(
                            '<tr>',
                                '<td rowspan="', meta.rowspan, '">"',
                                    nodes[m].childNodes[0].innerHTML,
                                '"</td>',
                                '<td rowspan="', meta.rowspan, '">',
                                    nodes[m].childNodes[1].innerHTML,
                                '</td>'
                        );

                        isRowStarted = true;

                        for(r = 0; r < meta.rowspan; r++){
                            if(isRowStarted){
                                isRowStarted = false;
                            } else {
                                result.push('<tr>');
                            }

                            for(c in columns){
                                section = meta.sections[c];
                                column  = columns[c];
                                cLen    = column.length;
                                row     = section.rows[r];

                                if(row){
                                    for(v = 0; value = section.rows[r][v], v < cLen; v++){
                                        result.push('<td>', value || '-', '</td>');
                                    }
                                } else {
                                    for(v = 0; v < cLen; v++){
                                        result.push('<td></td>');
                                    }
                                }
                            }

                            result.push('</tr>');
                        }
                    }

                    result.push(
                        '</table>'
                    );
                }

                result.push(
                        '</body>',
                    '</html>'
                );

                return result.join('');
            }
        };
    })();

    var handlers = {
        aProcessClick: function(e){
            var container = ui.elements.divProcessing,
                node      = container.firstChild;

            if(node && !ui.isProcessing){
                ui.isProcessing = true;

                loader.load(node);
            }
        },
        aSaveClick: function(e){
            var nodes   = ui.elements.divProcessing.childNodes,
                content = writer.write(nodes);

            w.saveAs('harvest.html', 'text/html; charset=utf-8', content);
        },
        wndLoad: function(e){
            var els = ui.elements;

            els.aProcess.addEventListener('click', handlers.aProcessClick);
            els.aSave.addEventListener('click', handlers.aSaveClick);
        }
    };

    w.addEventListener('load', handlers.wndLoad);

})(window, document, window.ui);