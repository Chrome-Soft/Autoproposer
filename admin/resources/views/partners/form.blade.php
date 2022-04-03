{{ csrf_field() }}

<div class="form-group">
    <label for="name">Név</label>
    <input type="text" id="name" name="name" class="form-control"
           value="{{ old('name') ?? $partner->name }}" required>
</div>
<div class="form-group">
    <label for="title">URL</label>
    <input type="text" id="url" name="url" class="form-control"
           value="{{ old('url') ?? $partner->url }}" required>
</div>
<div class="form-group">
    <label for="is_anonymus_domain">Névtelen domain?</label>
    <input type="checkbox" id="is_anonymus_domain" name="is_anonymus_domain" class="form-control"
           @if (old('is_anonymus_domain') || $partner->is_anonymus_domain)
           checked
            @endif>
</div>

<button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
@include('layouts.errors')
