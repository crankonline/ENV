(function(w, d, u){
    var dialogHideFn = function(){
        u.dialog.hide();
    };

    u.dialog = {
        initialize: function(){
            var background = d.createElement('div'),
                layout     = d.createElement('div'),
                message    = d.createElement('div'),
                image      = d.createElement('img'),
                text       = d.createElement('p'),
                button     = d.createElement('input');

            message.className = 'dialog-message';
            layout.className  = 'dialog-layout';

            button.type      = 'button';
            button.className = 'button';
            button.value     = 'Ок';

            button.onclick   = dialogHideFn;

            background.className     = 'dialog-background-layer';
            background.style.display = 'none';

            message.appendChild(image);
            message.appendChild(text);
            message.appendChild(button);

            layout.appendChild(message);

            background.appendChild(layout);

            d.body.appendChild(background);

            this.elements = {
                ui: background,
                image: image,
                text: text,
                button: button
            };
        },

        deinitialize: function(){
            d.body.removeChild(elements.ui);

            delete this.elements;
        },

        show: function(image, text, showButton, autoHide){
            var elements = this.elements,
                imageEl  = elements.image,
                buttonEl = elements.button,
                textEl   = elements.text,
                uiEl     = elements.ui,
                bodyEl   = d.body;

            if(image == null){
                imageEl.style.display = 'none';
            } else {
                imageEl.style.display = 'initial';

                if(imageEl.src != image){
                    imageEl.src = image;
                }
            }

            buttonEl.style.display = showButton ? 'inline-block' : 'none';
            textEl.innerHTML       = text;

            bodyEl.style.overflow = 'hidden';

            uiEl.style.display = 'table';

            if(autoHide){
                if(autoHide === true){
                    autoHide = 9000;
                }

                w.setTimeout(dialogHideFn, autoHide);
            }
        },

        hide: function(){
            var elements = this.elements,
                uiEl     = elements.ui,
                textEl   = elements.text,
                bodyEl   = d.body;

            bodyEl.style.overflow = 'initial';

            uiEl.style.display = 'none';

            textEl.innerHTML = '';
        },

        showLoading: function(){
            var image = 'resources/images/loading.gif',
                text  = [
                    'Наш софт, наша сеть и наши сервера "обрабатывают запрос".',
                    '<b>Пожалуйста, подождите...</b>'
                ].join('<br>');

            this.show(image, text, false);
        },

        showError: function(error, autoHide){
            var image, text;

            if(error){
                image = 'resources/images/oops.png';
                text  = error;
            } else {
                image = 'resources/images/error.png';
                text  = '<b>Произошла какая-то неведомая ошибка.</b>'
            }

            this.show(image, text, true, autoHide);
        },

        showMessage: function(text, autoHide){
            this.show(null, text, true, autoHide);
        }
    };

    u.initializers.push(
        function(){
            u.dialog.initialize();
        }
    );

})(window, document, window.utils);

(function(w, d, u){

    var tpl = [
            '<div class="anchors">',
                '<div class="container">',
                '</div>',
            '</div>'
        ].join(''),
        cache = {};

    u.anchors = {
        initialize: function(){
            var dom = $.parseHTML(tpl)[0];

            d.body.appendChild(dom);

            this.dom = dom;
        },

        deinitialize: function(){
            var dom = this.dom;

            if(dom){
                d.body.removeChild(dom);

                delete this.dom;
            }
        },

        append: (function(){
            var emptyFn = function(){};

            return function(id, cfg){
                var dom = this.dom;

                if(dom){
                    var anchor = d.createElement('a');

                    if(!$.isPlainObject(cfg)){
                        cfg = {};
                    }

                    id = id || ('anchors-' + u.sequence.next('anchors'));

                    anchor.id        = id;
                    anchor.href      = cfg.href || 'javascript: void(0)';
                    anchor.innerHTML = cfg.text || '?';
                    anchor.onclick   = $.isFunction(cfg.handler)
                        ? cfg.handler
                        : emptyFn;

                    dom.childNodes[0].appendChild(anchor);

                    cache[id] = anchor;

                    this.hide(id);
                }
            };
        })(),

        remove: function(id){
            var dom = this.dom;

            if(dom && (id in cache)){
                dom.childNodes[0].removeChild(cache[id]);

                delete cache[id];

                return true;
            }

            return false;
        },

        get: function(id){
            return cache[id] || null;
        },

        show: function(id){
            if(id in cache){
                cache[id].style.display = 'block';
            }
        },

        hide: function(id){
            if(id in cache){
                cache[id].style.display = 'none';
            }
        }
    };

    u.initializers.push(
        function(){
            u.anchors.initialize();
        }
    );

})(window, document, window.utils);

(function(w, d, u){

    var cfg  = null,
        idx  = null,
        step = null;

    function free(){
        cfg = idx = step = null;
    }

    function showUi(){
        var elements = u.wizard.elements,
            uiEl     = elements.ui,
            bodyEl   = d.body;

        uiEl.style.display    = 'table';
        bodyEl.style.overflow = 'hidden';
    }

    function hideUi(){
        var elements  = u.wizard.elements,
            uiEl      = elements.ui,
            displayEl = elements.display,
            bodyEl    = d.body;

        uiEl.style.display    = 'none';
        bodyEl.style.overflow = 'initial';

        displayEl.innerHTML = '';
    }

    function doStep(step){
        if('render' in step){
            step.render.call(cfg.context, u.wizard.elements.display);
        }

        if('onStart' in step){
            step.onStart.call(cfg.context, cfg.steps, step, idx);
        }
    }

    function nextStep(){
        if('onComplete' in step){
            step.onComplete.call(cfg.context, cfg.steps, step, idx);
        }

        if('next' in step){
            idx  = $.isFunction(step.next) ? step.next.call(cfg.context) : step.next;
            step = cfg.steps[idx];

            doStep(step);
        } else {
            hideUi();

            if('onFinish' in cfg){
                cfg.onFinish.call(cfg.context, cfg.steps, step, idx);
            }

            free();
        }
    }

    function prevStep(){
        if('onCancel' in step){
            step.onCancel.call(cfg.context, cfg.steps, step, idx);
        }

        if('prev' in step){
            idx  = $.isFunction(step.prev) ? step.prev.call(cfg.context) : step.prev;
            step = cfg.steps[idx];

            doStep(step);
        } else {
            hideUi();

            if('onAbort' in cfg){
                cfg.onAbort.call(cfg.context);
            }

            free();
        }
    }

    u.wizard = {
        initialize: function(){
            var background = d.createElement('div'),
                layout     = d.createElement('div'),
                box        = d.createElement('div'),
                display    = d.createElement('div'),
                btnPrev    = d.createElement('input'),
                btnNext    = d.createElement('input');

            background.className     = 'wizard-background-layer';
            background.style.display = 'none';

            layout.className  = 'wizard-layout';
            box.className     = 'wizard-box';
            display.className = 'wizard-display';

            btnPrev.value     = '<<';
            btnPrev.type      = 'button';
            btnPrev.className = 'button';
            btnPrev.onclick   = prevStep;

            btnNext.value     = '>>';
            btnNext.type      = 'button';
            btnNext.className = 'button';
            btnNext.onclick   = nextStep;

            background.appendChild(layout);

            layout.appendChild(box);

            box.appendChild(display);
            box.appendChild(btnPrev);
            box.appendChild(btnNext);

            d.body.appendChild(background);

            this.elements = {
                ui: background,
                display: display,

                buttons: {
                    prev: btnPrev,
                    next: btnNext
                }
            };
        },

        deinitialize: function(){
            d.body.removeChild(u.wizard.elements.ui);

            delete this.elements;

            free();
        },

        run: function(config){
            cfg = config || {};

            cfg.steps   = cfg.steps || [];
            cfg.context = cfg.context || {};

            if('onStart' in cfg){
                cfg.onStart.call(cfg.context, cfg.steps, step, idx);
            }

            idx  = 0;
            step = cfg.steps.length ? cfg.steps[idx] : null;

            if(step){
                showUi();

                doStep(step);
            }
        }
    };

    u.initializers.push(
        function(){
            u.wizard.initialize();
        }
    );

})(window, document, window.utils);

