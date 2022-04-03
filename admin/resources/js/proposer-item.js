class ProposerItemCreate {
    constructor() {
        this.select = $('select[name="type"]');
        this.htmlContent = $('#html-content');
        this.product = $('#product-selector');
        this.image = $('#image-upload');
        this.type = this.select.val();

        if (this.type) {
            this.show(this.type);
        }

        tinymce.init({ selector: '#html-content textarea' });
    }

    onChange() {
        this.select.change(x => {
            const value = $(x.target).val();
            console.log(value);
            this.show(value);
        });
    }

    show(type) {
        const contentHandler = this.createContentHandler(type);
        contentHandler.show();
    }

    createContentHandler(type) {
        const switcher = {
            'html': new ContentHandler(this.htmlContent),
            'image': new ContentHandler(this.image),
            'product': new ContentHandler(this.product),
            '': new ContentHandler($(''))
        };
        return switcher[type] || new ContentHandler($(''));
    }
}

class ContentHandler {
    constructor(htmlElement) {
        this.element = htmlElement;
    }

    show() {
        $('.dynamic-content').addClass('d-none');
        this.element.removeClass('d-none');
    }
}

$(document).ready(() => {
    const itemCreate = new ProposerItemCreate;
    itemCreate.onChange();
});