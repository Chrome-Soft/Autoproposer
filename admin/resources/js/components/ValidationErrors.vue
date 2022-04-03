<template>
    <div class="alert alert-flash alert-danger"
         role="alert"
         v-show="show">
        <h5>Kérjük javítsd az alábbi hibákat:</h5>
        <ul>
            <li v-for="message in messages" v-text="message"></li>
        </ul>
    </div>
</template>

<script>
    export default {
        props: ['error'],

        data() {
            return {
                body: '',
                show: false,
                messages: []
            }
        },

        created() {
            window.events.$on('validationError', data => {
                if (data.response.response.status != 422) {
                    flash('Hiba történt a művelet során', 'danger');
                    return;
                }

                const responseMessages = data.response.response.data;
                let messages = [];

                for (const key of Object.keys(responseMessages)) {
                    messages = messages.concat(responseMessages[key]);
                }

                this.messages = messages;
                this.flash();
            });
        },

        methods: {
            flash() {
                this.show = true;
                this.hide();
            },
            hide() {
                setTimeout(() => {
                    this.show = false;
                }, 8000);
            }
        }
    }
</script>

<style>
    .alert-flash {
        position: fixed !important;
        right: 25px;
        bottom: 25px;
    }
</style>