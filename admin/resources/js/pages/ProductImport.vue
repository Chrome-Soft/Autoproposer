<script>
    export default {
        data() {
            return {
                inProgress: false,
                stat: {}
            };
        },

        methods: {
            failedProductText(item) {
                const errors = item.errors.join('. ');
                return `Termék: ${item.product.name}. Hiba: ${errors}`;
            },

            productUrl(product) {
                return `/products/${product.slug}`;
            },

            click() {
                this.inProgress = true;

                axios.post('/api/products/import')
                    .then(res => {
                        this.inProgress = false;
                        this.stat = res.data;

                        if (this.stat.failedProducts.length === 0) {
                            flash('Sikeres importálás');
                        } else {
                            flash('A művelet véget ért, de vannak olyan termékek amiket nem lehetett importálni', 'danger');
                        }
                    })
                    .catch(err => {
                        this.inProgress = false;
                        flash('Sikertelen importálás', 'danger');
                        console.log(err);
                    });

            }
        }
    }
</script>