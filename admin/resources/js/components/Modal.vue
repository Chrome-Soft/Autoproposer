<template>
    <div class="modal show" :id="id" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <slot name="header">Megerősítés</slot>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <slot name="body">Biztosan törölni szeretnéd?</slot>
                </div>
                <div class="modal-footer">
                    <slot name="footer">
                        <button type="button" class="btn btn-primary">Igen</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Mégsem</button>
                    </slot>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: ['id']
    }
</script>