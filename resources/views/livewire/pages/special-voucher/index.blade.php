<?php

use Mary\Traits\Toast;
use App\Models\Product;
use App\Traits\LogFormatter;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\SpecialVoucher;
use App\Traits\CreateOrUpdate;
use Livewire\Attributes\Title;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

    public ?int $id = null;
    public ?int $product_searchable_id = null;
    public string $start_date = '';
    public string $end_date = '';
    public float $amount = 0;
    public bool $status = true;
    public array $varSpecialVoucher = ['id', 'product_searchable_id', 'start_date', 'end_date', 'amount', 'status'];

    public Collection $productsSearchable;

    public function mount(): void
    {
        $this->searchProduct();
    }

    public function searchProduct(string $value = '')
    {
        $selectedOption = Product::where('id', $this->product_searchable_id)->get();

        $this->productsSearchable = Product::query()
            ->where('description', 'like', "%{$value}%")
            ->orWhere('code', 'like', "%{$value}%")
            ->orderBy('description')
            ->get()
            ->merge($selectedOption);
    }

    public function create(): void
    {
        $this->redirect(route('special-voucher-form'), navigate: true);
    }

    public function save(): void
    {
        $this->validate([
            'product_searchable_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|boolean',
        ]);

        try {
            DB::beginTransaction();

            SpecialVoucher::updateOrCreate(['id' => $this->id], [
                'product_id' => $this->product_searchable_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'amount' => $this->amount,
                'status' => $this->status,
            ]);

            DB::commit();

            $this->success('Special Voucher Berhasil Disimpan.', position: 'toast-bottom');
            $this->reset($this->varSpecialVoucher);
            $this->modal = false;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Special Voucher Gagal Disimpan.', position: 'toast-bottom');
            $this->logError('debug', 'Gagal Save Special Voucher.', $e);
        }
    }

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
            ['key' => 'amount', 'label' => 'Nominal'],
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
        $js('create', () => {
            $wire.id = null;
            $wire.product_searchable_id = null;
            $wire.start_date = '';
            $wire.end_date = '';
            $wire.amount = 0;
            $wire.status = true;
            $wire.modal = true;
        })
    </script>
@endscript

<div>
    <!-- HEADER -->
    <x-header title="Special Voucher" separator>
        <x-slot:actions>
            <x-button label="Upload" @click="$js.upload" responsive icon="fas.upload" />
            <x-button label="Create" @click="$js.create" responsive icon="fas.plus"  />
        </x-slot:actions>
    </x-header>

    <div class="flex justify-end items-center gap-5">
        <x-input placeholder="Search..." wire:model.live="search" clearable icon="o-magnifying-glass" />
    </div>

    <!-- TABLE  -->
    <x-card class="mt-4" shadow>
        <x-table :headers="$headers" :rows="$datas" :sort-by="$sortBy" per-page="perPage" :per-page-values="[10, 25, 50, 100]"
            wire:model.live="selected" selectable with-pagination>
            @scope('cell_start_date', $data)
                <p>{{ \Carbon\Carbon::parse($data->start_date)->locale('id_ID')->isoFormat('D MMMM Y') }}</p>
            @endscope
            @scope('cell_end_date', $data)
                <p>{{ \Carbon\Carbon::parse($data->end_date)->locale('id_ID')->isoFormat('D MMMM Y') }}</p>
            @endscope
            @scope('cell_amount', $data)
                <p>Rp. {{ number_format($data->amount, 0, ',', '.') }}</p>
            @endscope
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
                <x-choices-offline
                    label="Product"
                    wire:model="product_searchable_id"
                    :options="$productsSearchable"
                    placeholder="Pilih Product ..."
                    search-function="searchProduct"
                    single
                    clearable
                    no-result-text="Ops! Nothing here ..."
                    searchable >
                    @scope('item', $product)
                        <x-list-item :item="$product" value="description" sub-value="code" />
                    @endscope

                    {{-- Selection slot--}}
                    @scope('selection', $product)
                        {{ $product->description }} ({{ $product->code }})
                    @endscope
                </x-choices-offline>
                </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-datepicker label="Tanggal Mulai" wire:model="start_date" icon="o-calendar" @change="$wire.$refresh()" :config="[
                        'minDate' => now()->toDateString(),
                    ]" />
                </div>
                <div>
                    <x-datepicker label="Tanggal Berakhir" wire:model="end_date" icon="o-calendar" :config="[
                    'minDate' => $start_date,
                    ]" />
                </div>
            </div>

            <div>
                <x-input label="Nominal" wire:model="amount" prefix="Rp" locale="pt-ID" money />
            </div>

            <div>
                <x-toggle label="Status" wire:model="status" />
            </div>

            <x-slot:actions>
                <x-button label="Save" type="submit" spinner="save" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    @include('livewire.modals.modal-upload')
</div>
