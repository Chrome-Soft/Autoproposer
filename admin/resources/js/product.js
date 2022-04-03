class ProductEdit {
    constructor() {
        this.slug = $('input[name="slug"]').val();
    }

    addEventListeners() {
        this.addDatePicker();
        this.onCheckboxChange();
    }

    addDatePicker() {
        $(document).on('focus', '.datepicker', function () {
            $(this).datetimepicker({
                format: 'YYYY.MM.DD'
            });
        });

        $(document).on('focus', '.datetimepicker', function () {
            $(this).datetimepicker({
                stepping: 5,
                collapse: false,
                sideBySide: true,
                format: 'YYYY.MM.DD HH:mm'
            });
        });
    }

    onCheckboxChange() {
        const that = this;
        $(document).on('change', '[type="checkbox"]', function () {
            that.toggleHiddenInput($(this));
        });
    }

    toggleHiddenInput(checkbox) {
        const checkboxValue = checkbox.is(':checked') ? 1 : 0;
        if (checkboxValue == 1) {
            checkbox.next('input[type="hidden"]').remove();
        } else {
            let elem = checkbox.next('input[type="hidden"]');
            if (elem.length > 0) {
                elem.val('off');
            } else {
                elem = $('<input type="hidden" name="attribute_values[]" value="off">');
                checkbox.after(elem);
            }
        }
    }
}

$(document).ready(() => {
    const productEdit = new ProductEdit;
    productEdit.addEventListeners();
});