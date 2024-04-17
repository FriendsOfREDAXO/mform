if (typeof Alpine !== 'undefined') {
    // Wenn Alpinejs bereits verfügbar ist
    addAlpineDirective();
} else {
    document.addEventListener('alpine:init', () => {
        // Wenn Alpinejs verfügbar ist
        addAlpineDirective();
    })
}

// Alpinejs directive um pjax/jquery murks zu verhindern...
function addAlpineDirective() {
    Alpine.directive('repeater', (el) => {
        setTimeout(() => {
            el.dispatchEvent(new CustomEvent('repeater:ready'))
        })
    })
}

window.repeater = () => {
    return {
        groups: [],
        value: '',
        initialValue: [],
        // der preloader muss im template vorhanden sein
        $alpineLoader: document.querySelector('.alpine-loader'),
        // initialisiert ein repeater mit allen nötigen objekten wie value, groups
        // hierüber kann auch der loader ausgeblendet werden
        // TODO preloader testen ...
        // die methode wird durch @repeater:ready.once aufgerufen
        setInitialValue(initialValue) {
            // Vorhanden Daten setzen...
            this.initialValue = initialValue;
            this.groups = [];

            if (this.initialValue) {
                this.groups = this.initialValue;
                this.value = JSON.stringify(this.groups);
            }

            this.$nextTick(() => {
                // blended loader spinner aus
                // this.$alpineLoader.classList.remove('rex-visible');
            });
        },
        // feuert das rex:ready event für den inhalt des repeater items idKey auf fields ebene
        // die methode wird durch alpine x-init aufgerufen
        rexInitFieldElement(idKey) {
            let that = this;
            that.rexPreInitElements($('#' + idKey + ' > .mform'));
            that.rexInitElements($('#' + idKey + ' > .mform'));
        },
        // feuert das rex:ready event für den inhalt des repeater items idKey auf group ebene
        // die methode wird durch alpine x-init aufgerufen
        rexInitGroupElement(idKey) {
            let that = this;
            that.rexPreInitElements($('#' + idKey + ' > .mform > *'));
            that.rexInitElements($('#' + idKey + ' > .mform > *'));
        },
        // wird vor rex:ready für ein repeater item ausgeführt
        rexPreInitElements(elements) {
            if (elements.length > 0) {
                elements.each(function (index, element) {
                    // PREPARE SELECTPICKER
                    // die selectpicker dürfen erst nach update.value instanziert werden
                    // dazu braucht es ein delay von mindestens einer milli sekunde
                    // deswegen erstmal keine instanzierung durch rex:ready
                    if ($(element).find('.selectpicker') !== undefined) {
                        $(element).find('.selectpicker').each(function () {
                            $(this).removeClass('selectpicker')
                                .addClass('repeater-selectpicker');
                        });
                    }
                    // PREPARE IMAGE LIST
                    // if ($(element).find('.rex-js-widget-imglist').length > 0) {
                    //     $(element).find('.rex-js-widget-imglist').each(function(){
                    //         $(this).removeClass('rex-js-widget-imglist').addClass('repeater-imglist');
                    //     });
                    // }
                    // PREPARE TOGGLE
                    if ($(element).find('input[type=checkbox][data-mform-toggle^=toggle]').length > 0) {
                        $(element).find('input[type=checkbox][data-mform-toggle^=toggle]').each(function () {
                            $(this).removeAttr('data-mform-toggle').addClass('repeater-toggle').removeAttr('x-on:change');
                        });
                    }
                    // PREPARE CKE5
                    // der editor soll nicht durch das rex:ready event initialisiert werden
                    if ($(element).find('.cke5-editor') !== undefined) {
                        $(element).find('.cke5-editor').each(function () {
                            if (typeof cke5_destroy !== 'function') {
                                return;
                            }
                            // damit es keine konflikte durch pjax gibt müssen die instanzen sauber entfernt werden
                            cke5_destroy($(this));
                            $(this).removeClass('cke5-editor') // verhindert das initialisieren durch rex:ready
                                .addClass('cke5-repeater'); // kennzeichen dafür, dass cke5 durch den repeater initialisiert wird
                        });
                    }
                });
            }
        },
        // führt rex:ready für ein repeater item aus
        // prepariert nachträglich sonderelemente oder widgets wie custom-links und cke5
        rexInitElements(elements) {
            let that = this;
            if (elements.length > 0) {
                elements.each(function (index, element) {
                    // triggert das klassische rex:ready event für den repeater item
                    $(element).trigger('rex:ready', [$(element)]);
                    // CUSTOM LINK PREPARE
                    if ($(element).find('.custom-link').length > 0) {
                        // fügt global für alle custom link elements ein listener hinzu der auf
                        that.rexInitCustomLink();
                    }
                    // SELECT PICKER PREPARE
                    if ($(element).find('.repeater-selectpicker').length > 0) {
                        setTimeout(function () {
                            $(element).find('.repeater-selectpicker').each(function () {
                                if (!$(this).hasClass('select-repeater-init')) {
                                    $(this).addClass('select-repeater-init');
                                    // hier keine spezielle behandlung nötig, das x-on:change value update reicht aus
                                    $(this).selectpicker();
                                }
                            });
                        }, 5);
                    }
                    // SELECT PICKER PREPARE
                    if ($(element).find('.repeater-toggle').length > 0) {
                        setTimeout(function () {
                            // initialisiere jede einzeilne toggle input checkbox
                            $(element).find('.repeater-toggle').each(function () {
                                if (!$(this).hasClass('repeater-toggle-init')) {
                                    $(this).addClass('repeater-toggle-init');
                                    that.rexInitToggle($(this));
                                }
                            });
                        }, 5);
                    }
                    // PREPARE CKE5
                    if ($(element).find('.cke5-repeater').length > 0) {
                        // initialisiere jede cke5 textarea einzeiln
                        $(element).find('.cke5-repeater:not(.cke5-repeater-init)').each(function () {
                            if (!$(this).hasClass('cke5-repeater-init')) {
                                $(this).addClass('cke5-repeater-init');
                                that.rexInitCke5($(this));
                            }
                        });
                    }
                });
            }
        },
        // fügt global für rex:selectCustomLink events ein listener hinzu
        // setzt bei trigger für das input feld den group element eintrag
        rexInitCustomLink() {
            let that = this;
            $(window).on('rex:selectCustomLink', (event, linkurl, linktext, input) => {
                // ermittle anhand des input den group element eintrag
                let element = that.rexGetInputElement(input);
                element['name'] = linktext; // setzt linktext für den shown input "Articlename [id]" oder sonstiger form visible text
                element['id'] = linkurl; // setzt die linkid oder die linkurl / das linkziel
                that.updateValues(); // reload to value form inputs
            });
        },
        rexGetInputIndexElement(input) {
            let parent = input.closest('.repeater-group'),
                parentParent = parent.parents('.repeater-group'),
                index = parent.attr('iteration'),
                groups = input.attr('groups'),
                parentIndex = ((parentParent.length) ? parentParent.attr('iteration') : undefined),
                explodedGroups = (groups.indexOf(".") !== -1) ? groups.split(".") : [],
                element = (this.groups[index] !== undefined) ? this.groups[index] : undefined;
            if (explodedGroups.length > 1 && parentIndex !== undefined) {
                element = this.groups[parentIndex][explodedGroups[1]][index];
            }
            return element;
        },
        rexGetInputElement(input) {
            let nameKey = input.attr('item_name_key'),
                element = this.rexGetInputIndexElement(input);
            if (element[nameKey] === undefined) {
                element[nameKey] = {'name': '', 'id': ''};
            }
            return element[nameKey];
        },
        rexInitToggle(input) {
            let that = this,
                element = this.rexGetInputIndexElement(input),
                nameKey = input.attr('item_name_key');
            if (element[nameKey] === input.attr('data-value')) {
                input.prop('checked', true);
            }
            input.bootstrapMFormToggle('destroy').bootstrapMFormToggle();
            input.change(function () {
                if ($(this).prop('checked') === true) {
                    $(this).val($(this).attr('data-value'));
                    element[nameKey] = $(this).attr('data-value');
                } else {
                    $(this).val('');
                    element[nameKey] = '';
                }
                that.updateValues();
            })
        },
        rexInitCke5(cke5Area) {
            let that = this;
            cke5_destroy(cke5Area);
            cke5_init_ready([cke5Area]);
            $(window).on('rex:cke5IsInit', (event, editor, uniqueId) => {
                if (cke5Area.attr('id') === uniqueId) {
                    let element = that.rexGetInputIndexElement($(editor.sourceElement)),
                        nameKey = $(editor.sourceElement).attr('item_name_key');
                    editor.setData(element[nameKey]);
                    editor.model.document.on('change:data', () => {
                        element[nameKey] = editor.getData();
                        that.updateValues();
                    });
                }
            });
        },
        // manipuliert anhand der group element einträge die formular input values
        updateValues(idKey = '0') {
            // Gruppen werden als String im value-Model gespeichert...
            this.value = JSON.stringify(this.groups);
        },
        // ermöglicht das dynamische anlegen der group elements struktur
        // addGroup und addFields sind dreh und angelpunkte für das versorgen der form input values durch alpine
        addGroup(obj) {
            this.groups.push(JSON.parse(JSON.stringify(obj))); // bottom
            // this.groups.unshift(obj); // top
        },
        // ermöglicht das dynamische anlegen der group elements fields ebenen struktur
        addFields(index, obj, fieldsKey, idKey) {
            this.groups[index][fieldsKey].push(JSON.parse(JSON.stringify(obj)));
        },
        removeGroup(index) {
            this.groups.splice(index, 1);
            this.updateValues();
        },
        removeField(index, fieldIndex, fieldsKey) {
            this.groups[index][fieldsKey].splice(fieldIndex, 1);
            this.updateValues();
        },
        moveGroup(from, to) {
            this.groups.splice(to, 0, this.groups.splice(from, 1)[0]);
            this.updateValues();
        },
        moveField(index, from, to, fieldsKey) {
            this.groups[index][fieldsKey].splice(to, 0, this.groups[index][fieldsKey].splice(from, 1)[0]);
            this.updateValues();
        },
        addLink(id, index, nameKey, fieldsKey, fieldIndex) {
            let linkMap = openLinkMap(id).replace('redaxo://', '');
            $(linkMap).on('rex:selectLink', (event, linkurl, linktext) => {
                if (fieldsKey !== undefined && fieldIndex !== undefined) {
                    this.groups[index][fieldsKey][fieldIndex][nameKey] = {'name': linktext, 'id': linkurl};
                } else {
                    this.groups[index][nameKey] = {'name': linktext, 'id': linkurl};
                }
                this.updateValues();
            });
            return false;
        },
        removeLink(index, nameKey, fieldsKey, fieldIndex) {
            if (fieldsKey !== undefined && fieldIndex !== undefined) {
                this.groups[index][fieldsKey][fieldIndex][nameKey] = {'name': '', 'id': ''};
            } else {
                this.groups[index][nameKey] = {'name': '', 'id': ''};
            }
            this.updateValues();
        },
        openMedia(id, index, nameKey, fieldsKey, fieldIndex) {
            let params = '',
                media = newPoolWindow('index.php?page=mediapool/media' + params + '&opener_input_field=' + id);
            this.onMediaSelect(media, index, nameKey, fieldsKey, fieldIndex);
            return false;
        },
        addMedia(id, index, nameKey, fieldsKey, fieldIndex) {
            let params = '',
                media = newPoolWindow('index.php?page=mediapool/upload&opener_input_field=' + id + params);
            this.onMediaSelect(media, index, nameKey, fieldsKey, fieldIndex);
            return false;
        },
        viewMedia(id, index, nameKey, fieldsKey, fieldIndex) {
            let params = '',
                element = (fieldsKey !== undefined && fieldIndex !== undefined) ? this.groups[index][fieldsKey][fieldIndex][nameKey] : this.groups[index][nameKey],
                param = params + '&file_name=' + element.media,
                media = newPoolWindow('index.php?page=mediapool/media' + param + '&opener_input_field=' + id);
            this.onMediaSelect(media, index, nameKey, fieldsKey, fieldIndex);
            return false;
        },
        deleteMedia(id, index, nameKey, fieldsKey, fieldIndex) {
            if (fieldsKey !== undefined && fieldIndex !== undefined) {
                this.groups[index][fieldsKey][fieldIndex][nameKey] = '';
            } else {
                this.groups[index][nameKey] = '';
            }
            this.updateValues();
        },
        onMediaSelect(media, index, nameKey, fieldsKey, fieldIndex) {
            $(media).on('rex:selectMedia', (event, mediaName) => {
                if (fieldsKey !== undefined && fieldIndex !== undefined) {
                    this.groups[index][fieldsKey][fieldIndex][nameKey] = mediaName;
                } else {
                    this.groups[index][nameKey] = mediaName;
                }
                this.updateValues();
            });
        },
    }
}
