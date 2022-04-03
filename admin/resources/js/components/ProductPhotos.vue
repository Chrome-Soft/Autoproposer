<template>
    <div>
        <img :src="photo.public_path" alt="">
        <!--<div v-for="photo in photos" style="display: inline">-->
            <!--<img :src="photo.public_path" alt="photo" width="200" height="200">-->

            <!--<button v-if="canUpdate" class="btn btn-danger remove-photo" @click.prevent="remove(photo.id)">Törlés</button>-->
        <!--</div>-->
    </div>
</template>

<script>
    /**
     * Itt régen több kép volt, de jelengleg csak egyet lehet feltölteni, ezért a sok nem használt kód
     */
    export default {
        props: ['product', 'canUpdate', 'initPhoto'],

        data() {
            return {
                photo: null
            };
        },

        created() {
            this.photo = this.initPhoto;
        },

        methods: {
            fetch() {
                axios.get(`/api/products/${this.product.slug}/photos`)
                    .then(res => this.photo = res.data.photos[0]);
            },

            remove(id) {
                deleteConfirm((res) => {
                    if (res === true) {
                        axios.delete(`/api/products/${this.product.slug}/photos/${id}`)
                            .catch(err => flash('Hiba történt a kép törlése során', 'danger'))
                            .then(res => {
                                this.fetch();
                                flash('Sikeres törlés');
                            });
                    }
                });
            }
        }
    }
</script>