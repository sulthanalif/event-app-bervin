{{--  modal --}}
<x-modal title="Upload File" wire:model="upload" box-class="w-12/12 md:w-8/12 lg:w-6/12 xl:w-4/12" without-trap-focus>
    <x-form wire:submit="import" class="relative" separator>
        <div class="flex justify-center items-center pt-5">
            <x-file wire:model="file" hint="Hanya File Excel" accept=".xlsx" required />
        </div>
        <x-slot:actions>
            <x-button label="Download Template" @click="$wire.downloadTemplate" spinner='downloadTemplate' />
            <x-button label="Upload" class="btn-primary" type="submit" spinner="import" />
        </x-slot:actions>
    </x-form>
</x-modal>
