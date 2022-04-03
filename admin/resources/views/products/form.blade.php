{{ csrf_field() }}

<product-form inline-template>
    <div>
        <input type="hidden" value="{{ $product->slug }}" name="slug">
        <div class="form-group">
            <label for="name">Megnevezés</label>
            <input type="text" id="name" name="name" class="form-control"
                   value="{{ old('name') ?? $product->name }}" required>
        </div>
        <div class="form-group">
            <label for="description">Leírás</label>
            <textarea name="description" id="description" class="form-control">{{ old('description') ?? $product->description }}</textarea>
        </div>
        <div class="form-group">
            <label for="link">Hivatkozás</label>
            <input type="text" id="link" name="link" class="form-control"
                   value="{{ old('link') ?? $product->link }}" required>
        </div>

        <h2>Attribútumok megadása</h2>
        <product-attributes :all-attributes="{{ $attributes }}" :init-selected-attributes-prop="{{ $product->attributes }}"></product-attributes>

        <h2>Árak megadása</h2>
        @foreach ($currencies as $i => $currency)
            <div class="form-group">
                <label for="price-{{ $currency->code }}">{{ $currency->name }} ({{ $currency->code }})</label>
                <input type="number" class="form-control" id="price-{{ $currency->code }}" name="prices[]" value="{{ old('prices')[$i] ?? (Arr::get($prices, $currency->id, '')) }}">
            </div>
            <input type="hidden" name="currencies[]" value="{{ $currency->id }}">
        @endforeach

        <h2>Kép feltölése</h2>
        <div class="form-group">
            <input type="file" class="form-control" name="photos[]">
        </div>

        <div>
            <img src="{{ optional($product->medium_photo)->public_path }}" alt="" style="width:200px;height:200px;">
        </div>
{{--        <product-photos :product="{{ $product }}" :init-photo="{{ $product ? $product->medium_photo : [] }}" :can-update="{{ json_encode($canUpdate) }}"></product-photos>--}}
        <br>

        <button type="submit" class="btn btn-primary">{{ $buttonText }}</button>

        @include('layouts.errors')
    </div>
</product-form>