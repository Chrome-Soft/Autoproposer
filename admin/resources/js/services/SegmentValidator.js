export default class SegmentValidator {
    constructor() {
        this.messages = {};
    }

    addMessage(isValid, field, message) {
        if (!isValid)
            this.messages[field] = message;
    }

    clearMessages() {
        this.messages = {};
    }

    validate(criteria, value, validators) {
        let isValid = true;

        if (!validators) return;

        if (_.includes(validators, 'bool')) {
            isValid = value.toLowerCase() == 'igen' || value.toLowerCase() == 'nem';
            this.addMessage(isValid, criteria.slug,`${criteria.name} csak 'Igen' vagy 'Nem' érték lehet`);
        }

        if (_.includes(validators, 'number')) {
            isValid = /^\d+$/.test(value);
            this.addMessage(isValid, criteria.slug,`${criteria.name} csak számot tartalmazhat`);
        }

        if (_.includes(validators, 'version')) {
            isValid = /^(\d+\.)?(\d+\.)?(\d+)$/.test(value);
            this.addMessage(isValid, criteria.slug,`${criteria.name} csak x vagy x.y vagy x.y.z formátumú verzió szám lehet`);
        }

        if (_.includes(validators, 'datetime')) {
            const isTime = moment(value, 'HH:mm', true).isValid();
            const isDate = moment(value, 'YYYY-MM-DD', true).isValid();
            const isDate1 = moment(value, 'YYYY.MM.DD', true).isValid();

            isValid = isTime || isDate || isDate1;
            this.addMessage(isValid, criteria.slug,`${criteria.name} csak év.hó.nap vagy óra:perc formátumú dátum / időpont lehet`);
        }

        if (_.includes(validators, 'phone_provider')) {
            isValid = value == '20' || value == '30' || value == '70';
            this.addMessage(isValid, criteria.slug,`${criteria.name} csak 20, 30, 70 lehet`);
        }

        return {
            isValid,
            messages: this.messages
        }
    }
}