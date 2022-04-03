<template>
    <div>
        <h2>Proposerben megjelenő elemek</h2>
        <a v-if="canUpdate" :href="createUrl()" class="btn btn-primary">Új elem</a>

        <table v-if="items.length > 0" class="table" style="margin-top:20px !important">
            <thead>
                <tr scope="row">
                    <th scope="col">Típus</th>
                    <th scope="col">Összes megjelenés</th>
                    <th scope="col">Összes kattintás</th>
                    <th scope="col">Kattintás%</th>
                    <th scope="col">Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in items" v-bind:key="item.id" scope="row">
                    <td v-text="item.type.name" scope="col"></td>
                    <td v-text="item.all_present" scope="col"></td>
                    <td v-text="item.all_view" scope="col"></td>
                    <td v-text="item.view_ratio" scope="col"></td>
                    <td v-if="canUpdate" scope="col">
                        <a :href="editUrl(item.id)" class="btn btn-primary">Szerkesztés</a>
                        <button class="btn btn-danger" @click.prevent="remove(item.id)">Törlés</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <p v-else>Nincsenek elemek</p>
    </div>
</template>

<script>
    export default {
        props: ['proposer', 'canUpdate'],

        data() {
            return {
                items: []
            };
        },

        created() {
            this.fetch();
        },

        methods: {
            fetch() {
                axios.get(`/api/proposers/${this.proposer.slug}/items`)
                    .then(res => this.items = res.data.items);
            },

            editUrl(itemId) {
                return `${this.proposer.slug}/items/${itemId}/edit`;
            },

            createUrl() {
                return `/proposers/${this.proposer.slug}/items/create`;
            },

            remove(itemId) {
                deleteConfirm((res) => {
                    if (res === true) {
                        axios.delete(`/api/proposers/${this.proposer.slug}/items/${itemId}`)
                            .catch(err => flash('Hiba történt a törlés során', 'danger'))
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
