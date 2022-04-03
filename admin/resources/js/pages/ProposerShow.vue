<script>
    import ProposerItems from '../components/ProposerItems';

    export default {
        props: ['proposer'],

        components: { ProposerItems },

        data() {
            return {};
        },

        methods: {
            onCopyClick() {
                const code = `<div class="kf-iframe-proposer" id="${this.proposer.slug}" style="width:${this.proposer.width}px;height:${this.proposer.height}px;"></div>`;
                this.copy(code);
                flash('A beágyazható HTML kód a vágolapra másolva!');
            },

            /**
             * navigator.clipboard nem működött éles környezetben...
             */
            copy(value) {
                const element = document.createElement('textarea');
                element.value = value;
                document.body.appendChild(element);
                element.select();
                document.execCommand('copy');
                document.body.removeChild(element);
            },

            isIframe() {
                return this.proposer.type_id == 1;
            }
        }
    }
</script>