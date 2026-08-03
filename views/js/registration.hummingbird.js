(function () {
    'use strict';

    function getForm() {
        return document.querySelector('#customer-form') ||
            document.querySelector('form[name="customer-form"]');
    }

    function getField(name) {
        return document.querySelector('[name="' + String(name) + '"]');
    }

    function getPersonTypeField() {
        return document.querySelector('[name="person_type"]') ||
            document.querySelector('[name^="person_type["]') ||
            document.querySelector('[name^="person_type"]');
    }

    function getPersonTypeContainer(form) {
        var input = getPersonTypeField();
        var node = input;

        if (!input) {
            return null;
        }

        while (node && node !== form) {
            if (node.nodeType === 1 &&
                node.querySelectorAll('[name^="person_type"]').length > 1 &&
                (node.tagName === 'FIELDSET' ||
                    node.classList.contains('form-group') ||
                    node.classList.contains('js-form-group') ||
                    node.classList.contains('mb-3'))) {
                return node;
            }

            node = node.parentElement;
        }

        return getFieldContainer(input, form);
    }

    function getSelectedPersonType() {
        var selected = document.querySelector('[name="person_type"]:checked');
        return selected ? selected.value : null;
    }

    function getFieldContainer(input, form) {
        var node = input;

        while (node && node !== form) {
            if (node.nodeType === 1 && (
                node.classList.contains('form-group') ||
                node.classList.contains('js-form-group') ||
                node.classList.contains('mb-3') ||
                node.tagName === 'FIELDSET' ||
                node.hasAttribute('data-field-name')
            )) {
                return node;
            }

            node = node.parentElement;
        }

        return input.parentElement;
    }

    function moveAfter(node, anchor) {
        if (node && anchor && anchor.parentNode) {
            anchor.parentNode.insertBefore(node, anchor.nextSibling);
        }
    }

    function moveBefore(node, anchor) {
        if (node && anchor && anchor.parentNode) {
            anchor.parentNode.insertBefore(node, anchor);
        }
    }

    function updateFieldVisibility() {
        var config = window.agcustomers;
        var type = getSelectedPersonType();

        if (!config || !config.fields || !config.fields.customer) {
            return;
        }

        config.fields.customer.forEach(function (field) {
            if (!field || String(field.is_default_input) === '1' || !field.name) {
                return;
            }

            var input = getField(field.name);
            var container = input ? getFieldContainer(input, getForm()) : null;
            var visible = false;

            if (field.insert) {
                if (type) {
                    visible = field.insert[type] === true || String(field.insert[type]) === '1';
                } else {
                    var personTypes = config.type_persons || [];
                    visible = personTypes.length > 0;

                    personTypes.forEach(function (person) {
                        if (!person.active || !field.insert[person.name] ||
                            String(field.insert[person.name]) !== '1' &&
                            field.insert[person.name] !== true) {
                            visible = false;
                        }
                    });
                }
            }

            if (container) {
                container.style.display = visible ? '' : 'none';
            }

            if (input) {
                var required = type && field.required &&
                    (field.required[type] === true || String(field.required[type]) === '1');
                input.required = Boolean(required);
                input.classList.toggle('required', Boolean(required));
            }
        });
    }

    function applyMasks() {
        var config = window.agcustomers;

        if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.mask ||
            !config || !config.fields || !config.fields.customer) {
            return false;
        }

        config.fields.customer.forEach(function (field) {
            if (!field || !field.name || !field.mask) {
                return;
            }

            var input = getField(field.name);
            if (input) {
                window.jQuery(input).mask(field.mask);
            }
        });

        return true;
    }

    function bindPersonTypeChange() {
        var inputs = document.querySelectorAll('[name="person_type"]');
        Array.prototype.forEach.call(inputs, function (input) {
            if (input.getAttribute('data-agcustomers-hummingbird-bound') === '1') {
                return;
            }

            input.setAttribute('data-agcustomers-hummingbird-bound', '1');
            input.addEventListener('change', function () {
                updateFieldVisibility();
                positionFields();
            });
        });
    }

    function positionFields() {
        var form = getForm();
        var config = window.agcustomers;

        if (!form || !config || !config.fields || !config.fields.customer ||
            !config.config || !config.config.customer ||
            String(config.config.customer.position) !== '1') {
            return;
        }

        var birthday = getField('birthday');
        var password = getField('password');
        var anchor = birthday ? getFieldContainer(birthday, form) : null;

        if (!anchor && password) {
            anchor = getFieldContainer(password, form);
        }

        if (anchor) {
            config.fields.customer.forEach(function (field) {
                if (!field || String(field.is_default_input) === '1' ||
                    !field.name || field.name === 'person_type') {
                    return;
                }

                var input = getField(field.name);
                var container = input ? getFieldContainer(input, form) : null;

                if (container) {
                    moveAfter(container, anchor);
                    anchor = container;
                }
            });
        }

        var personType = getPersonTypeField();
        var firstName = getField('firstname');

        if (personType && firstName) {
            moveBefore(
                getPersonTypeContainer(form),
                getFieldContainer(firstName, form)
            );
        }
    }

    function schedulePositioning() {
        positionFields();
        updateFieldVisibility();
        bindPersonTypeChange();
        window.setTimeout(positionFields, 100);
        window.setTimeout(positionFields, 500);
        window.setTimeout(positionFields, 1000);
        window.setTimeout(function () {
            applyMasks();
            updateFieldVisibility();
            bindPersonTypeChange();
        }, 250);
    }

    document.addEventListener('DOMContentLoaded', schedulePositioning);

    if (document.readyState !== 'loading') {
        schedulePositioning();
    }
}());
