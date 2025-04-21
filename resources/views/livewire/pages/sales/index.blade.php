<?php

use App\Models\Sales;
use App\Models\Dealer;
use Mary\Traits\Toast;
use App\Imports\SalesImport;
use App\Traits\LogFormatter;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use Livewire\WithFileUploads;
use App\Traits\CreateOrUpdate;
use Livewire\Attributes\Title;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\LengthAwarePaginator;

new #[Title('Sales')] class extends Component {
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
    public ?int $dealer_id = null;
    public bool $status = true;
    public array $varSales = ['recordId', 'code', 'status', 'name', 'dealer_id'];

    public Collection $dealersSearchable;

    public function mount(): void
    {
        $this->searchDealers();
    }

    public function searchDealers(string $value = '')
    {
        $selectedOption = Dealer::where('id', $this->dealer_id)->get();

        $this->dealersSearchable = Dealer::query()
            ->where('name', 'like', "%{$value}%")
            ->orWhere('code', 'like', "%{$value}%")
            ->orderBy('name')
            ->get()
            ->merge($selectedOption);
    }

    public function downloadTemplate()
    {
        $file = public_path('templates/template-sales.xlsx');

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
            Excel::import(new SalesImport(), $this->file);

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
        $this->setModel(new Sales());
        $this->saveOrUpdate(
            validationRules: [
                'code' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'dealer_id' => 'required|exists:dealers,id',
                'status' => 'boolean',
            ],
        );
        $this->modal = false;
        $this->reset($this->varSales);
    }

    public function datas(): LengthAwarePaginator
    {
        return Sales::query()
            ->withAggregate('dealer', 'name')
            ->when($this->search, function ($query) {
                $query->where('code', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function headers(): array
    {
        return [
            ['key' => 'code', 'label' => __('Code'), 'sortable' => true],
            ['key' => 'name', 'label' => __('Name'), 'sortable' => true],
            ['key' => 'dealer.name', 'label' => __('Dealer'), 'sortable' => 'dealer_name'],
            ['key' => 'status', 'label' => __('Status'), 'sortable' => true],
            ['key' => 'created_at', 'label' => __('Created At'), 'sortable' => true],
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
            $wire.dealer_id = null;
            $wire.status = true;
            $wire.modal = true;
        })
        $js('edit', (data) => {
            $wire.recordId = data.id;
            $wire.code = data.code;
            $wire.name = data.name;
            $wire.dealer_id = data.dealer_id;
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
    <x-header title="Sales" separator>
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
            wire:model.live="selected" selectable with-pagination @row-click="$js.edit($event.detail)">
            @scope('cell_status', $data)
                <p>{{ $data->status ? 'Aktif' : 'Tidak Aktif' }}</p>
            @endscope
            {{-- @scope('actions', $data)
                <div class="flex gap-2">
                    <x-button icon="fas.pencil" @click="$js.edit({{ $data }})"
                        class="btn-ghost btn-sm text-primary" />
                </div>
            @endscope --}}
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
                <x-choices-offline
                    label="Dealer"
                    wire:model="dealer_id"
                    :options="$dealersSearchable"
                    placeholder="Pilih Dealer ..."
                    search-function="searchDealer"
                    single
                    clearable
                    no-result-text="Ops! Nothing here ..."
                    searchable
                    required />
            </div>

            <div class="mt-5">
            <x-toggle label="Status" wire:model="status" />
            </div>

            <x-slot:actions>
                <x-button label="Save" type="submit" spinner="save" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    @include('livewire.modals.modal-upload')
</div>
