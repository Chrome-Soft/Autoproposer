<script>
    import ProductGroups from '../components/SegmentGroups';
    import _ from 'lodash';

    export default {
        props: ['segment'],
        components: {ProductGroups},

        data() {
            return {
                name: '',
                description: '',
                templateId: -1,
                template: null,
                groups: [],
                boolTypes: [],
                isValid: true
            }
        },

        created() {
            this.convertSegmentToData();
        },

        watch: {
            templateId: function (newId) {
                if (!newId) return;

                axios.get('/api/segment-appearance-templates/' + newId)
                    .then((res) => this.template = res.data);
            }
        },

        methods: {
            onSave() {
                let successMsg = 'Sikeres létrehozás';
                let failureMsg = 'Sikertelen létrehozás';
                let url = '/api/segments';
                let httpType = 'post';

                if (this.segment.id) {
                    successMsg = 'Sikeres szerkesztés';
                    failureMsg = 'Sikertelen szerkesztés';
                    url = `/api/segments/${this.segment.slug}`;
                    httpType = 'patch';
                }

                axios[httpType](url, this.convertDataToApi())
                    .then(res => {
                        flash(successMsg);
                        window.location.href = `/segments/${res.data.slug}`;
                    })
                    .catch(err => {
                        validationError(err);
                    });
            },

            onGroupAdded(data) {
                this.groups = data.groups;
                this.boolTypes = data.boolTypes;
            },

            onGroupRemoved(data) {
                this.groups = data.groups;
                this.boolTypes = data.boolTypes;
            },

            onIsValidChanged(isValid) {
                this.isValid = isValid;
            },

            shouldShowGroups() {
                console.log('should show');
                console.log(this.segment.is_default);
                return _.get(this.segment, 'is_default', 0) === 0;
            },

            /**
             * Komponens adatait konvertálja API -nak megfelelő formátumra
             * @returns {{name: string, description: string, groups: Array}}
             */
            convertDataToApi() {
                const newGroups = [];
                for (const groupIndex in this.groups) {
                    const group = this.groups[groupIndex];

                    const newGroup = {
                        criterias: [],
                        bool_type: this.boolTypes[groupIndex]
                    };
                    const criterias = [];

                    for (const index in group.criterias) {
                        const value = group.values[index] && group.values[index].value
                            ? group.values[index].value
                            : group.values[index];

                        criterias.push({
                            criteria: group.criterias[index],
                            relation: group.relations[index],
                            value: value,
                            bool_type: group.boolTypes[index]
                        });
                    }

                    newGroup.criterias = criterias;
                    newGroups.push(newGroup);
                }

                return {
                    name: this.name,
                    description: this.description,
                    template_id: this.templateId,
                    groups: newGroups
                };
            },

            /**
             * Szervertől kapott, updatelni kívánt szegmenst konvertálja a komponens data -jának megfelelő formátumra
             */
            convertSegmentToData() {
                if (!this.segment.id) {
                    return;
                }

                this.name = this.segment.name;
                this.description = this.segment.description;
                this.templateId = this.segment.template_id;
                this.boolTypes = [];

                const groups = [];
                for (const group of this.segment.groups) {
                    groups.push({
                        boolTypes: group.criterias.map(x => x.pivot.bool_type),
                        criterias: group.criterias.map(x => x.id),
                        relations: group.criterias.map(x => x.pivot.relation_id),
                        values: group.criterias.map(x => x.pivot.value)
                    });

                    this.boolTypes.push(group.bool_type);
                }

                this.groups = groups;
            }
        }
    }
</script>