@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="http://api.agapehospital.com.ng/pmall-logo.jpeg" class="logo" alt="Pmall Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
