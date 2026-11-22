@props(['value' => 0])
<div class="bar-row"><div class="bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $value }}"><span style="width: {{ $value }}%"></span></div><b>{{ $value }}%</b></div>
