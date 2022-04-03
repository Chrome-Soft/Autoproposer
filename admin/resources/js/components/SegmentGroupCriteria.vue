<template>
    <div>
        <div class="d-flex flex-row-reverse">
            <button class="btn btn-danger remove-criteria" @click.prevent="onCriteriaRemove()">
                <span class="glyphicon glyphicon-remove"></span>
            </button>
        </div>

        <div class="form-group">
            <select name="criteria[]" class="form-control" @change="onCriteriaChange()" v-model="criteria">
                <option value="-1">Kérlek válassz...</option>
                <option v-for="c in allCriterias" :value="c.id" v-text="c.name"></option>
            </select>
        </div>

        <div class="form-group">
            <select name="relation[]" class="form-control" @change="onRelationChange()" v-model="relation">
                <option value="-1">Kérlek válassz...</option>
                <option v-for="r in availableRelations" :value="r.id" v-text="r.name"></option>
            </select>
        </div>

        <div v-if="!options || options.length == 0" class="form-group">
            <textarea name="value[]" rows="3" class="form-control" @input="onValueChange()" v-model="value.value"></textarea>
        </div>
        <div v-else v-for="e in properties.elems" class="form-group">
            <select :name="e.name" v-model="value[e.name]" @change="onValueChange()" class="form-control">
                <option v-for="o in options[e.name]" :value="o.id" v-text="`${o.name} - ${o.partner.name}`"></option>
            </select>
        </div>
        <div v-if="!validationResult.isValid"
             class="alert alert-danger"
             role="alert">
            <ul>
                <li v-for="message in validationResult.messages" v-text="message"></li>
            </ul>
        </div>

        <div class="form-group" v-if="needBoolType">
            <select :name="boolTypeName()" class="form-control" @change="onBoolTypeChange()" v-model="boolType">
                <option value="-1">Kérlek válassz...</option>
                <option value="or">VAGY</option>
                <option value="and">ÉS</option>
            </select>
        </div>
    </div>
</template>

<script>
    import SegmentValidator from '../services/SegmentValidator';

    export default {
        props: ['allCriterias', 'allRelations', 'needBoolType', 'index', 'initCriteria', 'initRelation', 'initValue', 'initBoolType', 'availableRelationMap'],
        data() {
            return {
                criteria: null,
                relation: null,
                value: {},
                boolType: null,
                properties: null,
                options: null,
                availableRelations: [],
                validator: null,
                validationResult: {
                    isValid: true,
                    messages: {}
                }
            }
        },

        created() {
            this.criteria = this.initCriteria;
            this.relation = this.initRelation;
            this.boolType = this.initBoolType;

            if (isNaN(parseInt(this.criteria))) {
                this.availableRelations = this.allRelations;
            } else {
                this.getAvailableRelations();
            }

            const normalizedValue = this.parseValue(this.initValue);
            this.value = normalizedValue ? normalizedValue : { value: this.initValue };

            this.getProperties();

            this.validator = new SegmentValidator;
        },

        watch: {
            properties(newValue, oldValue) {
                if (newValue == null) {
                    this.options = null;
                    return;
                }

                axios.get(`/api/criterias/${this.criteria}/options`)
                    .then(res => this.options = res.data);
            }
        },

        methods: {
            boolTypeName() {
                return `bool_type_criteria_${this.index}`;
            },

            onCriteriaRemove() {
                this.$emit('criteriaRemoved', this.index);
            },

            onCriteriaChange() {
                this.$emit('criteriaChanged', this.criteria);

                this.getAvailableRelations();
                this.getProperties();

                this.clearValue();
                this.resetValidationResult();
            },

            onRelationChange() {
                this.$emit('relationChanged', this.relation);

                const relation = this.allRelations.find(x => x.id == this.relation);
                if (relation.symbol == 'IS NULL' || relation.symbol == 'IS NOT NULL') {
                    this.clearValue();
                    this.resetValidationResult();
                }
            },

            onValueChange() {
                // Ha szimpla érték, akkor csak azt küldi, egyébként az egész objektumot
                const value = this.value.value != null ? this.value.value : this.value;
                this.$emit('valueChanged', value);

                const validators = _.get(this.properties, 'validators', null);

                if (validators) {
                    this.validationResult = this.validator.validate(this.getCriteria(), value, validators);
                    this.$emit('validationResultChanged', this.validationResult);
                }
            },

            onBoolTypeChange() {
                this.$emit('boolTypeChanged', this.boolType);
            },

            clearValue() {
                /**
                 * Ha olyan kritériumról van szó, amihez tartoznak propertiek (pl meglátogatott URL), akkor üres objectre kell állítani
                 * Ez azért kell, mert különben
                 *
                 * {
                 *     page_id: 1,
                 *     value: ''
                 * }
                 *
                 * fog küldeni API -nak, az pedig visszadobja validációs hibával.
                 */
                this.value = this.properties ? {} : { value: '' };
                this.$emit('valueChanged', '');
            },

            resetValidationResult() {
                this.validationResult.isValid = true;
                this.validationResult.messages = {};
                this.validator.clearMessages();
                this.$emit('validationResultChanged', this.validationResult);
            },

            getAvailableRelations() {
                this.availableRelations = this.availableRelationMap.hasOwnProperty(this.criteria)
                    ? this.availableRelationMap[this.criteria]
                    : this.allRelations;
            },

            getProperties() {
                const criteria = this.getCriteria();
                this.properties = this.parseValue(_.get(criteria, 'properties'));
            },

            getCriteria() {
                return this.allCriterias.find(x => x.id == this.criteria);
            },

            // TODO model casts
            parseValue(value) {
                try {
                    const parsed = JSON.parse(value);

                    // Ha egy sima számos érték, pl 8 szerepel a value -ban, akkor null -t akarunk visszaadni
                    // Csak akkor adjuk vissza a parsed értéket, ha az tényleg json objektum
                    if (_.isNumber(parsed))
                        return null;

                    return parsed;
                } catch(err) {
                    return null;
                }
            },
        }
    }
</script>

<style>
    .remove-criteria { margin-bottom:14px; }
</style>