(function(w, d, u){

    var templates = {
        ui: [
            '<div class="usage-status-dialog">',
                '<div class="layout">',
                    '<div class="box">',
                        '<div class="current"></div>',
                        '<div class="caption">История состояний:</div>',
                        '<div class="history"></div>',
                        '<div class="caption">Изменение состояния:</div>',
                        '<div class="editor">',
                            '<table class="status">',
                                '<tr>',
                                    '<th>Состояние:</th>',
                                    '<td>',
                                        '<select id="sltUsageStatus{0}">',
                                            '<option value="1">Обслуживается</option>',
                                            '<option value="0">Не обслуживается</option>',
                                        '</select>',
                                    '</td>',
                                '</tr>',
                                '<tr>',
                                    '<th colspan="2">Комментарий:</th>',
                                '</tr>',
                                '<tr>',
                                    '<td colspan="2">',
                                        '<textarea id="txtarUsageStatusDescription{0}" placeholder="Укажите комментарий..."></textarea>',
                                    '</td>',
                                '</tr>',
                                '<tr>',
                                    '<td colspan="2" class="buttons">',
                                        '<input type="button" class="button" id="btnUsageStateApply{0}" value="Применить" />',
                                        ' ',
                                        '<input type="button" class="button" id="btnUsageStateСancel{0}" value="Отмена" />',
                                    '</td>',
                                '</tr>',
                            '</table>',
                        '</div>',
                        '<div class="idle"></div>',
                    '</div>',
                '</div>',
            '</div>'
        ].join(''),

        status: [
            '<table class="status">',
                '<tbody>',
                    '<tr>',
                        '<th>Состояние:</th>',
                        '<td>{0}</td>',
                    '</tr>',
                    '<tr>',
                        '<th>Дата и время:</th>',
                        '<td>{1}</td>',
                    '</tr>',
                    '<tr>',
                        '<th colspan="2">Комментарий:</th>',
                    '</tr>',
                    '<tr>',
                        '<td colspan="2"><pre>{2}</pre></td>',
                    '</tr>',
                '</tbody>',
            '</div>'
        ].join(''),

        message: '<div class="{0}">{1}</div>',
    };

    function renderUsageStatus(usageStatus){
        var escapeFn = u.string.escapeHTML;

        return u.string.format(
            templates.status,
            usageStatus.isActive
                ? 'Обслуживается'
                : 'Не обслуживается',
            escapeFn(usageStatus.dateTime),
            usageStatus.description
                ? escapeFn(usageStatus.description)
                : '(комментарий отсутствует)'
        );
    }

    function renderMessage(type, message){
        if(type){
            type = 'message ' + type;
        }

        return u.string.format(templates.message, type, message);
    }

    var getUsageStatusesFn = (function(){
        var ajaxNotFoundTpl = 'История назначений отсутствует.',
            ajaxCommonErrorTpl = [
                '<p class="caption"><b>Произошла ошибка:</b></p>',
                '<pre class="error">{0} ({1})</pre>'
            ].join(''),
            data = {},
            ajaxSuccessHandlerFn = function(response){
                var elements = u.usageStatusManager.elements,
                    sections = elements.sections,
                    statuses;

                if(response.success){
                    statuses = response['result-set'];

                    if(statuses && statuses.length){
                        var buf  = '',
                            sLen = statuses.length,
                            s, status;

                        for(s = 0; s < sLen; s++){
                            status = statuses[s];

                            buf += renderUsageStatus(status);
                        }

                        sections.history.innerHTML = buf;

                        if(!status){
                            sections.current.innerHTML = renderMessage(
                                'neutral',
                                'Состояние не задано'
                            );
                        } else if(status.isActive) {
                            sections.current.innerHTML = renderMessage(
                                'positive',
                                'Клиент обслуживается (' + u.string.escapeHTML(status.dateTime) + ')'
                            );
                        } else {
                            sections.current.innerHTML = renderMessage(
                                'negative',
                                'Клиент не обслуживается (' + u.string.escapeHTML(status.dateTime) + ')'
                            );
                        }

                    } else {
                        sections.history.innerHTML = ajaxNotFoundTpl;
                    }
                } else {
                    sections.history.innerHTML = renderMessage(
                        'negative',
                        u.string.format(
                            ajaxCommonErrorTpl,
                            u.string.escapeHTML(response['error-message']),
                            u.string.escapeHTML(response['error-code'])
                        )
                    );
                }
            },
            ajaxErrorHandlerFn = function(response){
                var elements = u.usageStatusManager.elements,
                    sections = elements.sections;

                sections.history.innerHTML = renderMessage(
                    'negative',
                    response.status == 200
                        ? 'Ошибка при обработке ответа.'
                        : u.string.format(
                            ajaxCommonErrorTpl,
                            u.string.escapeHTML(response.statusText),
                            u.string.escapeHTML(response.status)
                        )
                );
            },
            ajaxCompleteHandlerFn = function(response){
                for(var property in data){
                    delete data[property];
                }
            },
            config = {
                type: 'POST',
                url: 'services.php?service=UsageStatuses',
                data: data,
                success: ajaxSuccessHandlerFn,
                error: ajaxErrorHandlerFn,
                complete: ajaxCompleteHandlerFn
            };

        return function(uid){
            var elements = u.usageStatusManager.elements,
                sections = elements.sections;

            sections.current.innerHTML = '';
            sections.history.innerHTML = renderMessage('neutral', 'Загрузка...');

            data.action = 'getStatuses';
            data.uid    = uid;

            $.ajax(config);
        };
    })();

    var btnUsageStateApplyClickHandlerFn = (function(){
        var ajaxSuccessTpl = [
                '<b>Состояние успешно назначено.</b>',
                '<br>',
                'Эффект будет виден при следующем поиске.'
            ].join(''),
            ajaxCommonErrorTpl = [
                '<p class="caption"><b>Произошла ошибка:</b></p>',
                '<pre class="error">{0} ({1})</pre>'
            ].join(''),
            data = {},
            ajaxSuccessHandlerFn = function(response){
                var elements = u.usageStatusManager.elements,
                    sections = elements.sections,
                    statuses;

                if(response.success){
                    sections.idle.innerHTML = renderMessage(
                        'positive',
                        ajaxSuccessTpl
                    );

                    if(u.data && u.data.uid){
                        getUsageStatusesFn(u.data.uid);
                    }
                } else {
                    sections.idle.innerHTML = renderMessage(
                        'negative',
                        u.string.format(
                            ajaxCommonErrorTpl,
                            u.string.escapeHTML(response['error-message']),
                            u.string.escapeHTML(response['error-code'])
                        )
                    );
                }
            },
            ajaxErrorHandlerFn = function(response){
                var elements = u.usageStatusManager.elements,
                    sections = elements.sections;

                sections.idle.innerHTML = renderMessage(
                    'negative',
                    response.status == 200
                        ? 'Ошибка при обработке ответа.'
                        : u.string.format(
                            ajaxCommonErrorTpl,
                            u.string.escapeHTML(response.statusText),
                            u.string.escapeHTML(response.status)
                        )
                );
            },
            ajaxCompleteHandlerFn = function(response){
                for(var property in data){
                    delete data[property];
                }
            },
            config = {
                type: 'POST',
                url: 'services.php?service=UsageStatuses',
                data: data,
                success: ajaxSuccessHandlerFn,
                error: ajaxErrorHandlerFn,
                complete: ajaxCompleteHandlerFn
            };

        return function(){
            var uid      = u.data && u.data.uid,
                elements = u.usageStatusManager.elements,
                sections = elements.sections,
                controls = elements.controls;

            if(!uid){
                return;
            }

            data['action']      = 'setStatus';
            data['uid']         = uid;
            data['is-active']   = parseInt(controls.sltUsageStatus.value);
            data['description'] = controls.txtarUsageStatusDescription.value || null;

            sections.idle.innerHTML = renderMessage('neutral', 'Применение...');

            $.ajax(config);
        };
    })();

    var btnUsageStateСancelClickHandlerFn = function(){
        u.usageStatusManager.hide();
    };

    u.usageStatusManager = {
        initialize: function(){
            if(this.elements){
                this.deinitialize();
            }

            var idx      = u.sequence.next('usage-status-manager'),
                tpl      = u.string.format(templates.ui, idx),
                ui       = $.parseHTML(tpl)[0],
                layout   = ui.firstChild,
                box      = layout.firstChild,
                sections = box.childNodes;

            ui.style.display = 'none';

            d.body.appendChild(ui);

            this.elements = {
                ui: ui,
                sections: {
                    current: sections[0],
                    history: sections[2],
                    editor: sections[4],
                    idle: sections[5]
                },
                controls: {
                    sltUsageStatus:
                        d.getElementById('sltUsageStatus' + idx),

                    txtarUsageStatusDescription:
                        d.getElementById('txtarUsageStatusDescription' + idx),

                    btnUsageStateApply:
                        d.getElementById('btnUsageStateApply' + idx),

                    btnUsageStateСancel:
                        d.getElementById('btnUsageStateСancel' + idx)
                }
            };

            this.elements.controls.btnUsageStateApply.onclick
                = btnUsageStateApplyClickHandlerFn;

            this.elements.controls.btnUsageStateСancel.onclick
                = btnUsageStateСancelClickHandlerFn;
        },

        deinitialize: function(){
            d.body.removeChild(this.elements.ui);

            delete this.elements;
        },

        show: function(){
            var elements = this.elements,
                sections = elements.sections;

            if(u.data && u.data.uid){
                getUsageStatusesFn(u.data.uid);
            }

            elements.ui.style.display = 'table';

            d.body.style.overflow = 'hidden';
        },

        hide: function(){
            var elements = this.elements,
                sections = elements.sections,
                controls = elements.controls;

            elements.ui.style.display = 'none';

            d.body.style.overflow = 'initial';

            controls.sltUsageStatus.value              = 0;
            controls.txtarUsageStatusDescription.value = '';
        }
    };

    u.initializers.push(
        function(){
            u.usageStatusManager.initialize();
        }
    );

    u.initializers.push(
        function(){
            var id  = 'aUsageStatusManager',
                fn  = function(){
                    u.usageStatusManager.show();
                },
                cfg = {
                    text: 'Состояние',
                    handler: fn
                };

            u.anchors.append(id, cfg);
        }
    );

})(window, document, window.utils);

(function(w, d, u){

    u.initializers.push(
        function(){
            u.anchors.append('aCloudUsers', { text: 'Облако: 0' });
            u.anchors.append('aLocalUsers', { text: 'RuToken: 0' });
        }
    );

})(window, document, window.utils);

