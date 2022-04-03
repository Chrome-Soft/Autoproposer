<script>
    export default {
        props: ['proposer', 'types'],

        data() {
            return {
                type: null
            }
        },

        created() {
            this.type = this.proposer ? this.proposer.type_id : null;
        },

        methods: {
            isIframe() {
                return this.type == 1;
            }
        }
    }
</script>