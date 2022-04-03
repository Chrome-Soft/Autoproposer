
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
require('./bootstrap');


/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */
const files = require.context('./', true, /\.vue$/i);
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('product-form', require('./pages/ProductForm.vue'));

const app = new Vue({
    el: '#app'
});

window.deleteConfirm = function (callback) {
    $('#delete-modal').modal('show');

    $('#delete-modal .btn-primary').off().on('click', function() {
        $('#delete-modal').modal('hide');
        callback(true);
    });

    $('#delete-modal .btn-secondary').off().on('click', function() {
        $('#delete-modal').modal('hide');
        callback(false);
    });
};

$(document).ready(function () {
    jQuery.extend(true, jQuery.fn.datetimepicker.defaults, {
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'glyphicon-arrow-left',
            next: 'fas fa-chevron-right',
            today: 'fas fa-calendar-check',
            clear: 'far fa-trash-alt',
            close: 'far fa-times-circle'
        }
    });

    $(document).on('click', '.btn.btn-danger.confirmed', function (event) {
        event.stopPropagation();
        event.preventDefault();
        const button = $(this);

        deleteConfirm(function (res) {
             if (!res) {
                 return false;
             } else {
                 button.parent('form').submit();
             }
        });
    });
});