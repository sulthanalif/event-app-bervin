<?php

use Mary\Traits\Toast;
use App\Models\Product;
use App\Traits\LogFormatter;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Traits\CreateOrUpdate;
use Livewire\Attributes\Title;
use Illuminate\Pagination\LengthAwarePaginator;

new
#[Title('Products')]
class extends Component {
    use Toast, LogFormatter, WithPagination, CreateOrUpdate;

    public string $search = '';

    public bool $modal = false;

    public int $perPage = 10;
    public array $selected = [];
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public string $code = '';
    public string $description = '';
    public bool $status = true;
    public array $varProduct = ['recordId', 'code', 'description', 'status'];

    public function save(): void
    {
        $this->setModel(new Product());
        $this->saveOrUpdate(
            validationRules: [
                'code' => 'required|string|unique:products,code',
                'description' => 'required|string',
                'status' => 'required|boolean',
            ],
        );

        $this->unsetModel();
        $this->modal = false;
    }

    public function delete(): void
    {
        $this->setModel(new Product());

        foreach ($this->selected as $id) {
            $this->setRecordId($id);
            $this->deleteData();
        }

        $this->unsetRecordId();
        $this->unsetModel();
    }

    public function datas(): LengthAwarePaginator
    {
        return Product::query()
            ->where('code', 'like', "%{$this->search}%")
            ->orWhere('description', 'like', "%{$this->search}%")
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function headers(): array
    {
        return [
            ['key' => 'code', 'label' => 'Kode'],
            ['key' => 'description', 'label' => 'Deskripsi'],
            ['key' => 'created_at', 'label' => 'Dibuat', 'class' => 'w-64'],
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
        $js('create', () => {
            $wire.recordId = null;
            $wire.code = '';
            $wire.description = '';
            $wire.status = true;
            $wire.modal = true;
        })

        $js('edit', (data) => {
            $wire.recordId = data.id;
            $wire.code = data.code;
            $wire.description = data.description;
            $wire.status = data.status;
            $wire.modal = true;
        })
    </script>
@endscript

<div>
    <!-- HEADER -->
    <x-header title="Products" separator>
        <x-slot:actions>
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
        </x-table>

        @if ($selected)
            <div class="flex justify-end gap-2 mx-4 my-2">
                <x-button label="Delete" wire:click="delete" wire:confirm="Are you sure?"
                    spinner class=" btn-sm text-error" />
            </div>
        @endif
    </x-card>

    <x-modal wire:model="modal" title="Form Product" box-class="w-full h-fit max-w-[600px]" without-trap-focus>
        <x-form wire:submit="save" no-separator>

            <div>
                <x-input label="Kode" wire:model="code" required />
            </div>

            <div>
                <x-input label="Deskripsi" wire:model="description" required />
            </div>

            <div>
                <x-toggle label="Status" wire:model="status" right />
            </div>

            <x-slot:actions>
                <x-button label="save" type="submit" spinner="save" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
