<?php

use App\Models\Dealer;
use Mary\Traits\Toast;
use App\Traits\LogFormatter;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Imports\DealerImport;
use Livewire\WithFileUploads;
use App\Traits\CreateOrUpdate;
use Livewire\Attributes\Title;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\LengthAwarePaginator;

new
#[Title('Dealers')]
class extends Component {
    use Toast, LogFormatter, WithPagination, CreateOrUpdate, WithFileUploads;

    public string $search = '';

    public bool $modal = false;
    public bool $upload = false;

    public int $perPage = 10;
    public array $selected = [];
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public ?UploadedFile $file = null;

    public string $code = '';
    public string $name = '';
    public string $address = '';
    public string $phone = '';
    public bool $status = true;
    public array $varDealer = ['recordId', 'code', 'name', 'address', 'phone', 'status'];

    public function downloadTemplate()
    {
        $file = public_path('templates/template-dealer.xlsx');

        if (!file_exists($file)) {
            $this->error('File tidak ditemukan', position: 'toast-bottom');
            return;
        }

        return Response::download($file);
    }

    public function import(): void
    {
        $this->validate([
            'file' => 'required|mimes:xlsx',
        ]);

        try {
            Excel::import(new DealerImport(), $this->file);

            $this->upload = false;
            $this->reset('file');
            $this->success('Data berhasil diupload', position: 'toast-bottom');
        } catch (\Exception $e) {
            $this->error('Data gagal diupload', position: 'toast-bottom');
            Log::channel('debug')->error("message: {$e->getMessage()}  file: {$e->getFile()}  line: {$e->getLine()}");
        }
    }

    public function export()
    {
        $datas = Brand::all();
        $datas = $datas->map(function ($brand) {
            return [
                'code' => $brand->code,
                'name' => $brand->name,
                'created_at' => $brand->created_at->format('Y-m-d'),
            ];
        });

        $headers = ['KODE', 'NAMA', 'DIBUAT PADA'];

        return Excel::download(new ExportDatas($datas, 'Data Brand', $headers), 'brand_' . date('Y-m-d') . '.xlsx');
    }

    public function save(): void
    {
        $this->setModel(new Dealer());
        $this->saveOrUpdate(
            validationRules: [
                'code' => 'required|string|max:50|unique:dealers',
                'name' => 'required|string|max:50',
                'address' => 'nullable|string',
                'phone' => 'nullable|string',
                'status' => 'required|boolean',
            ],
        );

        $this->unsetModel();

        $this->reset($this->varDealer);
    }

    public function delete(): void
    {
        $this->setModel(new Dealer());

        foreach ($this->selected as $id) {
            $this->setRecordId($id);
            $this->deleteData();
        }

        $this->unsetModel();
        $this->unsetRecordId();
        $this->selected = [];
    }

    public function datas(): LengthAwarePaginator
    {
        return Dealer::query()
            ->where('code', 'like', "%{$this->search}%")
            ->orWhere('name', 'like', "%{$this->search}%")
            ->orWhere('address', 'like', "%{$this->search}%")
            ->orWhere('phone', 'like', "%{$this->search}%")
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }


    public function headers(): array
    {
        return [
            ['key' => 'code', 'label' => 'Kode'],
            ['key' => 'name', 'label' => 'Nama'],
            ['key' => 'address', 'label' => 'Alamat'],
            ['key' => 'phone', 'label' => 'Telepon'],
            ['key' => 'status', 'label' => 'Status'],
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
            $wire.name = '';
            $wire.address = '';
            $wire.phone = '';
            $wire.status = true;
            $wire.modal = true;
        })

        $js('edit', (data) => {
            $wire.recordId = data.id;
            $wire.code = data.code;
            $wire.name = data.name;
            $wire.address = data.address;
            $wire.phone = data.phone;
            $wire.status = data.status;
            $wire.modal = true;
        })

        $js('upload', () => {
            $wire.upload = true;
        })
    </script>
@endscript

<div>
    <!-- HEADER -->
    <x-header title="Dealers" separator>
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
