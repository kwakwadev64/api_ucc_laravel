<div class="flex items-center px-3">
    <form method="POST" action="{{ filament()->getLogoutUrl() }}" onsubmit="return confirm('Voulez-vous vraiment vous déconnecter ?')">
        @csrf
        <button
            type="submit"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
        >
            <x-filament::icon icon="heroicon-m-arrow-left-on-rectangle" class="h-4 w-4" />
            Déconnexion
        </button>
    </form>
</div>

@once
    <script>
        document.addEventListener('submit', (event) => {
            const form = event.target;

            if (form instanceof HTMLFormElement && form.closest('.fi-user-menu') && form.action === @js(filament()->getLogoutUrl())) {
                if (!window.confirm('Voulez-vous vraiment vous déconnecter ?')) {
                    event.preventDefault();
                }
            }
        }, true);
    </script>
@endonce
