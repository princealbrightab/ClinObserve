@props(['name','label','options','value'=>null,'required'=>false])
<div class="mb-3"><label for="{{ $name }}" class="form-label">{{ $label }}</label><select name="{{ $name }}" id="{{ $name }}" class="form-select" @required($required)><option value="">Select…</option>@foreach($options as $key=>$text)<option value="{{ $key }}" @selected((string)old($name,$value)===(string)$key)>{{ $text }}</option>@endforeach</select></div>


