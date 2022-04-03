<template>
    <div class="kf-container">
        <div class="kf-items-container">
            <a class="kf-item" v-for="item of items" @click="onItemClick(item)" :href="item.link" target="_blank">
                <img v-if="item.type_key === 'product' || item.type_key === 'image'" :src="getImg(item)" alt="">
                <div v-else v-html="item.html_content"></div>
            </a>
        </div>
    </div>
</template>

<script>
    import _ from 'lodash';

    export default {
        props: ['proposer', 'cookieId', 'items', 'storageUrl', 'trackInteractions'],

        data() {
            return {}
        },

        methods: {
            getImg(item) {
                if (item.type_key === 'product') {
                    return _.get(item, 'thumbnail_photos.small.public_path', '');
                }

                if (item.type_key === 'image') {
                    // TODO base url
                    return this.storageUrl + '/' + _.get(item, 'thumbnail_photos.small.public_path', '');
                }
            },

            onItemClick(item) {
                if (this.trackInteractions) {
                    const data = {
                        cookieId: this.cookieId,
                        partnerId: this.proposer.partner.external_id,
                        type: 'view',
                        items: [
                            { id: item.id, name: item.name || null, type: item.type_key }
                        ]
                    };

                    axios.post('/api/interaction-iframe', data);
                }
            }
        }
    }
</script>

<style>
    .kf-container {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        background-color: #f1f1f1;
    }
    .kf-items-container {
        background-color: #f1f1f1;
        height: 100%;
        width: 100%;
        display: flex;
        justify-content: space-evenly;
        align-items: center;
        padding: 5px 0;
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
        flex-wrap: wrap;
    }
    .kf-item img {
        max-width: 140px;
        height: 90px;
    }
</style>