{{-- resources/views/web/Qrs/index.blade.php --}}
{{-- FINAL, CORRECTED VERSION --}}

@extends('app')

@section('title', __('Qrs List'))

@section('content')
<div class="container mt-4 Qr-page-container">
    <div class="text-center mb-4">
        <img src="{{ asset('images/AutomateX.png') }}" alt="@lang('Company Logo')"
             style="display: block; margin-left: auto; margin-right: auto; width: 400px; max-height: 200px; object-fit: contain;">
    </div>

    <div class="card mx-auto" style="width: 90%;">
        <div class="card-header text-center bg-info">
            <h2>@lang('Qr List')</h2>
        </div>
        <div class="card-body">
            @php
                // This logic is still needed to correctly display images for items loaded initially via the controller
                $QrDisplayImages = [asset('images/A.png'), asset('images/B.png'), asset('images/C.png')];
                $page = $Qrs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $Qrs->currentPage() : 1;
                $perPage = $Qrs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $Qrs->perPage() : count($QrDisplayImages);
                $initialImageIndex = ($Qrs && $Qrs->count() > 0) ? (($page - 1) * $perPage) : 0;
            @endphp

            {{-- <<< KEY FIX: The table structure is now ALWAYS rendered so JavaScript can find it --}}
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="Qrs-table">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center">@lang('Qr Image')</th>
                            <th>@lang('Value / Code')</th>
                            <th>@lang('Associated Part')</th>
                            <th>@lang('Associated Table')</th>
                            <th>@lang('Start Time')</th>
                            <th>@lang('Current Time')</th>
                            <th class="text-center">@lang('Actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Use @forelse to handle both filled and empty states cleanly --}}
                        @forelse ($Qrs as $item)
                            @php
                                $currentDisplayImage = $QrDisplayImages[($initialImageIndex + $loop->index) % count($QrDisplayImages)];
                                $partNameFromImage = '';
                                if (str_ends_with($currentDisplayImage, 'A.png')) {
                                    $partNameFromImage = __('Part A');
                                } elseif (str_ends_with($currentDisplayImage, 'B.png')) {
                                    $partNameFromImage = __('Part B');
                                } elseif (str_ends_with($currentDisplayImage, 'C.png')) {
                                    $partNameFromImage = __('Part C');
                                }
                            @endphp
                            <tr data-Qr-id="{{ $item->id }}">
                                <td class="text-center">
                                    <img src="{{ $currentDisplayImage }}"
                                         alt="@lang('Qr for') {{ $item->Qr ?? 'N/A' }}"
                                         style="max-height: 60px; display: block; margin: auto; background-color: white; padding: 5px;">
                                </td>
                                <td>{{ $item->value ?? __('N/A') }}</td>
                                <td>{{ $item->part->name ?? $partNameFromImage }}</td>
                                <td>{{ $item->table->name ?? ($item->table_id ?? __('N/A')) }}</td>
                                <td>{{ $item->created_at ? (new DateTime($item->created_at))->format('d/m/Y, g:i:s A') : __('N/A') }}</td>
                                <td>{{ $item->updated_at ? (new DateTime($item->updated_at))->format('d/m/Y, g:i:s A') : __('N/A') }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="printQrImage('{{ $currentDisplayImage }}')" title="@lang('Print Displayed Image')">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            {{-- This row is displayed only when the collection is empty on page load --}}
                            <tr id="no-qrs-row">
                                <td colspan="7" class="text-center">@lang('No Qrs found. Waiting for new scans...')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($Qrs instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="d-flex justify-content-center mt-3">
                    {{ $Qrs->links() }}
                </div>
            @endif
        </div>
        <div class="card-footer text-end">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">@lang('Go Back')</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function logEcho(message, data = null) {
        console.log(`[Qr Page Echo] ${message}`, data || '');
    }

    let QrImagePaths = [];
    let nextDynamicImageIndex = 0;

    @php
        $jsQrDisplayImages = [asset('images/A.png'), asset('images/B.png'), asset('images/C.png')];
        $jsInitialImageIndex = 0;
        $jsTotalInitialItems = $Qrs ? $Qrs->count() : 0;
        if ($Qrs instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $page = $Qrs->currentPage();
            $perPage = $Qrs->perPage();
            $jsInitialImageIndex = ($page - 1) * $perPage;
        }
    @endphp

    QrImagePaths = @json($jsQrDisplayImages);
    nextDynamicImageIndex = ({{ $jsInitialImageIndex }} + {{ $jsTotalInitialItems }}) % QrImagePaths.length;
    logEcho('Initial image paths and index set.', { paths: QrImagePaths, nextIndex: nextDynamicImageIndex });

    function formatJsDateTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('en-GB', { dateStyle: 'short', timeStyle: 'medium', hour12: true }).format(date);
    }

    @auth
        const currentUserId = {{ auth()->id() }};
        logEcho(`User authenticated with ID: ${currentUserId}.`);

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.Echo !== 'undefined') {
                logEcho('Echo object found. Attempting to subscribe.');
                try {
                    // <<< KEY FIX: Channel name and Event name must match the backend (QrEvent.php and routes/channels.php)
                    window.Echo.private(`user.${currentUserId}.qrs`)
                        .listen('.Qr-event', (e) => {
                            logEcho('QrEvent received:', e);
                            const QrsTableBody = document.querySelector('#Qrs-table tbody');

                            if (!QrsTableBody) {
                                console.error('[Qr Page Echo] Critical error: Qrs table body not found!');
                                return;
                            }
                            if (!e.Qr || !e.Qr.id) {
                                console.error('[Qr Page Echo] Event received with invalid Qr data.', e);
                                return;
                            }

                            const QrValue = e.Qr.value || 'N/A';
                            const existingRow = QrsTableBody.querySelector(`tr[data-Qr-id="${e.Qr.id}"]`);

                            if (e.isUpdate === true && existingRow) {
                                logEcho('Processing as UPDATE event for Qr ID:', e.Qr.id);
                                existingRow.querySelector('td:nth-child(2)').textContent = QrValue;
                                existingRow.querySelector('td:nth-child(3)').textContent = e.Qr.part?.name || 'N/A';
                                existingRow.querySelector('td:nth-child(4)').textContent = e.Qr.table?.name || 'N/A';
                                existingRow.querySelector('td:nth-child(6)').textContent = formatJsDateTime(e.Qr.updated_at);
                                logEcho('Row updated successfully.');
                            } else {
                                if(existingRow) return; // Don't add a duplicate if it already exists
                                logEcho('Processing as NEW entry for Qr ID:', e.Qr.id);
                                
                                // <<< KEY FIX: Remove the placeholder "empty" row if it exists
                                const placeholderRow = document.querySelector('#no-qrs-row');
                                if (placeholderRow) {
                                    placeholderRow.remove();
                                }

                                const imageToUse = QrImagePaths[nextDynamicImageIndex];
                                nextDynamicImageIndex = (nextDynamicImageIndex + 1) % QrImagePaths.length;

                                const newRow = document.createElement('tr');
                                newRow.setAttribute('data-Qr-id', e.Qr.id);
                                newRow.innerHTML = `
                                    <td class="text-center">
                                        <img src="${imageToUse}" alt="Qr for ${QrValue}" style="max-height: 60px; display: block; margin: auto; background-color: white; padding: 5px;">
                                    </td>
                                    <td>${QrValue}</td>
                                    <td>${e.Qr.part?.name || 'N/A'}</td>
                                    <td>${e.Qr.table?.name || 'N/A'}</td>
                                    <td>${formatJsDateTime(e.Qr.created_at)}</td>
                                    <td>${formatJsDateTime(e.Qr.updated_at)}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="printQrImage('${imageToUse}')" title="@lang('Print Displayed Image')">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </td>
                                `;
                                QrsTableBody.prepend(newRow);
                                logEcho('New Qr row prepended to table.');
                            }
                        })
                        .error((error) => {
                            console.error('[Qr Page Echo] Error subscribing to private channel:', error);
                        });

                    if (window.Echo.connector?.pusher) {
                        window.Echo.connector.pusher.connection.bind('state_change', s => logEcho('Pusher state changed:', s));
                    }

                } catch (error) {
                    console.error('[Qr Page Echo] Error setting up Echo listener:', error);
                }
            } else {
                console.warn('[Qr Page Echo] Laravel Echo is not defined.');
            }
        });
    @else
        logEcho('User not authenticated. Real-time updates disabled.');
    @endauth

    function printQrImage(imageUrl) {
        if (!imageUrl) return;
        const printWindow = window.open('', '_blank', 'height=400,width=600');
        printWindow.document.write(`<html><body style="text-align:center;"><img src="${imageUrl}" onload="window.print(); setTimeout(window.close, 100);"></body></html>`);
        printWindow.document.close();
    }
</script>
@endpush