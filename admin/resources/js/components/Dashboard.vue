<template>
    <div>
        <p v-if="partners.length === 0">Adatok betöltése folyamatban...</p>
        <table v-else class="table">
            <thead>
            <tr>
                <th scope="col" style="width:20%">Partner</th>
                <th scope="col">Felhasználói adatok</th>
                <th scope="col">Oldal betöltések</th>
            </tr>
            </thead>
            <tbody>
            <tr scope="row" v-for="partner in partners">
                <td scope="col">{{ partner.name }}</td>
                <td scope="col">{{ partner.user_data_count }}</td>
                <td scope="col">{{ partner.page_load_count }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                partners: [],
            }
        },

        created() {
            axios.get('/api/partners/user-data-statistics')
                .then(res => {
                    this.partners = res.data;
                })
                .catch(err => flash('Hiba történt az adatok lekérdezése során', 'danger'));
        },

        methods: {
        }
    }
</script>