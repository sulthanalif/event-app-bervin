<?php

use Mary\Traits\Toast;
use App\Traits\LogFormatter;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\SpecialVoucher;
use App\Traits\CreateOrUpdate;
use Livewire\Attributes\Title;
use Illuminate\Pagination\LengthAwarePaginator;

new #[Title('Special Voucher')] class extends Component {
    use Toast, LogFormatter, WithPagination, CreateOrUpdate, WithFileUploads;

    public string $search = '';

    public bool $modal = false;
    public bool $upload = false;

    public int $perPage = 10;
    public array $selected = [];
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public ?UploadedFile $file = null;

    public function datas(): LengthAwarePaginator
    {
        return SpecialVoucher::query()
            ->with('product')
            ->withAggregate('product', 'description')
            ->whereHas('product', function ($query) {
                $query->where('description', 'like', "%{$this->search}%")
                      ->orWhere('code', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function headers(): array
    {
        return [
            ['key' => 'product.description', 'label' => 'Product', 'sortBy' => 'product_description'],
            ['key' => 'start_date', 'label' => 'Tanggal Mulai'],
            ['key' => 'end_date', 'label' => 'Tanggal Selesai'],
            ['key' => 'budget', 'label' => 'Budget'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'created_at', 'label' => 'Dibuat'],
        ];
    }

    public function with(): array
    {
        return [
            'datas' => $this->datas(),
            'headers' => $this->headers(),
        ];
    }


}; ?>

@script
    <script>
        $js('upload', () => {
            $wire.file = null;
            $wire.upload = true;
        })
    </script>
@endscript

<div>
    <!-- HEADER -->
    <x-header title="Special Voucher" separator>
        <x-slot:actions>
            <x-button label="Upload" @click="$js.upload" responsive icon="fas.upload" />
            <x-button label="Create" @click="$js.create" responsive icon="fas.plus" />
        </x-slot:actions>
    </x-header>

    <div class="flex justify-end items-center gap-5">
        <x-input placeholder="Search..." wire:model.live="search" clearable icon="o-magnifying-glass" />
    </div>

    <!-- TABLE  -->
    <x-card class="mt-4" shadow>
        <x-table :headers="$headers" :rows="$datas" :sort-by="$sortBy" per-page="perPage" :per-page-values="[10, 25, 50, 100]"
            wire:model.live="selected" selectable with-pagination>
            @scope('cell_status', $data)
                <p>{{ $data->status ? 'Aktif' : 'Tidak Aktif' }}</p>
            @endscope
            @scope('actions', $data)
                <div class="flex gap-2">
                    <x-button icon="fas.pencil" @click="$js.edit({{ $data }})"
                        class="btn-ghost btn-sm text-primary" />
                </div>
            @endscope
            <x-slot:empty>
                <x-icon name="o-cube" label="It is empty." />
            </x-slot:empty>
        </x-table>

        @if ($selected)
            <div class="flex justify-end gap-2 mx-4 my-2">
                <x-button label="Delete" wire:click="delete" wire:confirm="Are you sure?"
                    spinner class=" btn-sm text-error" />
            </div>
        @endif
    </x-card>

    <x-modal wire:model="modal" title="Form Dealer" box-class="w-full h-fit max-w-[600px]" without-trap-focus>
        <x-form wire:submit="save" no-separator>

            <div>
                <x-input label="Kode" wire:model="code" required />
            </div>

            <div>
                <x-input label="Name" wire:model="name" required />
            </div>

            <div>
                <x-textarea label="Alamat" wire:model="address" />
            </div>

            <div>
                <x-input label="Telepon" type="number" wire:model="phone" />
            </div>

            <div>
            <x-toggle label="Status" wire:model="status" right />
            </div>

            <x-slot:actions>
                <x-button label="Save" type="submit" spinner="save" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    @include('livewire.modals.modal-upload')
</div>