(function(w, d, u){

    var elements = null,
        cfg      = null;

    var consts = {
        MODE_REQUISITES: 0,
        MODE_STATEMENTS_FOR_REVISION: 1,
        MODE_STATEMENTS_PAID: 2,
        MODE_STATEMENTS_REJECTED: 3,

        POSITIONS_ACCOUNTANT: 3,

        ROLES_CHIEF: 1,
        ROLES_ACCOUNTANT: 2,
        ROLES_EDS_RECEIVER: 3,
        ROLES_EDS_USER: 4,
        ROLES_CONSULTING_AGENT: 5,
        ROLES_CONSULTING_ROOT: 6,

        CIVIL_LEGAL_STATUS_PHYSICAL: 2,

        EDS_USAGE_MODEL_LOCAL: 1,
        EDS_USAGE_MODEL_CLOUD: 2,

        VK_ENTER: 13
    };

    var rolesCache = {};

    var chiefMap = {
            passportSeries: 'common-chief-passport-series',
            passportNumber: 'common-chief-passport-number',

            passportIssuingAuthority: 'common-chief-passport-issuing-authority',
            passportIssingDate:       'common-chief-passport-issuing-date',

            position:   'common-chief-position',
            surname:    'common-chief-surname',
            name:       'common-chief-name',
            middleName: 'common-chief-middle-name',

            workPhone: 'common-chief-work-phone',

            roles: [],

            edsUsageModels: []
        },
        juristicAddressMap = {
            postCode:   'common-juristic-post-code',
            location:   'common-juristic-location',
            settlement: 'common-juristic-settlement',
            street:     'common-juristic-street',
            building:   'common-juristic-building',
            apartment:  'common-juristic-apartment'
        },
        physicalAddressMap = {
            postCode:   'common-physical-post-code',
            location:   'common-physical-location',
            settlement: 'common-physical-settlement',
            street:     'common-physical-street',
            building:   'common-physical-building',
            apartment:  'common-physical-apartment'
        };

    var defaultOption = {
            name: '-- выберите значение --'
        },
        emptyOptionTpl   = '<option value="">{0}</option>',
        defaultOptionTpl = '<option value="{0}">{1}</option>',
        codeOptionTpl    = '<option value="{0}">{1} (код {0})</option>',
        facetOptionTpl   = '<option value="{0}">{1} (фасет {2})</option>',
        optionRenderDefaultFn = function(o){
            var html = 'id' in o
                ? u.string.format(defaultOptionTpl, o.id, o.name)
                : u.string.format(emptyOptionTpl, o.name);

            return html;
        },
        optionRenderWithCodeFn = function(o){
            var html = 'id' in o
                ? u.string.format(codeOptionTpl, o.id, o.name)
                : u.string.format(emptyOptionTpl, o.name);

            return html;
        },
        optionRenderWithFacetFn = function(o){
            var html = 'id' in o
                ? o.facet === null
                    ? u.string.format(defaultOptionTpl, o.id, o.name)
                    : u.string.format(facetOptionTpl, o.id, o.name, o.facet)
                : u.string.format(emptyOptionTpl, o.name);

            return html;
        };

    var modes = (function(){
        var divModes, btnSubmit;

        var modes = [
            {
                code: consts.MODE_REQUISITES,
                text: 'В службе реквизитов',
                handler: function(){
                    btnSubmit.style.display = '';
                }
            }, {
                code: consts.MODE_STATEMENTS_FOR_REVISION,
                text: 'В заявках на проверку',
                handler: function(){
                    btnSubmit.style.display = 'none';
                }
            }, {
                code: consts.MODE_STATEMENTS_PAID,
                text: 'В оплаченных заявках',
                handler: function(){
                    btnSubmit.style.display = '';
                }
            }, {
                code: consts.MODE_STATEMENTS_REJECTED,
                text: 'В отклоненных заявках',
                handler: function(){
                    btnSubmit.style.display = '';
                }
            }
        ];

        var renderModeFn = function(mode, isSelected){
            var tpl = [
                    '<label>',
                        '<input type="radio" name="modes" value="', mode.code, '"',
                            isSelected ? ' checked' : '',
                            '/>',
                        '<span>' + mode.text + '</span>',
                    '</label>'
                ].join(''),
                dom = $.parseHTML(tpl)[0];

            dom.childNodes[0].onclick = mode.handler;

            return dom;
        };

        var renderModesFn = function(){
            var mLen = modes.length,
                m, mode;

            if(!(divModes && btnSubmit)){
                divModes  = d.getElementById('divModes');
                btnSubmit = d.getElementById('btnSubmit');
            }

            divModes.innerHTML = '';

            for(m = 0; mode = modes[m], m < mLen; m++){
                mode = renderModeFn(mode, mode.code == consts.MODE_REQUISITES);

                divModes.appendChild(mode);
            }
        };

        var getValueFn = function(){
            var nodes = divModes.getElementsByTagName('input'),
                nLen  = nodes.length,
                n, node;

            for(n = 0; node = nodes[n], n < nLen; n++){
                if(node.checked){
                    return parseInt(node.value);
                }
            }

            return consts.MODE_REQUISITES;
        };

        return {
            initialize: renderModesFn,
            getMode: getValueFn
        };
    })();

    var updateUsageModelAnchorsFn = (function(){
        var mapVFn   = function(value){
                return value;
            },
            mapIntFn = function(value){
                return parseInt(value);
            },
            grepFn = function(value){
                return value !== null;
            },
            getEdsCountFn = function(map){
                var roles = $.map(
                        $.grep(
                            getValues(map.roles),
                            grepFn
                        ),
                        mapIntFn
                    ),
                    result = 0;

                if(map.name.indexOf('-chief-') > -1){
                    roles.push(consts.ROLES_CHIEF);
                }

                if($.inArray(consts.ROLES_CONSULTING_AGENT, roles) > -1){
                    return result;
                }

                result += ($.inArray(consts.ROLES_CHIEF, roles) > -1);
                result += ($.inArray(consts.ROLES_ACCOUNTANT, roles) > -1);

                return result;
            };

        return function(){
            var anchors = {},
                counts  = {},
                maps    = [ chiefMap ].concat($.map(cfg.representatives.maps, mapVFn)),
                mLen    = maps.length,
                m, map,
                a, anchor,
                radios, rLen,
                r, radio,
                edsCount;

            anchors[consts.EDS_USAGE_MODEL_CLOUD] = u.anchors.get('aCloudUsers');
            anchors[consts.EDS_USAGE_MODEL_LOCAL] = u.anchors.get('aLocalUsers');

            for(m = 0; map = maps[m], m < mLen; m++){
                radios = map.edsUsageModels;
                rLen   = radios.length;

                edsCount = getEdsCountFn(map);

                for(r = 0; radio = elements[radios[r]], r < rLen; r++){
                    a = w.parseInt(radio.value);

                    if(radio.checked){
                        if(a in counts){
                            counts[a] += edsCount;
                        } else {
                            counts[a] = edsCount;
                        }
                    }
                }
            }

            for(a in anchors){
                anchor = anchors[a];

                if(counts[a]){
                    anchor.innerHTML = anchor.innerHTML.replace(reDigits, counts[a]);

                    u.anchors.show(anchor.id);
                } else {
                    u.anchors.hide(anchor.id);
                }
            }
        };
    })();

    function cacheElements(){
        var els  = cfg.form.elements,
            eLen = els.length,
            e, el,
            name;

        elements = {};

        for(e = 0; el = els[e], e < eLen; e++){
            if(el.name && (el.type != 'radio') && (el.type != 'checkbox')){
                name = el.name;
            } else if(el.id) {
                name = '#' + el.id
            } else {
                continue;
            }

            if(name in elements){
                if(elements[name] instanceof Array){
                    if($.inArray(el, elements[name]) === -1){
                        elements[name].push(el);
                    }
                } else if(elements[name] != el) {
                    elements[name] = [
                        elements[name],
                        el
                    ];
                }
            } else {
                elements[name] = el;
            }
        }
    }

    function cacheRole(role, isUsed){
        if(isUsed){
            if(role in rolesCache){
                rolesCache[role]++;
            } else {
                rolesCache[role] = 1;
            }
        } else {
            if(rolesCache[role] > 1){
                rolesCache[role]--;
            } else {
                delete rolesCache[role];
            }
        }
    }

    function fixEvent(e){
        return e || w.event;
    }

    function skipEvent(e){
        e = fixEvent(e);

        if(e.preventDefault){
            e.preventDefault();
        } else {
            e.returnValue = false;
        }

        return false;
    }

    function setDisabled(fields, is){
        if($.isPlainObject(fields)){
            var property, field;

            for(property in fields){
                field = fields[property];

                if($.isArray(field) || $.isPlainObject(field)){
                    setDisabled(field, is);
                } else {
                    elements[field].disabled = is;
                }
            }
        } else if($.isArray(fields)) {
            var fLen = fields.length,
                f, field;

            for(f = 0; field = fields[f], f < fLen; f++){
                elements[field].disabled = is;
            }
        }
    }

    function getFieldValue(field){
        if(!field.disabled){
            switch(field.type.toLowerCase()){
                case 'checkbox':
                case 'radio':
                    if(field.checked){
                        return field.value;
                    }
                break;

                case 'select':
                    if(field.options.length && (field.selectedIndex > -1)){
                        return field.options[field.selectedIndex];
                    }
                break;

                default:
                    return field.value;
                break;
            }
        }

        return null;
    }

    function getValues(fields){
        var result = [];

        if($.isPlainObject(fields)){
            var property, field;

            for(property in fields){
                field = fields[property];

                if($.isArray(field) || $.isPlainObject(field)){
                    result = result.concat(getValues(field));
                } else {
                    result.push(getFieldValue(elements[field]));
                }
            }
        } else if($.isArray(fields)) {
            var fLen = fields.length,
                f, field;

            for(f = 0; field = fields[f], f < fLen; f++){
                result.push(getFieldValue(elements[field]));
            }
        }

        return result;
    }

    function resetField(field){
        var events = [ 'onchange', 'onkeyup', 'onblur' ],
            eLen   = events.length,
            e, ev;

        switch(field.tagName.toLowerCase()){
            case 'select':
                if(field.options.length){
                    field.options[0].selected = true;
                }
            break;

            case 'input':
                switch(field.type){
                    case 'radio':
                    case 'checkbox':
                        field.checked = false;
                    break;

                    case 'text':
                        field.value = '';
                    break;
                }
            break;

            case 'textarea':
                field.value = '';
            break;
        }

        for(e = 0; ev = events[e], e < eLen; e++){
            if($.isFunction(field[ev])){
                field[ev]();
            }
        }
    }

    function resetFields(fields){
        if($.isPlainObject(fields)){
            var property, field;

            for(property in fields){
                field = fields[property];

                if($.isArray(field) || $.isPlainObject(field)){
                    resetFields(field);
                } else {
                    resetField(elements[field]);
                }
            }
        } else if($.isArray(fields)) {
            var fLen = fields.length,
                f, field;

            for(f = 0; field = fields[f], f < fLen; f++){
                resetField(elements[field]);
            }
        }
    }

    function pasteOptionsToListElement(el, records, renderFn){
        el.innerHTML = $.map(records, renderFn).join('');
    }

    function pasteOptionToStaticListElements(){
        var dictionaries = cfg.dictionaries,
            map = [
                [
                    'common-ownership-form',
                    'common-ownership-form',
                    optionRenderWithFacetFn
                ], [
                    'common-capital-form',
                    'common-capital-form',
                    optionRenderWithFacetFn
                ], [
                    'common-management-form',
                    'common-management-form',
                    optionRenderDefaultFn
                ], [
                    'common-civil-legal-status',
                    'common-civil-legal-status',
                    optionRenderWithFacetFn
                ], [
                    'common-chief-position',
                    'common-representative-position',
                    optionRenderDefaultFn
                ], [
                    'common-chief-basis',
                    'common-chief-basis',
                    optionRenderDefaultFn
                ], [
                    'sf-region',
                    'sf-region',
                    optionRenderWithCodeFn
                ], [
                    'sf-tariff',
                    'sf-tariff',
                    optionRenderWithCodeFn
                ], [
                    'sti-region-default',
                    'sti-region',
                    optionRenderWithCodeFn
                ], [
                    'sti-region-receiver',
                    'sti-region',
                    optionRenderWithCodeFn
                ]
            ],
            mLen = map.length,
            m, args;

        for(m = 0; args = map[m], m < mLen; m++){
            args[0] = elements[args[0]];
            args[1] = [ defaultOption ].concat(dictionaries[args[1]]);

            pasteOptionsToListElement.apply(null, args);
        }
    }

    function selectOptionByValue(el, value){
        var options = el.options,
            oLen    = options.length,
            o, option;

        for(o = 0; option = options[o], o < oLen; o++){
            if(!option.disabled && (option.value == value)){
                option.selected = true;

                break;
            }
        }
    }

    function autoHeightTextArea(el){
        $(el).height(0);

        if(el.value){
            $(el).height(el.scrollHeight - 20);
        }
    }

    var isEdsRequiredByMapFn = (function(){
        var grepFn = function(value){
                return value !== null;
            },
            mapFn  = function(value){
                return parseInt(value);
            };

        return function(map){
            var roles = $.map(
                $.grep(
                    getValues(map.roles),
                    grepFn
                ),
                mapFn
            );

            if(map.name.indexOf('-chief-') > -1){
                roles.push(consts.ROLES_CHIEF);
            }

            return (
                ($.inArray(consts.ROLES_CHIEF, roles) > -1)
                ||
                ($.inArray(consts.ROLES_ACCOUNTANT, roles) > -1)
            );
        };
    })();

    var detectBtnAddCommonRepresentativeStateFn = (function(){
        var mapFn = function(record){
                return record.id;
            },
            extractFn = function(value){
                return value;
            };

        return function(){
            var used   = $.map(cfg.representatives.maps, extractFn).length,
                cached = $.map(rolesCache, extractFn).length,
                roles  = $.map(cfg.dictionaries['common-representative-role'], mapFn).length,
                state  = (used < roles) && (cached < roles);

            elements['#btnAddRepresentative'].style.display = state ? 'initial' : 'none';
        };
    })();

    var applyRolesRemainsFn = (function(){
        var mapFn = function(value){
            return value;
        };

        function markRolesDiabledByMap(map){
            var fields = map.roles,
                fLen   = fields.length,
                f, field;

            for(f = 0; field = fields[f], f < fLen; f++){
                field = elements[field];

                field.disabled = (
                    (field.value in rolesCache)
                    &&
                    !field.checked
                );
            }
        }

        return function(){
            var maps = [ chiefMap ].concat($.map(cfg.representatives.maps, mapFn)),
                mLen = maps.length,
                m, map;

            for(m = 0; map = maps[m], m < mLen; m++){
                markRolesDiabledByMap(map);
            }

            detectBtnAddCommonRepresentativeStateFn();
        };
    })();

    var pasteRepresentativeDictionariesFn = (function(){
        var rolesRenderFn = (function(){
            var tpl = [
                '<tr>',
                    '<th colspan="2">',
                        '<input type="checkbox" id="{0}" name="{1}" value="{2}"> ',
                        '<label for="{0}">{3}</label>',
                    '</th>',
                '</tr>'
            ].join('');

            var checkboxChangeHandlerFn = function(){
                var parts  = this.id.split('_'),
                    map    = parts.length == 2
                        ? chiefMap
                        : cfg.representatives.maps[parts.pop()],
                    role   = this.value,
                    isUsed = this.checked;

                setDisabled(map.edsUsageModels, !isEdsRequiredByMapFn(map));

                cacheRole(role, isUsed);

                applyRolesRemainsFn();
                updateUsageModelAnchorsFn();
            };

            return function(map, id, name, record){
                var value = record.id;

                id   = $.isFunction(id) ? id(value) : id;
                name = $.isFunction(name) ? name(value) : name;

                map.roles.push('#' + id);

                var caption = record.name,
                    html    = u.string.format(tpl, id, name, value, caption),
                    row     = $.parseHTML(html)[0];

                row.getElementsByTagName('input')[0].onchange = checkboxChangeHandlerFn;

                return row;
            };
        })();

        var edsUsageModelRenderFn = (function(){
            var tpl = [
                '<tr>',
                    '<th colspan="2">',
                        '<input type="radio" id="{0}" name="{1}" value="{2}" disabled> ',
                        '<label for="{0}">{3}</label>',
                    '</th>',
                '</tr>'
            ].join('');

            var radioChangeHandlerFn = function(){
                var parts = this.id.split('_'),
                    map   = parts.length == 2
                        ? chiefMap
                        : cfg.representatives.maps[parts.pop()],
                    edsUsageModel = this.value,
                    isChosen      = this.checked;

                elements[map.deviceSerial].disabled = !(
                    (edsUsageModel == consts.EDS_USAGE_MODEL_LOCAL)
                    &&
                    isChosen
                );

                updateUsageModelAnchorsFn();
            };

            return function(map, id, name, record){
                var value = record.id;

                id = $.isFunction(id) ? id(value) : id;

                map.edsUsageModels.push('#' + id);

                var caption = record.name,
                    html    = u.string.format(tpl, id, name, value, caption),
                    row     = $.parseHTML(html)[0];

                row.getElementsByTagName('input')[0].onchange = radioChangeHandlerFn;

                return row;
            };
        })();

        var renderSectionFn = (function(){
            var tpl = [
                '<tr>',
                    '<td colspan="2" class="section-name">{0}</td>',
                '</tr>'
            ].join('');

            return function(parent, el, caption, dictionary, renderFn){
                var captionEl = $.parseHTML(u.string.format(tpl, caption))[0],
                    options   = $.map(dictionary, renderFn),
                    oLen      = options.length,
                    o, option;

                el = parent.insertBefore(captionEl, el.nextSibling);

                for(o = 0; option = options[o], o < oLen; o++){
                    el = parent.insertBefore(option, el.nextSibling);
                }

                return el;
            };
        })();

        var map = [
            [
                'Роли в системе',
                'common-representative-role',
                rolesRenderFn
            ], [
                'Модель использования ЭЦП',
                'common-eds-usage-model',
                edsUsageModelRenderFn
            ]
        ];

        function fnBind(fn, presets){
            return function(){
                return fn.apply(null, presets.concat($.makeArray(arguments)));
            };
        };

        var extractValuesFn = function(value){
            return value;
        };

        return function(fldMap, el, config){
            var parent        = el.parentNode,
                mLen          = map.length,
                renderPresets = [ fldMap, null, null ],
                m, args,
                curConfig;

            for(m = 0; m < mLen; m++){
                args      = $.makeArray(map[m]);
                curConfig = config[args[1]];

                args[1] = cfg.dictionaries[args[1]];

                if(curConfig && ('render' in curConfig)){
                    if('id' in curConfig.render){
                        renderPresets[1] = curConfig.render.id;
                    }

                    if('name' in curConfig.render){
                        renderPresets[2] = curConfig.render.name;
                    }
                }

                args[2] = fnBind(args[2], renderPresets);

                el = renderSectionFn.apply(null, [ parent, el ].concat(args));

                if(curConfig && $.isFunction(curConfig.callback)){
                    el = curConfig.callback(fldMap, el);
                }
            }

            return el;
        };
    })();

    var pasteChiefDictionariesFn = (function(){
        var completeTpl = [
            '<tr>',
                '<th>Серийный номер устройства (<a href = "javascript:insertSerial(\'common-chief-device-serial\')">Прочитать</a>)</th>',
                '<td><input required disabled type="text" maxlength="10" placeholder="Серийный номер" name="common-chief-device-serial"></td>',
            '</tr>'
        ].join('');

        var config = {
            'common-representative-role': {
                render: {
                    id: function(value){
                        return 'chkbxChiefRole_' + value;
                    },
                    name: 'common-chief-roles[]'
                }
            },
            'common-eds-usage-model': {
                render: {
                    id: function(value){
                        return 'rdoChiefEdsUsageModel_' + value;
                    },
                    name: 'common-chief-eds-usage-model'
                },
                callback: function(map, el){
                    var dom = $.parseHTML(completeTpl)[0];

                    map.deviceSerial = dom.getElementsByTagName('input')[0].name;

                    el.parentNode.appendChild(dom);
                }
            }
        };

        return function(){
            var el = d.getElementById('tblChief');

            el = el.getElementsByTagName('tbody')[0].lastChild;

            pasteRepresentativeDictionariesFn(chiefMap, el, config);
        };
    })();

    function resetMainActivity(){
        var fldGked  = elements['#txtCommonMainActivityGked'],
            fldText  = elements['#txtarCommonMainActivityText'],
            fldValue = elements['common-main-activity'];

        fldGked.value = fldText.value = fldValue.value = '';

        autoHeightTextArea(fldText);
    }

    function purgeRepresentatives(){
        var links = cfg.representatives.links,
            l;

        for(l in links){
            links[l].onclick();
        }
    }

    function reset(){
        rolesCache = {};

        u.anchors.hide('aUsageStatusManager');
        u.anchors.hide('aCloudUsers');
        u.anchors.hide('aLocalUsers');

        cfg.form.reset();

        elements['common-name'].onkeyup();
        elements['common-name'].onblur();

        resetMainActivity();

        elements['common-ownership-form'].onchange();
        elements['common-civil-legal-status'].onchange();

        elements['common-bank-bic'].onkeyup();
        elements['common-bank-bic'].onblur();

        autoHeightTextArea(elements['common-juristic-location']);
        autoHeightTextArea(elements['common-physical-location']);

        elements['#chkbxCommonPhysicalAddressSameAsCommonJuristicAddress'].checked = false;
        elements['#chkbxCommonPhysicalAddressSameAsCommonJuristicAddress'].onclick();

        setReadOnlyByMap(chiefMap, false);

        updateUsageModelAnchorsFn();
        purgeRepresentatives();
    }

    function finalizeAddress(address){
        var tmp        = [],
            settlement = address.settlement,
            district   = settlement.district,
            region     = settlement.region || (district && district.region);

        if(region){
            tmp.push(region.name);
        }

        if(district){
            tmp.push(district.name);
        }

        tmp.push(settlement.name);

        return {
            id: settlement.id,
            text: tmp.join(', ')
        };
    }

    function fillAddress(map, address){
        var finalized = finalizeAddress(address);

        elements[map.postCode].value   = address.postCode;
        elements[map.location].value   = finalized.text;
        elements[map.settlement].value = finalized.id;
        elements[map.street].value     = address.street;
        elements[map.building].value   = address.building;
        elements[map.apartment].value  = address.apartment;

        autoHeightTextArea(elements[map.location]);
    }

    function fillName(name){
        elements['common-name'].value = name;

        elements['common-name'].onkeyup();
        elements['common-name'].onblur();
    }

    function fillFullName(name){
        elements['common-full-name'].value = name;

        elements['common-full-name'].onkeyup();
        elements['common-full-name'].onblur();
    }

    function fillBankBicAndAccount(bic, account){
        elements['common-bank-bic'].value = bic;

        elements['common-bank-bic'].onkeyup();
        elements['common-bank-bic'].onblur();

        elements['common-bank-account'].value = account;
    }

    function fillMainActivity(activity){
        var fldGked  = elements['#txtCommonMainActivityGked'],
            fldText  = elements['#txtarCommonMainActivityText'],
            fldValue = elements['common-main-activity'];

        fldGked.value  = activity.gked;
        fldText.value  = activity.name;
        fldValue.value = activity.id;

        autoHeightTextArea(fldText);
    }

    function setReadOnlyByMap(map, is){
        var property, field,
            fields, fLen, f;

        for(property in map){
            field = map[property];

            if($.isArray(field)){
                for(fields = field, f = 0, fLen = fields.length; field = fields[f], f < fLen; f++){
                    elements[field].readOnly = is;
                }
            } else {
                elements[field].readOnly = is;
            }
        }
    }

    function fillRepresentativeByMap(map, representative, protect){
        var person          = representative.person,
            passport        = person.passport,
            dRoles          = representative.roles,
            mRoles          = map.roles,
            dEdsUsageModel  = representative.edsUsageModel,
            mEdsUsageModels = map.edsUsageModels,
            drLen           = (dRoles && dRoles.length) || 0,
            mrLen           = mRoles.length,
            meumLen         = mEdsUsageModels.length,
            dR, Role,
            mR, mRole,
            mEum, eumEl;

        elements[map.passportSeries].value = passport['series'];
        elements[map.passportNumber].value = passport['number'];

        elements[map.passportIssuingAuthority].value = passport.issuingAuthority;
        elements[map.passportIssingDate].value       = passport.issuingDate;

        elements[map.surname].value    = person.surname;
        elements[map.name].value       = person.name;
        elements[map.middleName].value = person.middleName;

        if(representative.position){
            selectOptionByValue(
                elements[map.position],
                representative.position.id
            );
        }

        if(representative.phone){
            elements[map.workPhone].value = representative.phone;
        }

        if(representative.deviceSerial){
            elements[map.deviceSerial].value = representative.deviceSerial;
        }

        if(dRoles){
            for(dR = 0; dRole = dRoles[dR], dR < drLen; dR++){
                for(mR = 0; mRole = mRoles[mR], mR < mrLen; mR++){
                    if(elements[mRole].value == dRole.id){
                        elements[mRole].checked = true;

                        cacheRole(dRole.id, true);
                    }
                }
            }
        }

        if(dEdsUsageModel){
            for(mEum = 0; eumEl = mEdsUsageModels[mEum], mEum < meumLen; mEum++){
                if(elements[eumEl].value == dEdsUsageModel.id){
                    elements[eumEl].checked = true;
                    elements[eumEl].onchange();
                }
            }
        }

        setDisabled(mEdsUsageModels, !isEdsRequiredByMapFn(map));

        map = $.extend({}, map);

        delete map.passportSeries;
        delete map.passportNumber;
        delete map.workPhone;
        delete map.edsUsageModels;
        delete map.roles;
        delete map.deviceSerial;

        setReadOnlyByMap(map, protect);
    }

    function getChiefRepresentative(representatives){
        var rpLen = representatives.length,
            rp, rep,
            roles,
            rlLen,
            rl, role;

        for(rp = 0; rep = representatives[rp], rp < rpLen; rp++){
            roles = rep.roles;
            rlLen = roles.length;

            for(rl = 0; role = roles[rl], rl < rlLen; rl++){
                if(role.id == 1){
                    return rep;
                }
            }
        }

        return null;
    }

    function fillByUser(user){
        user = user || {};

        if(user.common){
            if(user.common.inn){
                elements['common-inn'].value = user.common.inn;

                elements['common-inn'].onblur();
            }

            if(user.common.okpo){
                elements['common-okpo'].value = user.common.okpo;
            }

            if(user.common.rnsf){
                elements['common-rnsf'].value = user.common.rnsf;
            }

            if(user.common.name){
                fillName(user.common.name);
            }

            if(user.common.fullName){
                fillFullName(user.common.fullName);
            }

            if(user.common.rnmj){
                elements['common-rnmj'].value = user.common.rnmj;
            }

            if(user.common.mainActivity){
                fillMainActivity(user.common.mainActivity);
            }

            var legalForm = user.common.legalForm;

            if(legalForm){
                if(legalForm.ownershipForm){
                    selectOptionByValue(
                        elements['common-ownership-form'],
                        legalForm.ownershipForm.id
                    );

                    elements['common-ownership-form'].onchange();
                }

                selectOptionByValue(
                    elements['common-legal-form'],
                    legalForm.id
                );
            }

            if(user.common.civilLegalStatus){
                selectOptionByValue(
                    elements['common-civil-legal-status'],
                    user.common.civilLegalStatus.id
                );

                elements['common-civil-legal-status'].onchange();
            }

            if(user.common.capitalForm){
                selectOptionByValue(
                    elements['common-capital-form'],
                    user.common.capitalForm.id
                );
            }

            if(user.common.managementForm){
                selectOptionByValue(
                    elements['common-management-form'],
                    user.common.managementForm.id
                );
            }

            if(user.common.bank){
                fillBankBicAndAccount(user.common.bank.id, user.common.bankAccount);
            }

            if(user.common.eMail){
                elements['common-email'].value = user.common.eMail;
            }

            if(user.common.juristicAddress){
                fillAddress(juristicAddressMap, user.common.juristicAddress);

                if(user.common.physicalAddress){
                    var isSameAddresses = u.object.isEqual(
                        user.common.juristicAddress,
                        user.common.physicalAddress
                    );

                    elements['#chkbxCommonPhysicalAddressSameAsCommonJuristicAddress'].checked
                        = isSameAddresses;

                    elements['#chkbxCommonPhysicalAddressSameAsCommonJuristicAddress'].onclick();

                    if(!isSameAddresses){
                        fillAddress(physicalAddressMap, user.common.physicalAddress);
                    }
                }
            }

            if(user.common.chiefBasis){
                selectOptionByValue(
                    elements['common-chief-basis'],
                    user.common.chiefBasis.id
                );
            }

            if(user.common.representatives){
                var reps  = user.common.representatives,
                    chief = getChiefRepresentative(reps),
                    rLen  = reps.length,
                    r, rep,
                    map;

                for(r = 0; rep = reps[r], r < rLen; r++){
                    if(rep == chief){
                        map = chiefMap;
                    } else {
                        elements['#btnAddRepresentative'].onclick();

                        map = cfg.representatives.maps[u.sequence.get('rpsn')];
                    }

                    fillRepresentativeByMap(map, rep, true);
                }

                applyRolesRemainsFn();
            }
        }

        if(user.sf){
            if(user.sf.tariff){
                selectOptionByValue(elements['sf-tariff'], user.sf.tariff.id);
            }

            if(user.sf.region){
                selectOptionByValue(elements['sf-region'], user.sf.region.id);
            }
        }

        if(user.sti){
            if(user.sti.regionDefault){
                selectOptionByValue(
                    elements['sti-region-default'],
                    user.sti.regionDefault.id
                );
            }

            if(user.sti.regionReceive){
                selectOptionByValue(
                    elements['sti-region-receiver'],
                    user.sti.regionReceive.id
                );
            }
        }
    }

    var statementFiller = (function(){

        function fromMain(main){
            if(main.inn){
                elements['common-inn'].value = main.inn;

                elements['common-inn'].onblur();
            }

            if(main.okpo){
                elements['common-okpo'].value = main.okpo;
            }

            if(main.sf){
                elements['common-rnsf'].value = main.sf;
            }

            if(main.name){
                fillName(main.name);
                fillFullName(main.name);
            }

            if(main.minjust){
                elements['common-rnmj'].value = main.minjust;
            }

            if(main.gked){
                elements['#txtCommonMainActivityGked'].value = main.gked.id

                elements['#txtCommonMainActivityGked'].onblur();
            }

            if(main.ownerform){
                selectOptionByValue(elements['common-ownership-form'], main.ownerform);

                elements['common-ownership-form'].onchange();
            }

            if(main.legalform){
                selectOptionByValue(elements['common-legal-form'], main.legalform);
            }

            if(main.civilstatus){
                selectOptionByValue(elements['common-civil-legal-status'], main.civilstatus);

                elements['common-civil-legal-status'].onchange();
            }

            if(main.capitalform){
                selectOptionByValue(elements['common-capital-form'], main.capitalform);
            }

            if(main.manageform){
                selectOptionByValue(elements['common-management-form'], main.manageform);
            }
        }

        function fromAddress(map, address){
            elements[map.postCode].value  = address.post_index || '';
            elements[map.street].value    = address.street || '';
            elements[map.building].value  = address.building || '';
            elements[map.apartment].value = address.apartment || '';
        }

        function fromConctacts(contacts){
            elements['common-email'].value = contacts.email;

            var juristicAddress = contacts.juristic_address,
                physicalAddress = contacts.real_address,
                isSameAddresses = u.object.isEqual(juristicAddress, physicalAddress);

            fromAddress(juristicAddressMap, juristicAddress);

            elements['#chkbxCommonPhysicalAddressSameAsCommonJuristicAddress'].checked
                = isSameAddresses;

            elements['#chkbxCommonPhysicalAddressSameAsCommonJuristicAddress'].onclick();

            fromAddress(physicalAddressMap, physicalAddress);
        }

        function toRepresentative(personObj){
            var passport = {
                    series: personObj.passport_data.series,
                    number: personObj.passport_data.number,
                    issuingAuthority: personObj.passport_data.issue_place,
                    issuingDate: personObj.passport_data.issue_date
                },
                person = {
                    passport: passport,
                    surname: personObj.surname,
                    name: personObj.name,
                    middleName: personObj.fathername
                },
                representative = {
                    person: person,
                    phone: personObj.phone.replace(/[^\d]+/, ''),
                    position: {
                        id: personObj.position
                    },
                    roles: [],
                    edsUsageModel: {
                        id: consts.EDS_USAGE_MODEL_CLOUD
                    },
                    deviceSerial: null,
                };

            return representative;
        }

        function fromPerson(person){
            var reps              = [],
                chief             = person.chief,
                accountant        = person.accountant,
                chiefIsAccountant = (
                    chief
                    &&
                    accountant
                    &&
                    u.object.isEqual(chief.passport_data, accountant.passport_data)
                );

            if(chief){
                selectOptionByValue(elements['common-chief-basis'], chief.basis);

                chief = toRepresentative(chief);

                chief.roles.push({
                    id: consts.ROLES_CHIEF
                });

                reps.push(chief);
            }

            if(chiefIsAccountant){
                chief.roles.push({
                    id: consts.ROLES_ACCOUNTANT
                });
            } else if(accountant){
                if(!accountant.position){
                    accountant.position = consts.POSITIONS_ACCOUNTANT;
                }

                accountant = toRepresentative(accountant);

                accountant.roles.push({
                    id: consts.ROLES_ACCOUNTANT
                });

                reps.push(accountant);
            }

            var rLen = reps.length,
                r, rep,
                map;

            if(rLen){
                reps[rLen - 1].roles.push({
                    id: consts.ROLES_EDS_USER
                }, {
                    id: consts.ROLES_EDS_RECEIVER
                });
            }

            for(r = 0; rep = reps[r], r < rLen; r++){
                if(r == 0){
                    map = chiefMap;
                } else {
                    elements['#btnAddRepresentative'].onclick();

                    map = cfg.representatives.maps[u.sequence.get('rpsn')];
                }

                fillRepresentativeByMap(map, rep, false);
            }

            applyRolesRemainsFn();
        }

        function fromReporting(reporting){
            if(reporting.sftariff){
                selectOptionByValue(elements['sf-tariff'], reporting.sftariff);
            }

            if(reporting.sfregion){
                selectOptionByValue(elements['sf-region'], reporting.sfregion);
            }

            if(reporting.stiregion){
                selectOptionByValue(elements['sti-region-default'], reporting.stiregion);
            }

            if(reporting.stiapplyingregion){
                selectOptionByValue(
                    elements['sti-region-receiver'],
                    reporting.stiapplyingregion
                );
            }
        }

        function fill(statement){
            var data = statement.data;

            if(data.main){
                fromMain(data.main);
            }

            if(data.bank){
                fillBankBicAndAccount(data.bank.bic, data.bank.account);
            }

            if(data.contacts){
                fromConctacts(data.contacts);
            }

            if(data.person){
                fromPerson(data.person);
            }

            if(data.reporting){
                fromReporting(data.reporting);
            }
        }

        return {
            fromMain: fromMain,
            fromConctacts: fromConctacts,
            fromAddress: fromAddress,
            fromPerson: fromPerson,
            fill: fill
        };
    })();

    function setUsageStatus(data){
        var anchor      = u.anchors.get('aUsageStatusManager'),
            usageStatus = data && data.usageStatus;

        anchor.classList.remove('green', 'grey');

        if(usageStatus){
            if(usageStatus.isActive){
                anchor.classList.add('green');

                anchor.innerHTML = 'Состояние:<br>обслуживается';
            } else {
                anchor.classList.add('grey');

                anchor.innerHTML = 'Состояние:<br>не обслуживается';
            }
        } else {
            anchor.innerHTML = 'Состояние:<br>не назначено';
        }
    }

    var btnSearchClickHandler = (function(){
        var ajaxNotFoundTpl = 'Данных по ИНН <i><b>{0}</b></i> не найдено.',
            ajaxCommonErrorTpl = [
                '<p class="caption"><b>Произошла ошибка:</b></p>',
                '<pre class="error">{0} ({1})</pre>'
            ].join(''),
            data = {},
            ajaxSuccessHandlerFn = function(response){
                if(response.success){
                    if(response.data){
                        u.data = response.data;

                        switch(modes.getMode()){
                            default:
                            case consts.MODE_REQUISITES:
                                elements['uid'].value = u.data.uid;

                                reset();

                                setUsageStatus(u.data);

                                fillByUser(u.data);

                                u.anchors.show('aUsageStatusManager');

                                u.dialog.hide();
                            break;

                            case consts.MODE_STATEMENTS_FOR_REVISION:
                            case consts.MODE_STATEMENTS_PAID:
                            case consts.MODE_STATEMENTS_REJECTED:
                                elements['uid'].value = '';

                                reset();

                                statementFiller.fill(u.data);

                                u.dialog.hide();
                            break;
                        }

                    } else {

                        // IF NOT FIND

                        var inn = elements['common-inn'].value;

                        console.log(inn);


                        delete u.data;

                        elements['uid'].value = '';


                        reset();


                        elements['common-inn'].value = inn;

                        $.ajax({
                            url: 'services.php',
                            data: {
                                service: "Nwa",
                                inn: $('[name="common-inn"]').val()
                            },
                            success: function (response) {
                                u.dialog.showMessage(
                                    u.string.format(
                                        ajaxNotFoundTpl,
                                        $('[name="common-inn"]').val()
                                    )
                                );

                                $('[name="common-okpo"]').val(response.data.okpo);
                                $('[name="common-rnsf"]').val(response.data.rnsf);
                                $('[name="common-full-name"]').val(response.data.name);
                                $('[name="common-full-name"]').keyup();
                                $('[name="common-rnmj"]').val(response.data.mj);


                                console.log('success');
                                //u.dialog.hide();
                            },
                            error: function (response) {
                                u.dialog.showMessage(
                                    u.string.format(
                                        ajaxNotFoundTpl,
                                        $('[name="common-inn"]').val()
                                    )
                                );
                                //u.dialog.hide();
                            },
                            complete: function (response) {
                                console.log(response);
                            }
                        });
                    }
                } else {


                    u.dialog.showError(
                        u.string.format(
                            ajaxCommonErrorTpl,
                            u.string.escapeHTML(response['error-message']),
                            u.string.escapeHTML(response['error-code'])
                        )
                    );
                }
            },
            ajaxErrorHandlerFn = function(response){

                if(response.status != 200){
                    u.dialog.showError(
                        u.string.format(
                            ajaxCommonErrorTpl,
                            u.string.escapeHTML(response.statusText),
                            u.string.escapeHTML(response.status)
                        )
                    );
                } else {
                    u.dialog.showError('Ошибка при обработке ответа.');
                }
            },
            ajaxCompleteHandlerFn = function(response){
                for(var property in data){
                    delete data[property];
                }
            },
            config = {
                url: 'services.php',
                data: data,
                success: ajaxSuccessHandlerFn,
                error: ajaxErrorHandlerFn,
                complete: ajaxCompleteHandlerFn
            };

        return function(){
            var inn  = elements['common-inn'].value,
                mode = modes.getMode();

            if(!inn.length){
                return;
            }

            u.dialog.showLoading();

            switch(mode){
                case consts.MODE_REQUISITES:
                    data.service = 'Users';

                    data.inn = inn;
                break;

                case consts.MODE_STATEMENTS_FOR_REVISION:
                case consts.MODE_STATEMENTS_PAID:
                case consts.MODE_STATEMENTS_REJECTED:
                    data.service = 'Statements';

                    data.inn  = inn;
                    data.mode = mode;
                break;
            }

            $.ajax(config);
        };
    })();

    var btnResetClickHandler = function(){
        reset();

        elements['uid'].value = '';

        delete u.data;
    };

    var reSpaces    = /\s+/g,
        reDigits    = /\d+/g,
        reNotDigits = /[^\d]/g;

    function normalizeSpaces(value){
        return value.replace(reSpaces, ' ');
    }

    function filterChars(value){
        return value.replace(reDigits, '');
    }

    function filterDigits(value){
        return value.replace(reNotDigits, '');
    }

    function noSpaces(value){
        return $.trim(value.replace(reSpaces, ''));
    }

    function pad(str, chr, max){
        str = String(str);

        return str.length < max ? pad(chr + str, max) : str;
    }

    function filterDate(value){
        var glue  = '.',
            chr   = '0',
            parts = value.split(glue),
            date, cmp;

        if(parts.length < 3){
            return '';
        }

        date  = new Date(parts[2], parts[1] - 1, parts[0]);

        date  = [
            pad(date.getDate(), chr, 2),
            pad(date.getMonth() + 1, chr, 2),
            pad(date.getFullYear(), chr, 4)
        ].join(glue);

        value = [
            pad(parts[0], chr, 2),
            pad(parts[1], chr, 2),
            pad(parts[2], chr, 4)
        ].join(glue);

        return date === value ? value : '';
    }

    var digitFieldBlurHandler = function(){
            this.value = filterDigits(this.value);
        },
        charFieldBlurHandler = function(){
            this.value = filterChars(this.value);
        },
        dateFieldBlurHandler = function(){
            this.value = filterDate(this.value);
        },
        nospaceFieldBlurHandler = function(){
            this.value = noSpaces(this.value);
        };

    var monospaceFieldBlurHandler = function(){
        this.value = $.trim(normalizeSpaces(this.value));
    };

    var txtCommonInnBlurHandler = function(){
            digitFieldBlurHandler.call(this);

            var inn   = this.value,
                type  = null,
                field = elements['common-civil-legal-status'];

            if(inn.length){
                switch(parseInt(inn[0])){
                    case 1:
                    case 2:
                        type = 2;
                    break;

                    case 0:
                    case 4:
                        type = 1;
                    break;
                }

                if(type === null){
                    var options = field.options;

                    if(options.length){
                        options[0].selected = true;
                    }
                } else {
                    selectOptionByValue(field, type);
                }

                field.onchange();
            }
        },
        txtCommonInnKeyDownHandler = function(e){
            e = fixEvent(e);

            var key = event.which || event.keyCode;

            if(key == consts.VK_ENTER){
                elements['#btnSearch'].onclick();

                skipEvent(e);
            }
        };

    var txtarCommonNameKeyUpHandler = function(){
            autoHeightTextArea(this);
        },
        txtarCommonNameBlurHandler = function(){
            monospaceFieldBlurHandler.call(this);

            autoHeightTextArea(this);
        };

    var txtCommonRnmjBlurHandler = (function(){
        var reRnmj = /^\d+\-\d+\-.+$/;

        return function(){
            nospaceFieldBlurHandler.call(this);

            var v = this.value.toUpperCase();

            this.value = reRnmj.test(v) ? v : '';
        };
    })();

    var txtDeviceSerialBlurHandler = (function(){
        var reDeviceSerial = /^\d{10,10}$/;

        return function(){
            digitFieldBlurHandler.call(this);

            var v = this.value;

            v = reDeviceSerial.test(v) ? v : '';

            this.value = v;
        };
    })();

    var sltCommonOwnershipFormChangeHandler = (function(){
        var renderFn = function(o){
            var html = 'id' in o
                ? o.facet === null
                    ? u.string.format(
                        defaultOptionTpl,
                        o.id,
                        (o.shortName ? o.shortName + ' - ' : '') + o.name
                    )
                    : u.string.format(
                        facetOptionTpl,
                        o.id,
                        (o.shortName ? o.shortName + ' - ' : '') + o.name,
                        o.facet
                    )
                : u.string.format(emptyOptionTpl, o.name);

            return html;
        };

        return function(){
            var field  = elements['common-legal-form'],
                value  = this.options[this.selectedIndex].value,
                grepFn = function(r){
                    return r.ownershipForm == value;
                },
                records = $.grep(cfg.dictionaries['common-legal-form'], grepFn);

            if(records.length){
                field.disabled = false;

                pasteOptionsToListElement(
                    field,
                    [ defaultOption ].concat(records),
                    renderFn
                );

                if(records.length == 1){
                    selectOptionByValue(field, records[0].id);
                }
            } else {
                field.disabled  = true;
                field.innerHTML = '';
            }
        };
    })();

    var sltCommonCivilLegalStatusChangeHandler = function(){
        var option    = this.options[this.selectedIndex],
            doDisable = option.value == consts.CIVIL_LEGAL_STATUS_PHYSICAL;

        elements['common-rnmj'].disabled            = doDisable;
        elements['common-capital-form'].disabled    = doDisable;
        elements['common-management-form'].disabled = doDisable;
    };

    var txtCommonBankBicKeyUpHandler = (function(){

        function cleanUp(el1, el2){
            el1.value = '';
            el2.value = '';

            el2.disabled = true;
        }

        return function(){
            var bic            = this.value,
                fldBankName    = elements['#txtCommonBankName'],
                fldBankAccount = elements['common-bank-account'];

            if(bic.length < 6){
                return cleanUp(fldBankName, fldBankAccount);
            }

            var grepFn = function(record){
                    return record.id == bic;
                },
                banks  = $.grep(cfg.dictionaries['common-bank'], grepFn);

            if(banks.length){
                fldBankName.value       = banks[0].name;
                fldBankAccount.disabled = false;
                fldBankAccount.value    = bic.substr(0, 3);
            } else {
                cleanUp(fldBankName, fldBankAccount);
            }
        };
    })();

    var txtCommonBankBicBlurHandler = function(){
        if(elements['common-bank-account'].disabled){
            this.value = '';
        }
    };

    var chkbxIsCommonPhysicalAddressSameAsCommonJuristicAddressClickHandler = function(){
        var is = this.checked;

        elements['common-physical-post-code'].disabled  = is;
        elements['common-physical-location'].disabled   = is;
        elements['common-physical-settlement'].disabled = is;
        elements['common-physical-street'].disabled     = is;
        elements['common-physical-building'].disabled   = is;
        elements['common-physical-apartment'].disabled  = is;
    };

    var txtCommonRepresentativePassportSeriesFieldBlurHandler = function(){
        this.value = noSpaces(this.value).toUpperCase();
    };

    var representativeSearcherFn = (function(){
        var ajaxCommonErrorTpl = [
                '<p class="caption"><b>Произошла ошибка:</b></p>',
                '<pre class="error">{0} ({1})</pre>'
            ].join(''),
            ajaxNoDataMsgTpl = [
                'Представителей с паспортными данными',
                ' <i><b>{0}</b>№<b>{1}</b></i>',
                ' не найдено.'
            ].join(''),
            data = {
                service: 'Representatives',
            },
            config = {
                url: 'services.php',
                data: data
            },
            ajaxSuccessHandlerFn = function(response, map, data){
                if(response.success){
                    var isData = response.result !== null,
                        rep;

                    map = $.extend({}, map);

                    if(isData){
                        rep = {
                            person: response.result
                        };

                        fillRepresentativeByMap(map, rep, true);

                        delete map.passportSeries;
                        delete map.passportNumber;
                        delete map.roles;

                        u.dialog.hide();
                    } else {
                        delete map.passportSeries;
                        delete map.passportNumber;
                        delete map.roles;

                        resetFields(map);

                        u.dialog.showMessage(
                            u.string.format(
                                ajaxNoDataMsgTpl,
                                data.series,
                                data.number
                            )
                        );
                    }

                    applyRolesRemainsFn();

                    delete map.workPhone;
                    delete map.edsUsageModels;
                    delete map.deviceSerial;

                    setReadOnlyByMap(map, isData);
                } else {
                    u.dialog.showError(
                        u.string.format(
                            ajaxCommonErrorTpl,
                            u.string.escapeHTML(response['error-message']),
                            u.string.escapeHTML(response['error-code'])
                        )
                    );
                }
            },
            ajaxErrorHandlerFn = function(response){
                if(response.status != 200){
                    u.dialog.showError(
                        u.string.format(
                            ajaxCommonErrorTpl,
                            u.string.escapeHTML(response.statusText),
                            u.string.escapeHTML(response.status)
                        )
                    );
                } else {
                    u.dialog.showError('Ошибка при обработке ответа.');
                }
            },
            ajaxCompleteHandlerFn = function(response){
                delete data.series;
                delete data.number;

                delete config.success;
            };

        config.error    = ajaxErrorHandlerFn;
        config.complete = ajaxCompleteHandlerFn;

        return function(map){
            var series = elements[map.passportSeries].value,
                number = elements[map.passportNumber].value;

            if(series && number){
                data.series = series;
                data.number = number;

                config.success = function(response){
                    ajaxSuccessHandlerFn(response, map, data);
                };

                u.dialog.showLoading();

                $.ajax(config);
            }
        };
    })();

    var btnCommonChiefSearchClickHandler = function(){
        representativeSearcherFn(chiefMap);
    };

    var btnAddCommonRepresentativeClickHandler = (function(){
        var tpl = [
            '<table class="requisites">',
                '<caption>Сотрудник ( <a href="javascript: void(0)" id="{0}">Удалить</a> )</caption>',
                '<tbody>',
                    '<tr>',
                        '<td class="section-name" colspan="2">Паспортные данные</td>',
                    '</tr>',
                    '<tr>',
                        '<th>Серия и номер</th>',
                        '<td>',
                            '<div style="display: table; border-spacing: 0px; max-width: 305px;">',
                                '<div style="display: table-cell;">',
                                    '<input maxlength="10" required type="text" name="common-representative-passport-series-{2}" style="width: 110px" placeholder="Серия">',
                                '</div>',
                                '<div style="display: table-cell; padding: 3px">№</div>',
                                '<div style="display: table-cell; width: 100%;">',
                                    '<input maxlength="15" required type="text" name="common-representative-passport-number-{2}" style="width: 100%" placeholder="Номер">',
                                '</div>',
                            '</div>',
                        '</td>',
                    '</tr>',
                    '<tr>',
                        '<td colspan="2" class="centered">',
                            '<input type="button" class="button" id="{1}" value="Поиск существующих данных">',
                        '</td>',
                    '</tr>',
                    '<tr>',
                        '<th>Выдавший орган</th>',
                        '<td><input required placeholder="Наименование выдавшего органа" type="text" name="common-representative-passport-issuing-authority-{2}"></td>',
                    '</tr>',
                    '<tr>',
                        '<th>Дата выдачи</th>',
                        '<td><input required placeholder="ДД.ММ.ГГГГ" type="text" name="common-representative-passport-issuing-date-{2}"></td>',
                    '</tr>',
                    '<tr>',
                        '<td class="section-name" colspan="2">Основные сведения</td>',
                    '</tr>',
                    '<tr>',
                        '<th>Фамилия</th>',
                        '<td><input required type="text" placeholder="Фамилия" name="common-representative-surname-{2}"></td>',
                    '</tr>',
                    '<tr>',
                        '<th>Имя</th>',
                        '<td><input required type="text" placeholder="Имя" name="common-representative-name-{2}"></td>',
                    '</tr>',
                    '<tr>',
                        '<th>Отчество</th>',
                        '<td><input type="text" placeholder="Отчество" name="common-representative-middle-name-{2}"></td>',
                    '</tr>',
                    '<tr>',
                        '<th>Должность</th>',
                        '<td><select required name="common-representative-position-{2}"></select></td>',
                    '</tr>',
                    '<tr>',
                        '<th>Рабочий телефон</th>',
                        '<td><input required type="text" placeholder="Рабочий телефон" name="common-representative-work-phone-{2}"></td>',
                    '</tr>',
                '</tbody>',
            '</table>'
        ].join('');

        var createFieldMapFn = function(idx){
                var map = {
                    passportSeries: 'common-representative-passport-series-' + idx,
                    passportNumber: 'common-representative-passport-number-' + idx,

                    passportIssuingAuthority: 'common-representative-passport-issuing-authority-' + idx,
                    passportIssingDate:       'common-representative-passport-issuing-date-' + idx,

                    position:   'common-representative-position-' + idx,
                    surname:    'common-representative-surname-' + idx,
                    name:       'common-representative-name-' + idx,
                    middleName: 'common-representative-middle-name-' + idx,

                    workPhone: 'common-representative-work-phone-' + idx,

                    roles: [],

                    edsUsageModels: []
                };

                return map;
            },
            uncacheRoles = function(roles){
                var rLen = roles.length,
                    r, role;

                for(r = 0; role = roles[r], r < rLen; r++){
                    if(role){
                        cacheRole(role, false);
                    }
                }
            },
            createConfigFn = (function(){
                var completeTpl = [
                    '<tr>',
                        '<th>Серийный номер устройства (<a href = "javascript:insertSerial(\'common-representative-device-serial-{0}\')">Прочитать</a>)</th>',
                        '<td><input required disabled type="text" maxlength="10" placeholder="Серийный номер" name="common-representative-device-serial-{0}"></td>',
                    '</tr>'
                ].join('');

                return function(idx){
                    var config = {
                        'common-representative-role': {
                            render: {
                                id: function(value){
                                    return 'chkbxRepresentativeRole_' + value + '_' + idx;
                                },
                                name: function(value){
                                    return 'common-representative-roles-' + idx + '[]';
                                }
                            }
                        },
                        'common-eds-usage-model': {
                            render: {
                                id: function(value){
                                    return 'rdoRepresentativeEdsUsageModel_' + value + '_' + idx;
                                },
                                name: 'common-representative-eds-usage-model-' + idx
                            },
                            callback: function(map, el){
                                var dom = $.parseHTML(u.string.format(completeTpl, idx))[0];

                                map.deviceSerial = dom.getElementsByTagName('input')[0].name;

                                el.parentNode.appendChild(dom);
                            }
                        }
                    };

                    return config;
                };
            })(),
            lnkClickHandlerFn = function(){
                var links = cfg.representatives.links,
                    maps  = cfg.representatives.maps,
                    dom   = cfg.representatives.dom,
                    idx   = parseInt(this.id.split('_').pop()),
                    table = dom[idx];

                uncacheRoles(getValues(maps[idx].roles));

                delete links[idx];
                delete maps[idx];
                delete dom[idx];

                cfg.form.removeChild(table);

                applyRolesRemainsFn();
                updateUsageModelAnchorsFn();
            },
            btnClickHandlerFn = function(){
                var idx = parseInt(this.id.split('_').pop()),
                    map = cfg.representatives.maps[idx];

                representativeSearcherFn(map);
            };

        return function(){
            var form   = cfg.form,
                idx    = u.sequence.next('rpsn'),
                fldMap = createFieldMapFn(idx),
                config = createConfigFn(idx),
                lnkId  = 'btnRemoveRepresentative_' + idx,
                btnId  = 'btnSearchRepresentative_' + idx,
                dom    = $.parseHTML(u.string.format(tpl, lnkId, btnId, idx))[0],
                target = dom.childNodes[1].lastChild,
                lnk, btn;

            pasteRepresentativeDictionariesFn(fldMap, target, config);

            form.insertBefore(dom, this);

            cacheElements();

            pasteOptionsToListElement(
                elements[fldMap.position],
                [ defaultOption ].concat(cfg.dictionaries['common-representative-position']),
                optionRenderDefaultFn
            );

            lnk = d.getElementById(lnkId);
            btn = elements['#' + btnId];

            cfg.representatives.links[idx] = lnk;
            cfg.representatives.maps[idx]  = fldMap;
            cfg.representatives.dom[idx]   = dom;

            applyRolesRemainsFn();

            lnk.onclick = lnkClickHandlerFn;
            btn.onclick = btnClickHandlerFn;

            elements[fldMap.passportSeries].onblur
                = txtCommonRepresentativePassportSeriesFieldBlurHandler;

            elements[fldMap.passportNumber].onblur
                = nospaceFieldBlurHandler;

            elements[fldMap.passportIssuingAuthority].onblur = monospaceFieldBlurHandler;
            elements[fldMap.passportIssingDate].onblur       = dateFieldBlurHandler;

            elements[fldMap.surname].onblur    = monospaceFieldBlurHandler;
            elements[fldMap.name].onblur       = monospaceFieldBlurHandler;
            elements[fldMap.middleName].onblur = monospaceFieldBlurHandler;
            elements[fldMap.workPhone].onblur  = monospaceFieldBlurHandler;

            elements[fldMap.deviceSerial].onblur = txtDeviceSerialBlurHandler;
        };
    })();

    var commonAddressHandlers = (function(){

        function createListBox(records){
            var listBox = d.createElement('select');

            listBox.size         = 2;
            listBox.style.height = '250px';
            listBox.style.width  = '100%';

            pasteOptionsToListElement(listBox, records, optionRenderDefaultFn);

            return listBox;
        }

        function activateListBox(listBox, defaultValue){
            selectOptionByValue(listBox, defaultValue);

            listBox.ondblclick = u.wizard.elements.buttons.next.onclick;

            listBox.focus();
        }

        function listBoxSelectedOptionToObject(listBox){
            var option = listBox.options[listBox.selectedIndex];

            return {
                id: option.value,
                name: option.innerHTML
            };
        }

        var ajaxCommonErrorTpl = [
                '<p class="caption"><b>Произошла ошибка:</b></p>',
                '<pre class="error">{0} ({1})</pre>'
            ].join(''),
            ajaxNoDataErrorTpl = [
                '<p class="caption"><b>Недостаточно данных:</b></p>',
                '<p>Для заданных параметров не присвоено ни одного населенного пункта.</p>'
            ].join(''),
            ajaxSuccessHandlerFn = function(response, body){
                if(response.success){
                    if(response['result-set'].length){
                        var records = response['result-set'],
                            listBox = createListBox(records);

                        this.tmp = listBox;

                        body.innerHTML = '<p class="caption"><b>Выберите значение:</b></p>';
                        body.appendChild(listBox);

                        activateListBox(listBox, records[0].id);

                        u.wizard.elements.buttons.next.disabled = false;
                    } else {
                        body.innerHTML = ajaxNoDataErrorTpl;
                    }
                } else {
                    body.innerHTML = u.string.format(
                        ajaxCommonErrorTpl,
                        u.string.escapeHTML(response['error-message']),
                        u.string.escapeHTML(response['error-code'])
                    );
                }
            },
            ajaxErrorHandlerFn = function(response, body){
                if(response.status != 200){
                    body.innerHTML = u.string.format(
                        ajaxCommonErrorTpl,
                        u.string.escapeHTML(response.statusText),
                        u.string.escapeHTML(response.status)
                    );
                } else {
                    body.innerHTML = 'Ошибка при обработке ответа.';
                }
            };

        var wzdSettlement = {
            context: null,
            steps: [
                {
                    render: function(body){
                        var records = [
                                {
                                    id: 'none',
                                    name: '-- Республиканского подчинения --'.toUpperCase()
                                }
                            ].concat(
                                cfg.dictionaries['common-region']
                            ),
                            listBox = createListBox(records);

                        this.tmp = listBox;

                        body.innerHTML = '<p class="caption"><b>Выберите значение:</b></p>';
                        body.appendChild(listBox);
                    },
                    onStart: function(){
                        activateListBox(
                            this.tmp,
                            this.region ? this.region.id : 'none'
                        );
                    },
                    onCancel: function(){
                        delete this.tmp;
                        delete this.region;
                        delete this.district;
                    },
                    onComplete: function(){
                        var region = listBoxSelectedOptionToObject(this.tmp);

                        if(region.id == 'none'){
                            this.region = this.district = null;
                        } else {
                            delete this.district;

                            this.region = region;
                        }

                        delete this.tmp;
                    },
                    next: function(){
                        return this.region === null ? 2 : 1;
                    }
                }, {
                    render: function(body){
                        var region  = this.region.id,
                            grepFn  = function(o){
                                return o.region == region;
                            },
                            records = [
                                {
                                    id: 'none',
                                    name: '-- Областного подчинения --'.toUpperCase()
                                }
                            ].concat(
                                $.grep(cfg.dictionaries['common-district'], grepFn)
                            ),
                            listBox = createListBox(records);

                        this.tmp = listBox;

                        body.innerHTML = '<p class="caption"><b>Выберите значение:</b></p>';
                        body.appendChild(listBox);
                    },
                    onStart: function(){
                        activateListBox(
                            this.tmp,
                            this.district ? this.district.id : 'none'
                        );
                    },
                    onCancel: function(){
                        delete this.tmp;
                        delete this.district;
                    },
                    onComplete: function(){
                        var district = listBoxSelectedOptionToObject(this.tmp);

                        if(district.id == 'none'){
                            this.district = null;
                        } else {
                            district.region = this.region;

                            this.district   = district;
                            this.region     = null;
                        }

                        delete this.tmp;
                    },
                    next: 2,
                    prev: 0
                }, {
                    render: function(body){
                        var context = this,
                            data    = {
                                service:  'Settlements',
                                region:   this.region ? this.region.id : null,
                                district: this.district ? this.district.id : null
                            },
                            config  = {
                                url: 'services.php',
                                data: data,
                                success: function(response){
                                    ajaxSuccessHandlerFn.call(context, response, body);
                                },
                                error: function(response){
                                    ajaxErrorHandlerFn.call(context, response, body);
                                },
                                complete: function(response){
                                    u.wizard.elements.buttons.prev.disabled = false;
                                }
                            };

                        u.wizard.elements.buttons.prev.disabled = true;
                        u.wizard.elements.buttons.next.disabled = true;

                        body.innerHTML = '<i>Зарузка данных с сервера...</i>';

                        $.ajax(config);
                    },
                    onCancel: function(){
                        u.wizard.elements.buttons.next.disabled = false;

                        delete this.tmp;
                    },
                    onComplete: function(){
                        var settlement = listBoxSelectedOptionToObject(this.tmp);

                        settlement.region   = this.region;
                        settlement.district = this.district;

                        this.settlement = settlement;

                        delete this.tmp;

                        u.wizard.elements.buttons.next.disabled = false;
                    },
                    prev: function(){
                        return this.region === null ? 0 : 1;
                    }
                }
            ]
        };

        var wzdJuristicLocationFinishHandler = function(){
                var address = finalizeAddress(this);

                elements[juristicAddressMap.settlement].value = address.id;
                elements[juristicAddressMap.location].value   = address.text;

                autoHeightTextArea(elements[juristicAddressMap.location]);

                delete wzdSettlement.onFinish;
                delete wzdSettlement.context;
            },
            wzdPhysicalLocationFinishHandler = function(){
                var address = finalizeAddress(this);

                elements[physicalAddressMap.settlement].value = address.id;
                elements[physicalAddressMap.location].value   = address.text;

                autoHeightTextArea(elements[physicalAddressMap.location]);

                delete wzdSettlement.onFinish;
                delete wzdSettlement.context;
            };

        return {
            txtCommonJuristicLocationClickHandler: function(){
                wzdSettlement.onFinish = wzdJuristicLocationFinishHandler;

                u.wizard.run(wzdSettlement);
            },
            txtCommonPhysicalLocationClickHandler: function(){
                wzdSettlement.onFinish = wzdPhysicalLocationFinishHandler;

                u.wizard.run(wzdSettlement);
            }
        };
    })();

    var txtCommonMainActivityGkedBlurHandler = (function(){
        var ajaxCommonErrorTpl = [
                '<p class="caption"><b>Произошла ошибка:</b></p>',
                '<pre class="error">{0} ({1})</pre>'
            ].join(''),
            ajaxNotFoundTpl = 'Видов деятельности с ГКЭД <i><b>"{0}"</b></i> не найдено.',
            config = {
                url: 'services.php',
                type: 'GET'
            },
            data = {
                service: 'Activities',
                action: 'getByGked'
            },
            ajaxSuccessHandlerFn = function(response){
                if(response.success){
                    var activity = response.activity;

                    if(activity){
                        fillMainActivity(activity);

                        u.dialog.hide();
                    } else {
                        u.dialog.showMessage(
                            u.string.format(
                                ajaxNotFoundTpl,
                                data.gked
                            )
                        );

                        resetMainActivity();
                    }
                } else {
                    u.dialog.showError(
                        u.string.format(
                            ajaxCommonErrorTpl,
                            u.string.escapeHTML(response['error-message']),
                            u.string.escapeHTML(response['error-code'])
                        )
                    );
                }
            },
            ajaxErrorHandlerFn = function(response){
                if(response.status != 200){
                    u.dialog.showError(
                        u.string.format(
                            ajaxCommonErrorTpl,
                            u.string.escapeHTML(response.statusText),
                            u.string.escapeHTML(response.status)
                        )
                    );
                } else {
                    u.dialog.showError('Ошибка при обработке ответа.');
                }
            },
            ajaxCompleteHandlerFn = function(response){
                delete data.gked;
            };

        config.success  = ajaxSuccessHandlerFn;
        config.error    = ajaxErrorHandlerFn;
        config.complete = ajaxCompleteHandlerFn;

        config.data = data;

        return function(e){
            var gked = this.value;

            if(gked){
                u.dialog.showLoading();

                data.gked = gked;

                $.ajax(config);
            } else {
                resetMainActivity();
            }
        };
    })();

    var frmSubmitHandler = (function(){
        var ajaxCommonErrorTpl = [
                '<p class="caption"><b>Произошла ошибка:</b></p>',
                '<pre class="error">{0} ({1})</pre>'
            ].join(''),
            ajaxSuccessTpl = '<p class="caption"><i>Данные сохранены успешно.</i></p>',
            config = {
                url: 'index.php?action=submit',
                type: 'POST'
            },
            ajaxSuccessHandlerFn = function(response){
                if(response.success){
                    u.dialog.showMessage(ajaxSuccessTpl);

                    elements['uid'].value = response.uid;
                } else {
                    u.dialog.showError(
                        u.string.format(
                            ajaxCommonErrorTpl,
                            u.string.escapeHTML(response['error-message']),
                            u.string.escapeHTML(response['error-code'])
                        )
                    );
                }
            },
            ajaxErrorHandlerFn = function(response){
                if(response.status != 200){
                    u.dialog.showError(
                        u.string.format(
                            ajaxCommonErrorTpl,
                            u.string.escapeHTML(response.statusText),
                            u.string.escapeHTML(response.status)
                        )
                    );
                } else {
                    u.dialog.showError('Ошибка при обработке ответа.');
                }
            },
            ajaxCompleteHandlerFn = function(response){
                delete config.data;
            };

        config.success  = ajaxSuccessHandlerFn;
        config.error    = ajaxErrorHandlerFn;
        config.complete = ajaxCompleteHandlerFn;

        return function(e){
            switch(modes.getMode()){
                case consts.MODE_REQUISITES:
                case consts.MODE_STATEMENTS_PAID:
                case consts.MODE_STATEMENTS_REJECTED:
                    config.data = $(cfg.form).serialize();

                    u.dialog.showLoading();

                    $.ajax(config);
                break;

                default:
                case consts.MODE_STATEMENTS_FOR_REVISION:
                    // no action, skip;
                break;
            }

            return skipEvent(e);
        };
    })();

    function applyEventHandlers(){
        cfg.form.onsubmit = frmSubmitHandler;

        elements['common-inn'].onblur    = txtCommonInnBlurHandler;
        elements['common-inn'].onkeydown = txtCommonInnKeyDownHandler;

        elements['common-okpo'].onblur = nospaceFieldBlurHandler;
        elements['common-rnsf'].onblur = digitFieldBlurHandler;
        elements['common-rnmj'].onblur = txtCommonRnmjBlurHandler;

        elements['#btnSearch'].onclick = btnSearchClickHandler;
        elements['#btnReset'].onclick  = btnResetClickHandler;

        elements['common-name'].onkeyup = txtarCommonNameKeyUpHandler;
        elements['common-name'].onblur  = monospaceFieldBlurHandler;

        elements['common-full-name'].onkeyup = txtarCommonNameKeyUpHandler;
        elements['common-full-name'].onblur  = monospaceFieldBlurHandler;

        elements['#txtCommonMainActivityGked'].onblur
            = txtCommonMainActivityGkedBlurHandler;

        elements['common-ownership-form'].onchange
            = sltCommonOwnershipFormChangeHandler;

        elements['common-civil-legal-status'].onchange
            = sltCommonCivilLegalStatusChangeHandler;

        elements['common-bank-bic'].onkeyup = txtCommonBankBicKeyUpHandler;
        elements['common-bank-bic'].onblur  = txtCommonBankBicBlurHandler;

        elements['common-bank-account'].onblur = digitFieldBlurHandler;

        elements[juristicAddressMap.postCode].onblur = digitFieldBlurHandler;

        elements[juristicAddressMap.location].onclick
            = elements[juristicAddressMap.location].onfocus
            = commonAddressHandlers.txtCommonJuristicLocationClickHandler;

        elements[juristicAddressMap.street].onblur    = monospaceFieldBlurHandler;
        elements[juristicAddressMap.building].onblur  = monospaceFieldBlurHandler;
        elements[juristicAddressMap.apartment].onblur = monospaceFieldBlurHandler;

        elements['#chkbxCommonPhysicalAddressSameAsCommonJuristicAddress'].onclick
            = chkbxIsCommonPhysicalAddressSameAsCommonJuristicAddressClickHandler;

        elements[physicalAddressMap.postCode].onblur = digitFieldBlurHandler;

        elements[physicalAddressMap.location].onclick
            = elements[physicalAddressMap.location].onfocus
            = commonAddressHandlers.txtCommonPhysicalLocationClickHandler;

        elements[physicalAddressMap.street].onblur    = monospaceFieldBlurHandler;
        elements[physicalAddressMap.building].onblur  = monospaceFieldBlurHandler;
        elements[physicalAddressMap.apartment].onblur = monospaceFieldBlurHandler;

        elements[chiefMap.passportSeries].onblur
            = txtCommonRepresentativePassportSeriesFieldBlurHandler;

        elements[chiefMap.passportNumber].onblur = nospaceFieldBlurHandler;

        elements[chiefMap.passportIssuingAuthority].onblur = monospaceFieldBlurHandler;
        elements[chiefMap.passportIssingDate].onblur       = dateFieldBlurHandler;

        elements['#btnChiefSearch'].onclick = btnCommonChiefSearchClickHandler;

        elements[chiefMap.surname].onblur    = monospaceFieldBlurHandler;
        elements[chiefMap.name].onblur       = monospaceFieldBlurHandler;
        elements[chiefMap.middleName].onblur = monospaceFieldBlurHandler;
        elements[chiefMap.workPhone].onblur  = monospaceFieldBlurHandler;

        elements[chiefMap.deviceSerial].onblur = txtDeviceSerialBlurHandler;

        elements['#btnAddRepresentative'].onclick
            = btnAddCommonRepresentativeClickHandler;
    }

    u.form = {
        setup: (function(){
            var grepFn = function(record){
                return record.id > 1;
            };

            return function(config){
                config = config || {};

                if(!config.form){
                    throw new Error('"config.form" is not specified or is empty');
                } else if(typeof config.form == 'string') {
                    config.form = d.getElementById(config.form);
                }

                if(!config.dictionaries){
                    throw new Error('"config.dictionaries" is not specified or is empty');
                }

                config.representatives = {
                    links: {},
                    maps: {},
                    dom: {}
                };

                cfg = config;

                cfg.dictionaries['common-representative-role'] = $.grep(
                    cfg.dictionaries['common-representative-role'],
                    grepFn
                );

                modes.initialize();

                pasteChiefDictionariesFn();
                cacheElements();
                setDisabled(chiefMap.edsUsageModels, false);
                pasteOptionToStaticListElements();
                applyEventHandlers();
                reset();
            };
        })()
    };

})(window, document, window.utils);