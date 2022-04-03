<template>
    <div>
        <button v-if="!showFilter" class="btn btn-primary" @click.prevent="showFilter = true">
            <span class="glyphicon glyphicon-search"></span>
        </button>
        <form class="form-inline" v-if="showFilter">

            <div class="current-filter">
                <select v-model="filters.currentColumn" class="form-control filter-column">
                    <option value="-1" selected>Kérlek válassz...</option>
                    <option v-for="column, key in columns" v-if="!isHidden(key) && !isExcludedFromFilters(key)" :value="key" v-text="column"></option>
                </select>

                <select v-model="filters.currentRelation" class="form-control filter-relation">
                    <option value="-1" selected>Kérlek válassz...</option>
                    <option v-for="relation in relations" :value="relation.id" v-text="relation.name"></option>
                </select>
                <input type="text" v-model="filters.currentValue" class="form-control filter-value" @keyup.enter="onFilter()">

                <button class="btn btn-primary filter-button" @click.prevent="addFilter">
                    <span class="glyphicon glyphicon-plus"></span>
                </button>
            </div>

            <div v-if="filters.items.length > 0">
                <div v-for="item, index in filters.items" class="current-filter-container">
                    <select v-model="item.column" class="form-control filter-column">
                        <option v-for="column, key in columns" v-if="!isHidden(key)" :value="key" v-text="column" :selected="key == item.column"></option>
                    </select>
                    <select v-model="item.relation" class="form-control filter-relation">
                        <option v-for="relation in relations" :value="relation.id" v-text="relation.name" :selected="relation.id == item.relation.id"></option>
                    </select>

                    <input type="text" v-model="item.value" @keyup.enter="onFilter()" class="form-control filter-value">
                    <button class="btn btn-danger filter-button" @click.prevent="onRemoveFilterItem(index)">
                        <span class="glyphicon glyphicon-remove"></span>
                    </button>
                </div>
            </div>
        </form>

        <div v-if="showFilter" class="form-group d-block">
            <button class="btn btn-success" @click.prevent="onFilter()">
                <span class="glyphicon glyphicon-search"></span>
            </button>
            <button class="btn btn-secondary" @click.prevent="onCloseFilter()">
                <span class="glyphicon glyphicon-ban-circle"></span>
            </button>
        </div>

        <div class="table-responsive">
            <table v-if="items.length > 0" class="table table-striped table-hover">
                <thead>
                <tr scope="row">
                    <th v-for="column, key in columns" v-if="!isHidden(key)" v-text="column" scope="col"></th>
                    <th v-if="Object.keys(actions).length > 0" scope="col" style="width:15%;">Műveletek</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in items" scope="row">
                    <td v-for="column, key in columns" v-if="!isHidden(key)" v-text="item[key]" scope="col"></td>
                    <td>
                        <button v-for="data, name in actions" v-text="data.label" class="btn" :class="getButtonClass(data.style)" @click.prevent="onClick(name, item)"></button>
                    </td>
                </tr>
                </tbody>
            </table>
            <div v-else>
                <p>Nincsenek elemek</p>
                <button class="btn btn-primary" @click.prevent="onRefreshClick()">Frissítés</button>
            </div>
        </div>

        <div v-if="items.length > 0" class="form-inline">
            Egy oldalon lévő elemek száma:
            <select class="form-control ml-2 mr-2" v-model="paging.itemsPerPage">
                <option v-for="n in paging.options" :value="n" :selected="paging.itemsPerPage == n" v-text="n"></option>
            </select>
            Oldalak:
            <select class="form-control ml-2 mr-2" v-model="paging.currentPage">
                <option v-for="n in availablePages" :value="n" :selected="paging.currentPage == n" v-text="n"></option>
            </select>
            Összes elem: <span v-text="paging.count"></span>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['collectionName', 'defaultFilters', 'customActions', 'customApiEndpoint'],

        data() {
            return {
                columns: [],
                hiddenColumns: [],
                items: [],
                actions: [],
                paging: {
                    itemsPerPage: 10,
                    currentPage: 1,
                    options: [10,20,50,100],
                    count: 0
                },
                relations: [],
                filters: {
                    currentColumn: -1,
                    currentRelation: -1,
                    currentValue: '',
                    items: []
                },
                showFilter: false,
                excludedFromFilters: []
            }
        },

        watch: {
            'paging.itemsPerPage': function (newValue, oldValue) {
                this.paging.currentPage = 1;
                this.fetch();
            },
            'paging.currentPage': function (newValue, oldValue) {
                this.fetch();
            }
        },

        computed: {
            availablePages: function () {
                const n = Math.ceil(this.paging.count / this.paging.itemsPerPage);
                return Array(n).fill().map((v, i) => i + 1);
            },

            apiEndponit: function () {
                if (this.customApiEndpoint) return this.customApiEndpoint;

                return `/api/${this.collectionName}/list`;
            }
        },

        created() {
            const callback = () => {
                if (this.filters.items.length === 0 && this.columns.hasOwnProperty('name')) {
                    this.filters.currentColumn = 'name';
                    this.filters.currentRelation = 5;
                }
            };
            this.fetch(callback);

            axios.get('/api/relations')
                .then(res => this.relations = res.data)
                .catch(err => flash('Hiba történt, a szűrők nem elérhetők', 'danger'));

            window.events.$on('item-was-restored', data => {
                this.onRemoveItem(data);
            });

            window.events.$on('item-was-deleted', data => {
                this.onRemoveItem(data);
            });

            window.events.$on('refresh-list', data => {
                if (data.list == this.collectionName) {
                    this.fetch();
                }
            });
        },

        methods: {
            onRefreshClick() {
                this.fetch(() => {});
            },

            fetch(callback = null) {
                const data = {
                    filters: this.filters.items.concat(this.defaultFilters).filter(x => x != undefined && x != null),
                    paging: this.paging
                };

                axios.post(this.apiEndponit, data)
                    .then(res => {
                        this.columns = res.data.columns;
                        this.items = res.data.items;
                        this.hiddenColumns = res.data.hiddenColumns;
                        this.paging.count = res.data.count;
                        this.excludedFromFilters = res.data.excludedFromFilters || [];

                        if (this.customActions) {
                            this.actions = this.customActions;
                        } else {
                            this.actions = res.data.actions;
                        }

                        if (callback) callback();
                    })
                    .catch(err => flash('Hiba történt az adatok lekérdezése során', 'danger'));
            },

            onRemoveItem(data) {
                if (this.collectionName === data.list) {
                    this.items = this.items.filter(x => x.id != data.item.id);
                    this.fetch();
                }
            },

            addFilter() {
                if (!this.validateCurrentFilter()) {
                    return;
                }

                this.filters.items.push({
                    column: this.filters.currentColumn,
                    relation: this.filters.currentRelation,
                    value: this.filters.currentValue
                });

                this.filters.currentColumn = -1;
                this.filters.currentRelation = -1;
                this.filters.currentValue = '';
            },

            onRemoveFilterItem(index) {
                this.filters.items = this.filters.items.filter((x,i) => i != index);
            },

            onCloseFilter() {
                this.showFilter = false;
                this.filters.items = [];

                this.fetch();
            },

            validateCurrentFilter() {
                return this.filters.currentColumn != -1 && this.filters.currentRelation != -1;
            },

            onFilter() {
                this.addFilter();
                this.fetch();
            },

            isExcludedFromFilters(column) {
                return this.excludedFromFilters.some(x => x == column);
            },

            isHidden(column) {
                return this.hiddenColumns.hasOwnProperty(column);
            },

            getButtonClass(className) {
                return {
                    'btn-primary': className === 'primary',
                    'btn-secondary': className === 'secondary',
                    'btn-danger': className === 'danger',
                    'btn-success': className === 'success'
                };
            },

            onClick(label, item) {
                window.events.$emit(`list-button-was-clicked`, {
                    list: this.collectionName,
                    button: label,
                    item: item
                });
            }
        }
    }
</script>

<style>
    button {
        margin-top: 5px !important;
        margin-right: 5px !important;
    }

    .filter-column {
        width: 234px !important;
    }

    .filter-relation {
        width: 234px !important;
    }

    .filter-value {
        width: 234px !important;
    }

    .filter-button {
        margin-top: 0 !important;
    }

    .current-filter-container {
        margin-top: 3px;
    }
</style>