@php
    $adminNotifCount = $adminNotifications->count();
@endphp

@foreach($adminNotifications as $item)
    <div class="notif-item notif-unread p-3.5 transition flex items-start gap-3 relative cursor-pointer group"
         data-notif-id="{{ $item['id'] }}"
         data-notif-type="{{ $item['type'] }}"
         onclick="handleAdminNotificationClick('{{ $item['id'] }}', '{{ $item['url'] }}')">

        <div class="notif-unread-dot w-2.5 h-2.5 rounded-full bg-rose-500 mt-1 shrink-0 transition"></div>

        <div class="status-icon-container w-9 h-9 rounded-xl {{ $item['icon_bg'] }} flex items-center justify-center shrink-0 text-sm shadow-2xs group-hover:scale-105 transition">
            <i class="{{ $item['icon_class'] }}"></i>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-1.5 mb-1">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-[9px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                        <i class="{{ $item['category_icon'] }} text-[9px] mr-0.5"></i> {{ $item['category'] }}
                    </span>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded border {{ $item['status_badge_class'] }}">
                        {{ $item['status_label'] }}
                    </span>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="text-[10px] text-slate-400 font-medium flex items-center gap-1">
                        <i class="fa-regular fa-clock text-[9px]"></i> {{ $item['formatted_time'] }}
                    </span>
                    <button type="button"
                            class="btn-delete-notif w-5 h-5 rounded hover:bg-rose-100 text-slate-400 hover:text-rose-600 flex items-center justify-center transition cursor-pointer text-[10px]"
                            title="Hapus notifikasi ini"
                            aria-label="Hapus notifikasi ini"
                            onclick="event.stopPropagation(); window.deleteAdminNotification('{{ $item['id'] }}', event);">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
            </div>

            <h4 class="text-xs font-bold text-slate-900 group-hover:text-[#0b7c89] transition leading-snug">
                {{ $item['title'] }}
            </h4>

            <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                {!! $item['message'] !!}
            </p>

            <div class="mt-2.5 flex items-center justify-between">
                <span class="notif-action-link text-[10px] font-bold text-[#0b7c89] group-hover:text-[#065b65] inline-flex items-center gap-1">
                    <span>{{ $item['url_label'] }}</span>
                    <i class="fa-solid fa-arrow-right text-[9px] transition-transform group-hover:translate-x-0.5"></i>
                </span>
                @if(!empty($item['reference_no']))
                    <span class="text-[9px] font-mono text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">
                        {{ $item['reference_no'] }}
                    </span>
                @endif
            </div>
        </div>
    </div>
@endforeach

<div class="p-8 text-center {{ $adminNotifCount > 0 ? 'hidden' : '' }}" id="admin-notif-empty">
    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2.5 text-lg">
        <i class="fa-regular fa-bell-slash"></i>
    </div>
    <h4 class="text-xs font-bold text-slate-700">Belum Ada Pemberitahuan Baru</h4>
    <p class="text-[11px] text-slate-500 mt-1 max-w-xs mx-auto">
        Pengajuan akta, pendaftaran warga baru, dan permohonan perubahan data akan tampil di sini.
    </p>
</div>

