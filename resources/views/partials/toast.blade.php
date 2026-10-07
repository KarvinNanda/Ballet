{{-- Flash toasts: session('msg') = success, session('error') = something went wrong. --}}
@if (session()->has('msg') || session()->has('error'))
    <div class="toast-stack">
        @if (session()->has('msg'))
            <div class="toast align-items-center" role="status" aria-live="polite" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-check-circle-fill text-success me-2" aria-hidden="true"></i>{{ session('msg') }}</div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="toast toast-error align-items-center" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="6000">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-exclamation-triangle-fill text-danger me-2" aria-hidden="true"></i>{{ session('error') }}</div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
                </div>
            </div>
        @endif
    </div>
@endif
