import {observeDom} from './observeDom';

export default function dynForms(formId) {
    const dynForms = $('#'+formId+' form');

    observeDom(document.getElementById(formId), function() {
        updateVisibilities();
    });

    const getValue = function(name) {
        let array = [];
        let isArray = false;
        const objects = document.querySelectorAll('[name="form'+name+'"]');

        for (var i = 0; i < objects.length; i++) {
            if (objects[i].getAttribute('type') === 'radio') {
                if (objects[i].checked) {
                    return objects[i].value;
                }
            } else if (objects[i].getAttribute('type') === 'checkbox') {
                isArray = true;
                if (objects[i].checked) {
                    array.push(objects[i].value);
                }
            } else {
                if (objects[i].value !== undefined) {
                    array.push(objects[i].value);
                }
            }
        }

        if (isArray || array.length > 1) {
            return array;
        }
        if (array.length > 0) {
            return array[0];
        }

        return undefined;
    };

    function resetField($field) {
        $field.find('input:radio').prop('checked', false);
        $field.find('input:checkbox:checked').prop( "checked", false );
        $field.find('select').val("");
    }

    function equalOrInArray(value, expected) {
        if(Array.isArray(value)) {
            return value.includes(expected);
        }

        return value === expected;
    }

    function moreThan(value, limit) {
        if(Array.isArray(value)) {
            return value.length > parseInt(limit);
        }
        if (value !== undefined && parseInt(limit) === 0) {
            return true;
        }

        return false;
    }

    function lessThan(value, limit) {
        if(Array.isArray(value)) {
            return value.length < parseInt(limit);
        }
        if (value === undefined && parseInt(limit) > 0) {
            return false;
        }

        return true;
    }

    function dateBefore(date, before) {
        const dateParsed = Date.parse(date);
        const beforeParsed = Date.parse(before);

        if (isNaN(dateParsed) || isNaN(beforeParsed)) {
            return false;
        }

        return dateParsed < beforeParsed;
    }

    function dateAfter(date, after) {
        const dateParsed = Date.parse(date);
        const afterParsed = Date.parse(after);

        if (isNaN(dateParsed) || isNaN(afterParsed)) {
            return false;
        }

        return dateParsed > afterParsed;
    }

    function countValidRules(rules) {
        let counter = 0;
        if (rules === undefined) {
            return;
        }

        const objectKeys = Object.keys(rules);
        for (let i = 0; i < objectKeys.length; i++) {
            const value = getValue(rules[objectKeys[i]].field);
            const score = getScore(rules[objectKeys[i]].field);
            let pass = false;
            switch (rules[objectKeys[i]].condition) {
                case 'is':
                    pass = equalOrInArray(value, rules[objectKeys[i]].value);
                    break
                case 'is-not':
                    pass = !equalOrInArray(value, rules[objectKeys[i]].value);
                    break
                case 'more-than':
                    pass = moreThan(value, rules[objectKeys[i]].value);
                    break
                case 'less-than':
                    pass = lessThan(value, rules[objectKeys[i]].value);
                    break
                case 'date-before':
                    pass = dateBefore(value, rules[objectKeys[i]].value);
                    break
                case 'date-after':
                    pass = dateAfter(value, rules[objectKeys[i]].value);
                    break
                case 'score-below':
                    pass = score < rules[objectKeys[i]].value;
                    break
                case 'score-above':
                    pass = score > rules[objectKeys[i]].value;
                    break
                default:
                    console.log('Test unknown: ' + rules[objectKeys[i]].condition);
            }
            counter += pass ? 1 : 0;
        }
        return counter;
    }

    const setVisibility = function(field) {
        const $field = $(field);
        const rules = $field.data('rules');
        const showHide = $field.data('show-hide');
        const allAny = $field.data('all-any');
        let counter = countValidRules(rules);

        let show = true;

        if (showHide === 'hide' && allAny === 'any' && counter > 0) {
            show = false;
        }
        if (showHide === 'hide' && allAny === 'all' && counter === rules.length) {
            show = false;
        }
        if (showHide === 'show' && allAny === 'any' && counter === 0) {
            show = false;
        }
        if (showHide === 'show' && allAny === 'all' && counter !== rules.length) {
            show = false;
        }

        if (show) {
            $field.show();
        } else {
            $field.hide();
            resetField($field);
        }


    };

    function getScore(name) {
        const fields = document.querySelectorAll('[name="'+name+'"]');
        let total = 0;
        fields.forEach(function(element){
            if (!element.checked) {
                return;
            }
            const $field = $(element);

            const rules = $field.data('rules');
            const counter = countValidRules(rules);
            const score = $field.data('score');
            const alternativeScore = $field.data('alternative-score');
            const allAny = $field.data('all-any');

            if (allAny === 'all' && rules.length === counter) {
                total += alternativeScore;
            } else if (allAny === 'any' && counter > 0) {
                total += alternativeScore;
            } else {
                total += score;
            }
        });

        return total;
    }

    const updateVisibilities = function() {
        $('.collapse-on-change').collapse('hide');
        dynForms.find('.form-group').each(function(){
            setVisibility(this);
        });
    };

    dynForms.on('submit', function (e){
        if ('button' !== document.activeElement.localName || document.activeElement.getAttribute('value') === undefined) {
            e.preventDefault();
        }
        $('input[name="advice"]').val(document.activeElement.getAttribute('value'));
    })

    dynForms.find('input,select').on('change', function(){
        updateVisibilities();
    })

    function uncheckedOther($input) {
        if ($input.parents('.form-group').find('input:checked').not(".none-of-the-above").prop( "checked", false ).length > 0) {
            updateVisibilities();
        }
    }

    dynForms.find('input.none-of-the-above').on('change', function(){
        uncheckedOther($(this));
    })

    function uncheckedNone($input) {
        if ($input.parents('.form-group').find('.none-of-the-above:checked').prop( "checked", false ).length > 0) {
            updateVisibilities();
        }
    }

    dynForms.find('input:checkbox').not('.none-of-the-above').on('change', function(){
        uncheckedNone($(this));
    })

    updateVisibilities();

}
