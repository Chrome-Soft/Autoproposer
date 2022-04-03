<template>
    <div>
        <button class="btn btn-primary" @click.prevent="add">Új attribútum</button>

        <div v-for="initAttr in initSelectedAttributes">
            <div class="form-group">
                <select name="attribute_ids[]" class="form-control" v-model="initAttr.id">
                    <option value="-1">Kérlek válassz...</option>
                    <option v-for="a in allAttributes" :value="a.id" v-text="a.name" :selected="a.id == initAttr.id"></option>
                </select>

                <input
                        v-if="initAttr.type.properties.elemType == 'input' && initAttr.type.properties.inputType != 'checkbox'"
                        :type="initAttr.type.properties.inputType"
                        :class="initAttr.type.properties.classes"
                        required="true" class="form-control product-attribute" name="attribute_values[]"
                        :value="
                            Array.isArray(initAttr.pivot.value)
                                ? initAttr.pivot.value[0]
                                : initAttr.pivot.value
                        ">

                <input
                        v-if="initAttr.type.properties.elemType == 'input' && initAttr.type.properties.inputType == 'checkbox'"
                        :class="initAttr.type.properties.classes"
                        type="checkbox" class="form-control product-attribute" name="attribute_values[]"
                        :checked="initAttr.pivot.value == 'Igen'">

                <input
                        v-if="initAttr.type.properties.inputType == 'checkbox'"
                        :value="initAttr.pivot.value == 'Nem' ? 'off' : 'on'"
                        type="hidden" name="attribute_values[]">

                <input
                        v-if="initAttr.type.properties.elemType == 'input' && initAttr.type.properties.numberOfElems == 2"
                        :type="initAttr.type.properties.inputType"
                        :class="initAttr.type.properties.classes"
                        :value="initAttr.pivot.value[1]"
                        class="form-control" name="attribute_values[]" required>

                <textarea required
                          v-if="initAttr.type.properties.elemType == 'textarea'"
                          :class="initAttr.type.properties.classes"
                          v-text="initAttr.pivot.value"
                          class="form-control" rows="5" name="attribute_values[]"></textarea>

                <button class="btn btn-danger" @click.prevent="removeInit(initAttr)">Törlés</button>
            </div>
        </div>

        <div v-for="(selectedId, index) in selectedIds">
            <div class="form-group">
                <select name="attribute_ids[]" class="form-control" v-model="selectedId.id">
                    <option value="-1">Kérlek válassz...</option>
                    <option v-for="a in allAttributes" :value="a.id" v-text="a.name"></option>
                </select>

                <div v-if="selectedAttributes[selectedId.id] != undefined">
                    <input
                            v-if="selectedAttributes[selectedId.id].type.properties.elemType == 'input'"
                            :type="selectedAttributes[selectedId.id].type.properties.inputType"
                            :class="selectedAttributes[selectedId.id].type.properties.classes"
                            :required="selectedAttributes[selectedId.id].type.properties.inputType != 'checkbox'"
                            class="form-control product-attribute" name="attribute_values[]">

                    <input
                            v-if="selectedAttributes[selectedId.id].type.properties.inputType == 'checkbox'"
                            type="hidden" value="off" name="attribute_values[]">

                    <input
                            v-if="selectedAttributes[selectedId.id].type.properties.elemType == 'input' && selectedAttributes[selectedId.id].type.properties.numberOfElems == 2"
                           :type="selectedAttributes[selectedId.id].type.properties.inputType"
                           :class="selectedAttributes[selectedId.id].type.properties.classes"
                           class="form-control" name="attribute_values[]" required>

                    <textarea required
                            v-if="selectedAttributes[selectedId.id].type.properties.elemType == 'textarea'"
                            :class="selectedAttributes[selectedId.id].type.properties.classes"
                            class="form-control" rows="5" name="attribute_values[]"></textarea>
                </div>

                <button class="btn btn-danger" @click.prevent="remove(index)">Törlés</button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['allAttributes', 'initSelectedAttributesProp'],

        data () {
            return {
                selectedIds: [],
                selectedAttributes: {},
                initSelectedAttributes: []
            };
        },

        created() {
            this.initSelectedAttributes = this.initSelectedAttributesProp;
        },

        watch: {
            selectedIds: {
                deep: true,
                handler(newVals) {
                    const newIds = newVals.map(x => x.id);
                    if (newIds.find(x => x == -1) != null) return;

                    this.syncSelectedAttributes(newIds);
                }
            }
        },

        methods: {
            removeInit(initAttr) {
                this.initSelectedAttributes = this.initSelectedAttributes.filter(x => x.id != initAttr.id);
            },

            hasInit(id) {
                const sameIds = this.initSelectedAttributes.filter(x => x.id == id);
                return sameIds.length > 0;
            },

            add() {
                // TODO flash message
                if (this.has(-1)) return;

                this.selectedIds.push({id: -1});
            },

            remove(index) {
                this.selectedIds.splice(index, 1);
            },

            has(id) {
                const sameIds = this.selectedIds.filter(x => x.id == id);
                return sameIds.length > 0;
            },

            syncSelectedAttributes(selectedIds) {
                this.selectedAttributes = {};

                for (let i = 0; i < selectedIds.length; i++) {
                    const id = selectedIds[i];
                    const attr = this.allAttributes.find(x => x.id == id);
                    if (!attr) continue;

                    this.selectedAttributes[id] = attr;
                }
            }
        }
    }
</script>