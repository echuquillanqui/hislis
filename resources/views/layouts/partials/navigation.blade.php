@php
    $navigationItems = collect(config('navigation', []))->map(function (array $group) {
        $group['children'] = collect($group['children'] ?? [])->filter(function (array $item) {
            $routeExists = isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']);
            $isAllowed = empty($item['permission']) || auth()->user()?->can($item['permission']);

            return $routeExists && $isAllowed;
        })->values()->all();

        return $group;
    })->filter(fn (array $group) => ! empty($group['children']))->values();
@endphp

<ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
    @foreach($navigationItems as $group)
        @php
            $isGroupActive = request()->routeIs($group['active'] ?? []);
        @endphp
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ $isGroupActive ? 'active bg-primary text-white' : '' }}"
               href="#"
               role="button"
               data-bs-toggle="dropdown"
               aria-expanded="{{ $isGroupActive ? 'true' : 'false' }}">
                <i class="{{ $group['icon'] ?? 'fa-solid fa-circle' }} {{ $isGroupActive ? 'text-white' : '' }}"></i> {{ $group['title'] }}
            </a>
            <ul class="dropdown-menu shadow {{ $isGroupActive ? 'show-on-active' : '' }}">
                @foreach($group['children'] as $item)
                    @php
                        $isItemActive = request()->routeIs($item['active'] ?? [$item['route']]);
                        $href = route($item['route']) . (! empty($item['fragment']) ? '#'.$item['fragment'] : '');
                    @endphp
                    <li>
                        <a class="dropdown-item py-2 {{ $isItemActive ? 'bg-primary text-white' : '' }}"
                           href="{{ $href }}"
                           aria-current="{{ $isItemActive ? 'page' : 'false' }}">
                            <i class="{{ $item['icon'] ?? 'fa-solid fa-circle' }} me-2 {{ $isItemActive ? 'text-white' : 'text-muted' }}"></i>{{ $item['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </li>
    @endforeach
</ul>
