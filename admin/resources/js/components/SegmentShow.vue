<script>
    export default {
        props: ['segment'],
        data() {
            return {}
        },

        methods: {
            onSegmentifyClick() {
                axios.post(`/api/segments/${this.segment.slug}/segmentify`)
                    .catch(err => console.log(err));
            },

            onReplicateClick() {
                axios.post(`/api/segments/${this.segment.slug}/replicate`)
                    .then(res => window.location.href = `/segments/${res.data.slug}`)
                    .catch(err => console.log(err));
    }
        }
    }
</script>