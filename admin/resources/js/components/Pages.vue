<template>
    <div id="pages-container">
        <h2>Aloldalak</h2>
        <button v-if="!showForm" class="btn btn-success" @click.prevent="showForm = true">
            <span class="glyphicon glyphicon-plus"></span>
        </button>

        <form v-if="showForm">
            <div class="form-group">
                <label for="name">Név</label>
                <input type="text" class="form-control" id="name" v-model="newPage.name">
            </div>
            <div class="form-group">
                <label for="url-autocomplete">Keresés meglévő menüpontok között</label>
                <autocomplete
                        input-class="form-control"
                        :request-headers="authHeaders"
                        :results-display="autocompleteDisplay"
                        placeholder="Keresés a rögzített URL -ek között"
                        id="url-autocomplete"
                        results-value="value"
                        @noResults="onAutocompleteNoResults"
                        @selected="onAutocompleteSelect"
                        source="/api/page-load?q=">
                    <template slot="noResults">
                        Kiválasztás
                    </template>
                </autocomplete>
            </div>
            <div class="form-group">
                <label for="url">URL</label>
                <input type="text" class="form-control" v-model="newPage.url" id="url">
            </div>

            <button class="btn btn-success" @click.prevent="onSavePage()">Mentés</button>
            <button class="btn btn-secondary" @click.prevent="onCloseForm()">Mégsem</button>
        </form>
        <list collection-name="pages" :default-filters="defaultFilters()"></list>
    </div>
</template>

<script>
    import Autocomplete from 'vuejs-auto-complete';

    export default {
        components: { Autocomplete },
        props: ['partner'],

        data() {
            return {
                showForm: false,
                newPage: {
                    name: '',
                    url: '',
                    slug: null
                }
            }
        },

        created() {
            window.events.$on('edit-page', data => {
                this.onEditPage(data);
            });
        },

        computed: {
            authHeaders () {
                return {
                    'Authorization': `Bearer ${window.App.user.api_token}`
                }
            }
        },

        methods: {
            onAutocompleteNoResults(data) {
                this.newPage.url = data.query;
            },

            onAutocompleteSelect(data) {
                this.newPage.url = data.value;
            },

            autocompleteDisplay(item) {
                return item.value;
            },

            defaultFilters() {
                return [
                    { column: 'partner_id', relation: 1, value: this.partner.id }
                ];
            },

            onSavePage() {
                const requestType = this.newPage.slug ? 'patch' : 'post';
                const url = this.newPage.slug ? `/api/pages/${this.newPage.slug}` : '/api/pages';
                const data = {
                    name: this.newPage.name,
                    url: this.newPage.url,
                    partner_id: this.partner.id
                };
                const msg = this.newPage.slug ? 'Sikeres szerkesztés' : 'Sikeres létrehozás';

                if (this.newPage.slug != null) {
                    data.slug = this.newPage.slug;
                }

                axios[requestType](url, data)
                    .then(res => {
                        flash(msg);
                        this.showForm = false;
                        this.resetForm();

                        window.events.$emit('refresh-list', {
                            list: 'pages'
                        });
                    })
                    .catch(err => {
                        validationError(err);
                    });
            },

            onEditPage(data) {
                this.newPage.name = data.item.name;
                this.newPage.url = data.item.url;
                this.newPage.slug = data.item.slug;

                this.showForm = true;
            },

            onCloseForm() {
                this.showForm = false;
                this.resetForm();
            },

            resetForm() {
                this.newPage.name = '';
                this.newPage.url = '';
                this.newPage.slug = null;
            }
        }
    }
</script>

<style>
    #pages-container { margin-top:20px; }
</style>