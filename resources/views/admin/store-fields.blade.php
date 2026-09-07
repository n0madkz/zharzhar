<input type="hidden" name="editor" value="{{ $editor }}">
<div class="form-grid">
@foreach($fields as $name => $field)
@php
$value = $name === 'theme' ? ($record?->config_json['theme'] ?? 'sage') : ($record?->$name ?? ($field['default'] ?? ''));
if ($name === 'expires_at' && $value) { $value = $value->format('Y-m-d\TH:i'); }
if (old('editor') === $editor) { $value = old($name, $value); }
@endphp
@if(isset($field['multiple_options']))
<fieldset class="field wide choice-field"><legend>{{ $field['label'] }}</legend><div class="choice-grid">
@foreach($field['multiple_options'] as $option => $label)<label class="check"><input type="checkbox" name="{{ $name }}[]" value="{{ $option }}" @checked(in_array((string)$option, (array)$value, true))> {{ $label }}</label>@endforeach
</div>@if(isset($field['hint']))<small>{{ $field['hint'] }}</small>@endif
@if(old('editor') === $editor)@error($name)<span class="error">{{ $message }}</span>@enderror @error($name.'.*')<span class="error">{{ $message }}</span>@enderror @endif
</fieldset>
@elseif(($field['type'] ?? null) === 'file')
<label class="field wide">{{ $field['label'] }}<input name="{{ $name }}" type="file" accept="{{ $field['accept'] ?? '' }}" @required(($field['required'] ?? false) && !$record)>
@if($record?->audio_url)<audio controls preload="none" src="{{ $record->audio_url }}" aria-label="Текущий аудиофайл"></audio><small>Если файл не выбран, сохранится текущая запись.</small>@elseif(isset($field['hint']))<small>{{ $field['hint'] }}</small>@endif
@if(old('editor') === $editor)@error($name)<span class="error">{{ $message }}</span>@enderror @endif
</label>
@else
<label class="field">{{ $field['label'] }}
@if(isset($field['options']))<select name="{{ $name }}">@foreach($field['options'] as $option => $label)<option value="{{ $option }}" @selected((string)$value === (string)$option)>{{ $label }}</option>@endforeach</select>
@else<input name="{{ $name }}" type="{{ $field['type'] ?? 'text' }}" value="{{ $value }}" @required($field['required'] ?? false) @if(isset($field['min'])) min="{{ $field['min'] }}" @endif @if(isset($field['max'])) max="{{ $field['max'] }}" @endif>@endif
@if(isset($field['hint']))<small>{{ $field['hint'] }}</small>@endif
@if(old('editor') === $editor)@error($name)<span class="error">{{ $message }}</span>@enderror @endif
</label>
@endif
@endforeach
</div>
<label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('editor') === $editor ? old('is_active', false) : ($record ? ($record instanceof \App\Models\Restaurant ? $record->status === 'active' : $record->is_active) : true))> Активен / виден клиентам</label>
<button class="button primary" type="submit">{{ $record ? 'Сохранить изменения' : 'Добавить' }}</button>
