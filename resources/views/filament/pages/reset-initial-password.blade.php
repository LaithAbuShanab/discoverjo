<!-- resources/views/filament/pages/reset-initial-password.blade.php -->
<x-filament::page>
    {{ $this->form }}

    <div class="mt-4">
        <x-filament::button
            type="submit"
            form="submitForm"
            wire:click="submit"
            class="text-sm px-4 py-2 bg-warning-500 hover:bg-warning-600 text-white rounded-md shadow-sm"
        >
            Update Password
        </x-filament::button>
    </div>
</x-filament::page>
