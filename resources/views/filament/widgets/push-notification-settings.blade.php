<x-filament-widgets::widget>
    <x-filament::section>
        <div
            data-admin-push-settings
            data-public-key="{{ $publicKey }}"
            data-subscription-endpoint="{{ $subscriptionEndpoint }}"
            data-csrf-token="{{ $csrfToken }}"
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Avisos de nuevas citas</h2>
                <p data-push-description class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Comprueba las notificaciones de este dispositivo para avisarte de cada reserva o cancelación.
                </p>
            </div>
            <button
                data-push-toggle
                type="button"
                class="fi-btn fi-btn-size-md inline-grid min-h-10 shrink-0 place-items-center rounded-lg bg-primary-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 disabled:cursor-wait disabled:opacity-60"
            >
                Comprobar avisos
            </button>
        </div>
        @vite('resources/js/admin-push.ts')
    </x-filament::section>
</x-filament-widgets::widget>
