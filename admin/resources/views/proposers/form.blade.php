{{ csrf_field() }}

<proposer-form inline-template :proposer="{{ json_encode($proposer) }}" :types="{{ json_encode($types) }}">
    <div>
        <div class="form-group">
            <label for="partner_id">Partner</label>
            <select name="partner_id" id="partner_id" class="form-control" required>
                <option value="">Kérlek válassz...</option>
                @foreach($partners as $partner)
                    <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id || $proposer->partner_id == $partner->id ? 'selected' : '' }}>
                        {{ $partner->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="title">Megnevezés</label>
            <input type="text" id="name" name="name" class="form-control"
                   value="{{ old('name') ?? $proposer->name }}" required>
        </div>
        <div class="form-group">
            <label for="description">Leírás</label>
            <textarea id="description" name="description" class="form-control">{{ old('description') ?? $proposer->description }}</textarea>
        </div>
        <div class="form-group">
            <label for="max_item_number">Maximálisan megjelenített itemek száma</label>
            <input type="number" id="max_item_number" name="max_item_number" class="form-control"
                   value="{{ old('max_item_number') ?? $proposer->max_item_number }}" required>
        </div>

        <div class="form-group">
            <label for="type_id">Típus</label>
            <select name="type_id" id="type_id" class="form-control" v-model="type" required>
                <option value="">Kérlek válassz...</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" {{ old('type_id') == $type->id || $proposer->type_id == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div v-if="!isIframe()" class="form-group">
            <label for="page_url">Aloldal URL</label>
            <input type="text" id="page_url" name="page_url" class="form-control"
                   value="{{ old('page_url') ?? $proposer->page_url }}" required>
        </div>

        <div v-if="isIframe()">
            <div class="form-group">
                <label for="width">Szélesség (px)</label>
                <input type="number" id="width" name="width" class="form-control"
                       value="{{ old('width') ?? $proposer->width }}" required>
            </div>
            <div class="form-group">
                <label for="height">Magasság (px)</label>
                <input type="number" id="height" name="height" class="form-control"
                       value="{{ old('height') ?? $proposer->height }}" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
        @include('layouts.errors')
    </div>
</proposer-form>
