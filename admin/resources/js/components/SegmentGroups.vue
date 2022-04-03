<template>
    <div>
        <h2>Kritériumok</h2>

        <div v-for="(group, index) in groups">
            <segment-group
                    :group="group"
                    :all-criterias="allCriterias"
                    :all-relations="allRelations"
                    :available-relation-map="availableRelationMap"
                    @groupRemoved="remove(index)"
                    @isValidChanged="onIsValidChanged($event, index)">
            </segment-group>

            <div class="form-group" v-if="index !== groups.length -1">
                <select :name="boolTypeName(index)" class="form-control" v-model="boolTypes[index]">
                    <option value="-1">Kérlek válassz...</option>
                    <option value="or">VAGY</option>
                    <option value="and">ÉS</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <button class="btn btn-success" :disabled="!isValid" @click.prevent="add">Új csoport</button>
        </div>
    </div>
</template>

<script>
    import SegmentGroup from './SegmentGroup';

    class Group {
        constructor() {
            this.criterias = [-1];
            this.relations = [-1];
            this.values = [''];
            this.boolTypes = [''];
        }
    }
    export default {
        props: ['initGroups', 'initBoolTypes', 'allRelations', 'allCriterias', 'availableRelationMap'],
        components: { SegmentGroup },

        data() {
            return {
                groups: [],
                boolTypes: [],
                validationResults: {},
                isValid: true
            }
        },

        created() {
            if (this.initGroups) {
                this.groups = this.initGroups;
                this.boolTypes = this.initBoolTypes;
            }
        },

        methods: {
            add() {
                this.groups.push(new Group);
                this.$emit('group-added', { groups: this.groups, boolTypes: this.boolTypes } );
            },

            remove(index) {
                this.groups = this.groups.filter((x,i) => i !== index);
                this.boolTypes = this.boolTypes.filter((x,i) => i !== index - 1);
                this.$emit('group-removed', { groups: this.groups, boolTypes: this.boolTypes } );
            },

            onIsValidChanged(isValid, index) {
                // this.isValid = isValid;
                this.validationResults[index] = isValid;

                this.isValid = true;
                for (const index in this.validationResults) {
                    if (!this.validationResults[index]) {
                        this.isValid = false;
                        break;
                    }
                }

                this.$emit('is-valid-changed', isValid);
            },

            boolTypeName(index) {
                return `bool_type_group_${index}`;
            }
        }
    }
</script>