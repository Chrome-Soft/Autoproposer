<template>
    <div>
        <h2>Termékek</h2>

        <div class="form-group">
            <button class="btn btn-primary" v-if="!showForm" @click.prevent="showForm = true">Termék hozzáadása</button>
        </div>

        <form v-if="showForm">
            <div class="form-group">
                <!--<select class="form-control" v-model="newProduct.id">
                    <option value="-1" selected>Kérlek válassz...</option>
                    <option v-for="p in products" :value="p.id" v-text="p.name"></option>
                </select>-->
                <label for="url-autocomplete">Keresés a termékek között</label>
                <autocomplete
                        input-class="form-control"
                        :request-headers="authHeaders"
                        :results-display="autocompleteDisplay"
                        placeholder="Keresés a termékek között"
                        id="url-autocomplete"
                        results-value="value"
                        @noResults="onAutocompleteNoResults"
                        @selected="onAutocompleteSelect"
                        :source="'/api/products/autocomplete?segment='+ segment.id + '&q='">
                    <template slot="noResults">
                        Kiválasztás
                    </template>
                </autocomplete>
            </div>

            <div class="form-group">
                <select class="form-control" v-model="newProduct.priorityId">
                    <option value="-1" selected>Kérlek válassz...</option>
                    <option v-for="p in priorities" :value="p.id" v-text="priorityName(p)"></option>
                </select>
            </div>

            <div class="form-group">
                <button :disabled="!isFormValid()" class="btn btn-success" @click.prevent="save">Mentés</button>
                <button class="btn btn-link" @click.prevent="showForm = false">Mégsem</button>
            </div>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Termék</th>
                    <th scope="col">Prioritás</th>
                    <th scope="col">Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="segmentProduct in segmentProducts">
                    <td scope="row">
                        <span v-text="segmentProduct.product.name"></span>
                    </td>
                    <td scope="row">
                        <span v-text="segmentProduct.priority.name"></span>
                    </td>
                    <td>
                        <button v-if="canUpdate" class="btn btn-danger" @click.prevent="remove(segmentProduct.id)">Eltávolítás</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import Autocomplete from 'vuejs-auto-complete';

    export default {
        components: { Autocomplete },
        props: ['segment', 'initSegmentProducts', 'canUpdate', 'priorities', 'initProducts'],

        data() {
            return {
                showForm: false,
                products: [],
                segmentProducts: [],
                newProduct: this.emptyProduct()
            }
        },

        computed: {
            authHeaders () {
                return {
                    'Authorization': `Bearer ${window.App.user.api_token}`
                }
            }
        },

        created() {
            this.segmentProducts = this.initSegmentProducts;
            this.products = this.initProducts || [];
        },

        watch: {
            showForm(newValue, oldaValue) {
                if (!newValue) this.newProduct = this.emptyProduct();
            }
        },

        methods: {
            autocompleteDisplay(item) {
                return item.value;
            },

            onAutocompleteNoResults(data) {
                this.newProduct = this.emptyProduct();
            },

            onAutocompleteSelect(data) {
                this.newProduct.id = data.selectedObject.id;
            },

            emptyProduct() {
                return {
                    id: null,
                    priorityId: null
                };
            },

            priorityName(priority) {
                return `${priority.name} (${priority.description})`;
            },

            isFormValid() {
                return this.newProduct.id != null && this.newProduct.id != -1
                    && this.newProduct.priorityId != null && this.newProduct.priorityId != -1;
            },

            remove(id) {
                axios.delete('/api/segment-products/' + id)
                    .then(() => {
                        const deletedSegmentProduct = this.segmentProducts.find(x => x.id == id);

                        // A törölt termék vissza kerül a termékek közé
                        this.products.push(deletedSegmentProduct.product);
                        this.products.sort((a, b) => {
                            if(a.name.toLowerCase() < b.name.toLowerCase()) return -1;
                            if(a.name.toLowerCase() > b.name.toLowerCase()) return 1;
                            return 0;
                        });

                        this.segmentProducts = this.segmentProducts.filter(x => x.id != id);

                        flash('Sikeres eltávolítás');
                    })
                    .catch(
                        (err) => {
                            flash('Hiba történt az eltávolítás során', 'danger');
                            console.log(err);
                        }
                    );
            },

            save() {
                const data = {
                    productId: this.newProduct.id,
                    priorityId: this.newProduct.priorityId,
                    segmentId: this.segment.id,
                };

                axios.post('/api/segment-products', data)
                    .then(res => {
                        this.showForm = false;
                        this.segmentProducts.push(res.data);

                        this.products = this.products.filter(x => x.id != res.data.product.id);

                        flash('Sikeres hozzáadás');
                    })
                    .catch(
                        (err) => {
                            flash('Hiba történt a hozzáadás során', 'danger');
                            console.log(err);
                        }
                    );
            }
        }
    }
</script>
