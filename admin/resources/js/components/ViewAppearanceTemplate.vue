<template>
    <div>
        <a :href="getUrl()" target="_blank">Sablon megtekintése</a>
    </div>
</template>

<script>
    import _ from 'lodash';

    export default {
        props: ['template', 'storageUrl'],

        data() {
            return {};
        },

        methods: {
            getUrl() {
                return this.storageUrl + '/' + _.get(this.template, 'photo_path', '');
            }
        }
    }
</script>