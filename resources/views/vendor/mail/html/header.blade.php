@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel' || trim($slot) === 'Travclicks')
@php
    $logoSetting = \App\Helpers\CommonHelper::masterSettingsName('logo');
    $logo = $logoSetting['master_value'] ?? asset('images/logo.png');
@endphp
<img src="{{ $logo }}" class="logo" alt="Travclicks Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
