<template>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2><span v-text="type"></span> adatok</h2>
        </div>

        <div class="panel-body">
            <table class="table table-striped table-hover">
                <tr v-for="field in details.fields" scope="row">
                    <th v-text="details.data[field].label" scope="col"></th>
                    <td v-text="details.data[field].value" scope="col"></td>
                </tr>
            </table>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['details', 'type'],
        data() {
            return {}
        },

        methods: {

        }
    }
</script>

<style>

</style>