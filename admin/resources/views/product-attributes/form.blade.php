{{ csrf_field() }}

<div class="form-group">
    <label for="name">Megnevezés</label>
    <input type="text" id="name" name="name" class="form-control"
           value="{{ old('name') ?? $productAttribute->name }}" required>
</div>

<div class="form-group">
    <label for="type_id">Típus</label>
    <select name="type_id" id="type_id" class="form-control">
        @foreach ($attributeTypes as $type)
            <option value="{{ $type->id }}" {{ old('type_id') == $type->id || $productAttribute->type_id == $type->id ? 'selected' : '' }}>
                {{ $type->name }}
            </option>
        @endforeach
    </select>
</div>

<button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
@include('layouts.errors')
