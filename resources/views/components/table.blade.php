@props([
    'headers'  => [],   // array string header kolom
    'striped'  => false,
    'id'       => null,
])

{{--
    Komponen tabel: header navy-50, di mobile berubah jadi kartu.
    Gunakan slot 'head' untuk thead kustom, atau prop 'headers'.
    Gunakan slot default untuk tbody rows (x-app-table-row / tr).
--}}

<div class="overflow-hidden" style="border-radius: var(--radius-card); border: 1px solid var(--color-border);">
    {{-- Desktop tabel --}}
    <div class="hidden sm:block overflow-x-auto">
        <table @if($id) id="{{ $id }}" @endif class="w-full">
            <thead>
                @if(isset($head))
                    {{ $head }}
                @elseif(count($headers))
                    <tr>
                        @foreach($headers as $header)
                            <th scope="col">{{ $header }}</th>
                        @endforeach
                    </tr>
                @endif
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
            @if(isset($foot))
                <tfoot>{{ $foot }}</tfoot>
            @endif
        </table>
    </div>

    {{-- Mobile: kartu per baris --}}
    <div class="sm:hidden divide-y" style="divide-color: var(--color-border);">
        {{ $mobile ?? $slot }}
    </div>
</div>

@if(isset($pagination))
    <div class="mt-4">{{ $pagination }}</div>
@endif
