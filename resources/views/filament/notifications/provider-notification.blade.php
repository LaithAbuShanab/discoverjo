<x-filament-notifications::notification
    :notification="$notification"
    class="flex w-96 max-w-full {{ isset($notification->getViewData()['api']) ? '' : 'rounded-lg' }} bg-white dark:bg-gray-900 p-4 shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 transition duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:leave-end="opacity-0"
>
    <div class="flex items-start gap-3 w-full">
        {{-- Notification icon --}}
        <x-filament-notifications::icon
            :icon="$notification->getIcon() ?? 'heroicon-o-check-circle'"
            :color="$notification->getColor() ?? 'success'"
            class="mt-0.5 h-5 w-5 shrink-0"
        />

        <div class="flex-1 space-y-1 text-sm">
            {{-- Title --}}
            <h3 class="font-semibold text-gray-800 dark:text-white">
                @php
                    $data = $notification->getViewData();
                 @endphp

                @if (
                    isset($data['reservation_id'], $data['reservation_username']) &&
                    !empty($data['reservation_id']) &&
                    !empty($data['reservation_username'])
                )
                    {{ __('panel.provider.' . $notification->getTitle()) }}
                @else
                    {{ $notification->getTitle() }}
                @endif
            </h3>

            {{-- Date --}}
            <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ $getDate() }}
            </p>

            {{-- Body --}}
            @if ($notification->getBody())
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    @php
                        $data = $notification->getViewData();
                    @endphp

                    @if (
                        isset($data['reservation_id'], $data['reservation_username']) &&
                        !empty($data['reservation_id']) &&
                        !empty($data['reservation_username'])
                    )
                        {{ __('panel.provider.' . $notification->getBody(), [
                            'username' => $data['reservation_username'],
                            'reservation_id' => $data['reservation_id'],
                        ]) }}
                    @else
                        {{ $notification->getBody() }}
                    @endif
                </p>
            @endif

            {{-- Actions --}}
            @if (!empty($notification->getActions()))
                <div class="pt-2">
                    @foreach ($notification->getActions() as $action)
                        <a
                            href="{{ $action->getUrl() }}"
                            class="fi-link group/link relative inline-flex items-center justify-center outline-none fi-size-sm fi-link-size-sm gap-1 fi-color-custom fi-color-primary fi-ac-action fi-ac-link-action"
                        >
                            <span
                                class="font-semibold text-sm text-custom-600 dark:text-custom-400 group-hover/link:underline group-focus-visible/link:underline"
                                style="--c-400: var(--primary-400); --c-600: var(--primary-600);"
                            >
                                {{ __('panel.provider.' . $action->getLabel()) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Close button --}}
        <button
            type="button"
            wire:loading.attr="disabled"
            x-on:click="close"
            class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 focus-visible:ring-2 -m-2 h-9 w-9 text-gray-400 hover:text-gray-500 focus-visible:ring-primary-600 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:ring-primary-500 fi-color-gray fi-no-notification-close-btn"
        >
            <x-heroicon-o-x-mark class="w-4 h-4" />
        </button>
    </div>
</x-filament-notifications::notification>
