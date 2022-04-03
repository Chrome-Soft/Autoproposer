<template>
    <div class="kf-container" :class="getClass()" :style="getStyle()">
        <div class="kf-items-container">
            <a class="kf-item" v-for="item of items" @click="onItemClick(item)" :href="item.link" target="_blank">
                <img v-if="item.type_key === 'product' || item.type_key === 'image'" :src="getImg(item)" alt="">
                <div v-else v-html="item.html_content"></div>
            </a>
        </div>
    </div>
</template>

<script>
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
            },

            getStyle() {
                if (this.proposer.type_id == 1) {
                    return {
                        width: this.proposer.width + 'px',
                        height: this.proposer.height + 'px',
                    }
                }
            },

            getClass() {
                if (this.proposer.type_id == 2) {
                    return {
                        responsive: true,
                        embedded: true
                    };
                }

                return {
                    responsive: false,
                    embedded: false
                };
            }
        }
    }
</script>

<style>
    .embedded {
        width: 970px;
        height: 120px;
        position: fixed;
        left: 50%;
        transform: translateX(-50%);
    }
    .kf-container {
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

    @media only screen and (max-width: 970px) {
        .kf-container.responsive {
            width: 800px;
            height: 220px;
        }
        .kf-container.responsive .kf-items-container {
            height: 100%;
            padding-top: 2px;
        }
        .kf-container.responsive a {
            padding-top: 4px;
            width: 33.33%;
            text-align: center;
        }
    }

    @media only screen and (max-width: 800px) {
        .kf-container.responsive {
            width: 600px;
        }
    }

    @media only screen and (max-width: 600px) {
        .kf-container.responsive {
            width: 400px;
        }
        .kf-container.responsive .kf-items-container {
            overflow: auto;
        }
        .kf-container.responsive a {
            width: 50%;
        }
    }

    @media only screen and (max-width: 420px) {
        .kf-container.responsive {
            width: 420px;
        }
    }

</style>