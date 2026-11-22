@props(['name', 'size' => null])
<svg {{ $attributes->merge(['class' => 'i']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" @if($size) style="width:{{ $size }}px;height:{{ $size }}px" @endif>
@switch($name)
@case('home') <path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/> @break
@case('folder') <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/> @break
@case('check') <rect x="3" y="3" width="18" height="18" rx="4"/><path d="M8 12l3 3 5-6"/> @break
@case('tick') <path d="M5 12l5 5 9-11"/> @break
@case('upload') <path d="M12 16V4"/><path d="M7 9l5-5 5 5"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/> @break
@case('book') <path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z"/><path d="M4 21V5"/> @break
@case('file') <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h6"/> @break
@case('bell') <path d="M6 8a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/><path d="M10 20a2 2 0 0 0 4 0"/> @break
@case('user') <circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/> @break
@case('users') <circle cx="9" cy="8" r="3.5"/><path d="M2 20a7 7 0 0 1 14 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7"/><path d="M18 14.5A7 7 0 0 1 22 20"/> @break
@case('layers') <path d="M12 3l9 5-9 5-9-5z"/><path d="M3 13l9 5 9-5"/> @break
@case('flag') <path d="M5 21V4"/><path d="M5 4h12l-2 4 2 4H5"/> @break
@case('alert') <path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18h.01"/> @break
@case('chart') <path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/> @break
@case('settings') <circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/> @break
@case('shield') <path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/> @break
@case('database') <ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/> @break
@case('activity') <path d="M3 12h4l3-8 4 16 3-8h4"/> @break
@case('logout') <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/> @break
@case('plus') <path d="M12 5v14M5 12h14"/> @break
@case('eye') <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/> @break
@case('eye-off') <path d="M3 3l18 18"/><path d="M10.6 6.1A9.8 9.8 0 0 1 12 6c6 0 10 6 10 6a17 17 0 0 1-3.2 3.9"/><path d="M6.6 6.7C3.9 8.5 2 12 2 12s4 7 10 7c1.6 0 3-.4 4.3-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/> @break
@case('mail') <rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/> @break
@case('lock') <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/> @break
@case('cap') <path d="M12 3L2 8l10 5 10-5z"/><path d="M6 10.5V15c0 1.5 3 3 6 3s6-1.5 6-3v-4.5"/><path d="M22 8v6"/> @break
@case('download') <path d="M12 4v12"/><path d="M7 11l5 5 5-5"/><path d="M4 20h16"/> @break
@case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
@case('trash') <path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/> @break
@case('edit') <path d="M4 20h4L19 9l-4-4L4 16z"/> @break
@case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
@case('search') <circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/> @break
@case('target') <circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/> @break
@case('refresh') <path d="M20 11a8 8 0 0 0-14-4L4 9"/><path d="M4 4v5h5"/><path d="M4 13a8 8 0 0 0 14 4l2-2"/><path d="M20 20v-5h-5"/> @break
@case('send') <path d="M21 3L10 14"/><path d="M21 3l-7 18-4-7-7-4z"/> @break
@default <circle cx="12" cy="12" r="9"/>
@endswitch
</svg>
