<header class="app-header">
    @if ($hasSidebar)
        <button type="button" class="btn-icon d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#appSidebar"
                aria-controls="appSidebar" aria-label="Buka menu">
            <i class="bi bi-list fs-4" aria-hidden="true"></i>
        </button>
    @endif

    <a href="{{ $user && Route::has((string) $user->role) ? route($user->role) : url('/') }}" class="app-brand">En Pointe</a>

    @if ($user)
        <div class="dropdown app-user">
            <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-5" aria-hidden="true"></i>
                <span class="d-none d-sm-inline">{{ $user->name }}</span>
                <small class="d-none d-md-inline">{{ $user->role }}</small>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('change-profile-page') }}"><i class="bi bi-person me-2" aria-hidden="true"></i>Change Profile</a></li>
                <li><a class="dropdown-item" href="{{ route('change-password-page') }}"><i class="bi bi-key me-2" aria-hidden="true"></i>Change Password</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Sign Out</button>
                    </form>
                </li>
            </ul>
        </div>
    @endif
</header>
