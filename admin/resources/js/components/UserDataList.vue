<template>
    <div>
        <h2>Felhasználói adatok</h2>

        <div class="form-group">
            <label for="from">Kezdő időpont</label>
            <date-picker id="from" v-model="from" :config="options"></date-picker>
        </div>
        <div class="form-group">
            <label for="to">Záró időpont</label>
            <date-picker id="to" v-model="to" :config="options"></date-picker>
        </div>
        <div class="form-group">
            <button class="btn btn-primary" @click="fetch">Adatok lekérdezése</button>
        </div>

        <p>Összes elem: <span v-text="sum"></span></p>

        <table class="table">
            <thead>
                <tr>
                    <th scope="col" v-for="c in criterias" v-text="c.name"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in items">
                    <td v-for="c in criterias" scope="row" v-text="item[c.slug]"></td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import 'bootstrap/dist/css/bootstrap.css';
    import datePicker from 'vue-bootstrap-datetimepicker';
    import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';

    export default {
        components: { datePicker },
        props: ['segmentSlug'],

        data() {
            return {
                items: [],
                criterias: [],
                sum: 0,
                from: null,
                to: null,
                options: {
                    format: 'YYYY-MM-DD',
                    useCurrent: false,
                }
            }
        },

        created() {
            axios.get('/api/criterias')
                .then(res => this.criterias = res.data);
        },

        methods: {
            fetch() {
                this.normalizeDates();

                axios.get(`/api/segments/${this.segmentSlug}/user-data?from=${this.from}&to=${this.to}`)
                    .then(res => {
                        this.items = res.data;
                        this.sum = res.data.length;
                    })
                    .catch(err => {
                        console.log(err);
                    });
            },

            normalizeDates() {
                if (this.from && !this.to) {
                    this.to = moment(this.from).add(1, 'month').format('YYYY-MM-DD');
                }

                if (!this.from && this.to) {
                    this.from = moment(this.to).subtract(1, 'month').format('YYYY-MM-DD');
                }

                if (!this.from && !this.to) {
                    this.from = moment().subtract(1, 'month').format('YYYY-MM-DD');
                    this.to = moment().format('YYYY-MM-DD');
                }
            }
        }
    }
</script>