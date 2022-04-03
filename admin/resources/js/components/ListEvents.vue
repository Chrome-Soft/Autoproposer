<template>

</template>

<script>
    export default {
        data() {
            return {};
        },

        created() {
            window.events.$on('list-button-was-clicked', data => {
                const identifier = data.item.slug ? data.item.slug : data.item.id;

                if (data.list == 'pages' && data.button == 'edit') {
                    window.events.$emit('edit-page', data);
                    return;
                }

                switch (data.button) {
                    case 'view':
                        window.location.href = `/${data.list}/${identifier}`;
                        break;
                    case 'edit':
                        window.location.href = `/${data.list}/${identifier}/edit`;
                        break;
                    case 'restore':
                        this.restore(data.list, identifier, data.item);
                        break;
                    case 'delete':
                        this.delete(data.list, identifier, data.item)
                        break;
                }
            });
        },

        methods: {
            restore(list, identifier, item) {
                axios.patch(`/api/${list}/${identifier}/restore`)
                    .then(res => {
                        window.events.$emit('item-was-restored', {
                            list: list,
                            item: item
                        });

                        flash('Sikeres aktiválás');
                    })
                    .catch(err => flash('Hiba az aktiválás során', 'danger'));
            },

            delete(list, identifier, item) {
                axios.delete(`/api/${list}/${identifier}`)
                    .then(res => {
                        window.events.$emit('item-was-deleted', {
                            list: list,
                            item: item
                        });

                        flash('Sikeres törlés');
                    })
                    .catch(err => flash('Hiba a törlés során', 'danger'));
            }
        }
    }
</script>