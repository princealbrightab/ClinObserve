@props(['name','label'=>null,'value'=>null,'type'=>'text','required'=>false])
<div class="mb-3"><label class="form-label" for="{{ $name }}">{{ $label ?? str($name)->replace('_',' ')->title() }} @if($required)<span class="text-danger">*</span>@endif</label>
@if($type==='textarea')<textarea id="{{ $name }}" name="{{ $name }}" rows="4" {{ $attributes->class(['form-control','is-invalid'=>$errors->has($name)]) }} @required($required)>{{ old($name,$value) }}</textarea>
@else<input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type==='password'?'':old($name,$value) }}" {{ $attributes->class(['form-control','is-invalid'=>$errors->has($name)]) }} @required($required)>@endif
@error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>


