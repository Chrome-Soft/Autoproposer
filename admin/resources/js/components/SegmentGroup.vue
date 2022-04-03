<template>
    <div class="group-container">
        <segment-group-criteria
                v-if="allCriterias.length > 0"
                v-for="(c, index) in group.criterias"
                :key="`criteria-${index}`"
                :all-criterias="allCriterias"
                :all-relations="allRelations"
                :need-bool-type="index !== group.criterias.length - 1"
                :index="index"
                :init-criteria="c"
                :init-relation="group.relations[index]"
                :init-bool-type="group.boolTypes[index]"
                :init-value="group.values[index]"
                :available-relation-map="availableRelationMap"
                @criteriaChanged="onCriteriaChange($event, index)"
                @relationChanged="onRelationChange($event, index)"
                @valueChanged="onValueChange($event, index)"
                @boolTypeChanged="onBoolTypeChange($event, index)"
                @criteriaRemoved="onCriteriaRemove($event, index)"
                @validationResultChanged="onValidationResultChanged($event, index)">
        </segment-group-criteria>

        <div class="form-group">
            <button class="btn btn-success" :disabled="!isValid" @click.prevent="addCriteria(group)">
                <span class="glyphicon glyphicon-plus"></span>
            </button>
        </div>

        <div class="form-group">
            <button class="btn btn-danger" @click.prevent="onRemove">Csoport törlése</button>
        </div>
    </div>
</template>

<script>
    import SegmentGroupCriteria from './SegmentGroupCriteria';

    class Criteria {

    }
    export default {
        props: ['group', 'allCriterias', 'allRelations', 'availableRelationMap'],
        components: { SegmentGroupCriteria },

        data() {
            return {
                validationResults: {},
                isValid: true
            }
        },

        watch: {
            isValid() {
                this.$emit('isValidChanged', this.isValid);
            }
        },

        methods: {
            key() {
                return this.group.criterias.length - 1;
            },

            addCriteria() {
                this.group.criterias.push(-1);
                this.group.relations.push(-1);
                this.group.values.push('');
            },

            optionName(option) {
                return `${option.name} - ${option.partner.name}`;
            },

            onRemove() {
                this.$emit('groupRemoved');
            },

            onCriteriaRemove(event, index) {
                const criterias = this.group.criterias;
                const relations = this.group.relations;
                const values = this.group.values;
                const boolTypes = this.group.boolTypes;

                this.group.criterias = [];
                this.group.relations = [];
                this.group.values = [];
                this.group.boolTypes = [];

                delete this.validationResults[index];
                this.calculateIsValid();

                // TODO HACK ME
                setTimeout(() => {
                    this.group.criterias = criterias.filter((x,i) => i !== index);
                    this.group.relations = relations.filter((x,i) => i !== index);
                    this.group.values = values.filter((x,i) => i !== index);
                    this.group.boolTypes = boolTypes.filter((x,i) => i !== index - 1);

                    if (this.group.criterias.length <= 1)
                        this.group.boolTypes = [];
                }, 0);
            },

            onCriteriaChange(criteriaId, index) {
                this.group.criterias[index] = criteriaId;
            },

            onRelationChange(relationId, index) {
                this.group.relations[index] = relationId;
            },

            onValueChange(value, index) {
                this.group.values[index] = value;
            },

            onBoolTypeChange(boolType, index) {
                this.group.boolTypes[index] = boolType;
            },

            onValidationResultChanged(validationResult, index) {
                if (validationResult.isValid) {
                    this.validationResults[index] = {
                        isValid: true,
                        messages: {}
                    };
                } else {
                    this.validationResults[index] = {
                        isValid: false,
                        messages: validationResult
                    };
                }

                this.calculateIsValid();
            },

            calculateIsValid() {
                this.isValid = true;
                for (const index in this.validationResults) {
                    if (!this.validationResults[index].isValid) {
                        this.isValid = false;
                        break;
                    }
                }
            }
        }
    }
</script>

<style>
    .group-container {
        background-color: rgb(230,245,252);
        padding: 10px;
        margin: 5px;
    }
</style>