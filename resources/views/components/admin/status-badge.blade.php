@props(['status'])

<span {{ $attributes->class(['inline-flex w-fit rounded-full px-3 py-1 text-xs font-black ring-1 ring-inset', $status->badgeClasses()]) }}>{{ $status->label() }}</span>
