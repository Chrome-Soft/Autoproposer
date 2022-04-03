{{ csrf_field() }}

<input type="hidden" name="proposer_id" value="{{ $proposer->id }}">
@if($proposerItem->id)
<input type="hidden" name="type" value="{{ $proposerItem->type->key }}">
@endif

<div class="form-group">
    <label for="type">Típus</label>
    <select name="type" id="type" class="form-control" required {{ $proposerItem->id ? "disabled" : ""  }}>
        <option value="-1">Kérlek válassz...</option>
        @foreach($proposerItemTypes as $type)
            <option value="{{ $type->key }}" {{ old('type') == $type->id || $proposerItem->type_id == $type->id ? 'selected' : '' }}>
                {{ $type->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group d-none dynamic-content" id="html-content">
    <label for="html_content">HTML tartalom</label>
    <textarea name="html_content" id="html_content" class="form-control" rows="10">{{ $proposerItem->html_content }}</textarea>
</div>
<div class="form-group d-none dynamic-content" id="image-upload">
    <div class="form-group">
        <label for="image">Képfeltölés</label>
        <input type="file" name="image" id="image" class="form-control">
        @if($proposerItem->medium_photo)
            <img src="{{ Storage::url($proposerItem->medium_photo->image_path) }}" alt="">
        @endif
    </div>
    <div class="form-group">
        <label for="link">Hivatkozás</label>
        <input type="text" class="form-control" id="link" name="link" value="{{ old('link') ?? $proposerItem->link }}">
    </div>
</div>
<div class="form-group d-none dynamic-content" id="product-selector">
    <label for="product_id">Termék</label>
    <select name="product_id" id="product_id" class="form-control" required>
        <option value="-1">Kérlek válassz...</option>
        @foreach($products as $product)
            <option value="{{ $product->id }}" {{ old('product_id') == $product->id || $proposerItem->product_id == $product->id ? 'selected' : '' }}>
                {{ $product->name }}
            </option>
        @endforeach
    </select>
</div>

<button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
@include('layouts.errors')
