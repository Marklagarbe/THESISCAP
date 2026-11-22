@props(['user', 'lg' => false])
<span class="avatar {{ $lg ? 'lg' : '' }}" title="{{ $user->name ?? '' }}">{{ $user ? $user->initials() : '?' }}</span>
