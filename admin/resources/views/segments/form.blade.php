{{ csrf_field() }}

<segment-form inline-template :segment="{{ json_encode($segment) }}">
    <div>
        <div class="form-group">
            <label for="name">Név</label>
            <input v-model="name" type="text" id="name" name="name" class="form-control"
                   value="{{ old('name') ?? $segment->name }}" required>
        </div>
        <div class="form-group">
            <label for="description">Leírás</label>
            <textarea v-model="description" class="form-control" name="description" id="description" rows="5" required>{{ old('description') ?? $segment->description }}</textarea>
        </div>
        <div class="form-group">
            <label for="appearance_template">Sablon</label>
            <select name="template_id" id="template_id" class="form-control" v-model="templateId">
                <option value="-1">Ne legyen egyedi sablon használva</option>
                @foreach ($appearanceTemplates as $template)
                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                @endforeach
            </select>

            <view-appearance-template v-show="template !== null" :template="template" :storage-url="{{ json_encode(asset('images')) }}"></view-appearance-template>
        </div>

        <segment-groups
                v-if="shouldShowGroups()"
                :all-relations="{{ json_encode($relations) }}"
                :all-criterias="{{ json_encode($criterias) }}"
                :init-groups="this.groups"
                :init-bool-types="this.boolTypes"
                :available-relation-map="{{ json_encode($availableRelationMap) }}"
                @group-added="onGroupAdded"
                @group-removed="onGroupRemoved"
                @is-valid-changed="onIsValidChanged($event)">
        </segment-groups>

        <button type="submit" class="btn btn-success" :disabled="!isValid" @click.prevent="onSave">Mentés</button>

        @include('layouts.errors')
    </div>
</segment-form>