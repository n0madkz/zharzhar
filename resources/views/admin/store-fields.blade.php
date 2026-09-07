<input type="hidden" name="editor" value="{{ $editor }}">
<div class="form-grid">
@foreach($fields as $name => $field)
@php
$value = $name === 'theme' ? ($record?->config_json['theme'] ?? 'sage') : ($record?->$name ?? ($field['default'] ?? ''));
if ($name === 'expires_at' && $value) { $value = $value->format('Y-m-d\TH:i'); }
if (old('editor') === $editor) { $value = old($name, $value); }
@endphp
<label class="field">{{ $field['label'] }}
@if(isset($field['options']))<select name="{{ $name }}">@foreach($field['options'] as $option => $label)<option value="{{ $option }}" @selected((string)$value === (string)$option)>{{ $label }}</option>@endforeach</select>
@else<input name="{{ $name }}" type="{{ $field['type'] ?? 'text' }}" value="{{ $value }}" @required($field['required'] ?? false) @if(isset($field['min'])) min="{{ $field['min'] }}" @endif @if(isset($field['max'])) max="{{ $field['max'] }}" @endif>@endif
@if(isset($field['hint']))<small>{{ $field['hint'] }}</small>@endif
@if(old('editor') === $editor)@error($name)<span class="error">{{ $message }}</span>@enderror @endif
</label>@endforeach
</div>
<label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('editor') === $editor ? old('is_active', false) : ($record ? ($record instanceof \App\Models\Restaurant ? $record->status === 'active' : $record->is_active) : true))> Активен / виден клиентам</label>
<button class="button primary" type="submit">{{ $record ? 'Сохранить изменения' : 'Добавить' }}</button>
