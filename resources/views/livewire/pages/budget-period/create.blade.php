<?php

use App\Models\Dealer;
use Mary\Traits\Toast;
use App\Models\Voucher;
use App\Models\BudgetPeriod;
use App\Traits\LogFormatter;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Traits\CreateOrUpdate;
use Livewire\Attributes\Title;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

new #[Title('Form Budget Period')] class extends Component {
    use Toast, LogFormatter, WithPagination, CreateOrUpdate, WithFileUploads;

    public ?int $dealer_searchable_id = null;
    public string $start_date = '';
    public string $end_date = '';
    public string $budget = '';
    public bool $status = true;
    public array $varBudgetPeriod = ['recordId', 'dealer_searchable_id', 'start_date', 'end_date', 'budget', 'status'];

    public Collection $dealersSearchable;

    public array $vouchers = [];
    public int $totalNominal = 0;

    #[Url]
    public ?int $id = null;


    public function mount(): void
    {
        $this->searchDealer();

        if ($this->id) {
            $this->edit();
        }
    }

    public function edit(): void
    {
        $budgetPeriod = BudgetPeriod::findOrFail($this->id);

        $this->dealer_searchable_id = $budgetPeriod->dealer_id;
        $this->start_date = $budgetPeriod->start_date;
        $this->end_date = $budgetPeriod->end_date;
        $this->budget = $budgetPeriod->budget;
        $this->status = $budgetPeriod->status;

        $vouchers = $budgetPeriod->vouchers()
            ->where('status', true)
            ->where('is_claimed', false)
            ->where('is_locked', false)
            ->get()
            ->groupBy('amount')
            ->map(function ($group, $amount) {
                return [
                    'amount' => $amount,
                    'qty' => $group->count(),
                ];
            })
            ->values()
            ->toArray();

        $this->vouchers = $vouchers;
        $this->countTotalNominal();
    }

    public function back(): void
    {
        $this->redirect(route('budget-period'), navigate: true);
    }

    public function searchDealer(string $value = '')
    {
        $selectedOption = Dealer::where('id', $this->dealer_searchable_id)->get();

        $this->dealersSearchable = Dealer::query()
            ->where('name', 'like', "%{$value}%")
            ->orWhere('code', 'like', "%{$value}%")
            ->orderBy('name')
            ->get()
            ->merge($selectedOption);
    }

    public function countTotalNominal(): void
    {
        $this->totalNominal = (int) collect($this->vouchers)->sum(function ($voucher) {
            $amount = (int) ($voucher['amount'] ?? 0);
            $qty = (int) ($voucher['qty'] ?? 0);
            return $amount * $qty;
        });

        $this->dispatch('$refresh');
    }

    public function addVoucher(): void
    {
        $this->vouchers[] = ['amount' => '', 'qty' => ''];
    }

    public function deleteVoucher(int $index): void
    {
        unset($this->vouchers[$index]);
        $this->vouchers = array_values($this->vouchers);

        $this->countTotalNominal();
    }

    public function save(): void
    {
        $this->validate([
            'dealer_searchable_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'required|numeric|min:0',
            'status' => 'required|boolean',
        ]);

        $vouchers = collect($this->vouchers)
            ->filter(fn ($voucher) => filled($voucher['amount']) && filled($voucher['qty']))
            ->filter(fn ($voucher) => (int) ($voucher['amount'] ?? 0) > 0 && (int) ($voucher['qty'] ?? 0) > 0);

        if ($vouchers->isEmpty()) {
            $this->warning('Voucher Tidak Boleh Kosong!', position: 'toast-bottom');
            return;
        }

        try {
            DB::beginTransaction();
            $budget = BudgetPeriod::create([
                'dealer_id' => $this->dealer_searchable_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'budget' => $this->budget,
                'status' => $this->status,
            ]);

            foreach ($vouchers as $voucher) {
                $amount = (int) $voucher['amount'];
                $qty = (int) $voucher['qty'];

                for ($i = 0; $i < $qty; $i++) {
                    Voucher::create([
                        'budget_period_id' => $budget->id,
                        'amount' => $amount,
                        'status' => true,        // default aktif
                        'is_claimed' => false,   // default belum diklaim
                        'is_locked' => false,    // default belum dikunci
                        // 'code' diisi otomatis via boot()
                    ]);
                }
            }

            DB::commit();

            $this->success('Budget Period Berhasil Disimpan.', position: 'toast-bottom', redirectTo: route('budget-period'));
            // $this->redirect(route('budget-period'), navigate: true);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->logError('debug', 'Gagal Save Budget Period.', $th);
            $this->error('Ada kesalahan pada sistem.', position: 'toast-bottom');
        }

    }

}; ?>


<div>
    <!-- HEADER -->
    <x-header title="Form Budget Period" separator>
        <x-slot:actions>
            <x-button label="Back" @click="$wire.back" responsive icon="fas.arrow-left" />
        </x-slot:actions>
    </x-header>

    <x-form wire:submit='save'>
        <div class="flex flex-col md:flex-row gap-4">
            <x-card title="Budget Period" shadow class="w-full md:w-1/2">
                <div>
                <x-choices-offline
                    label="Dealer"
                    wire:model="dealer_searchable_id"
                    :options="$dealersSearchable"
                    placeholder="Pilih Dealer ..."
                    search-function="searchDealer"
                    single
                    clearable
                    no-result-text="Ops! Nothing here ..."
                    searchable />
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
                    <x-input label="Budget" wire:model="budget" prefix="Rp" locale="pt-ID" money />
                </div>

                <div class="mt-3">
                    <x-toggle label="Status" wire:model="status" />
                </div>
            </x-card>

            <x-card title="Voucher" shadow class="w-full md:w-1/2">
                <table class="w-full table-auto">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="px-4 py-2 border-b">Nominal Voucher</th>
                            <th class="px-4 py-2 border-b">Jumlah Voucher</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vouchers as $index => $voucher)
                            <tr>
                                <td class="px-4 py-2 ">
                                    <x-input
                                        class="w-full"
                                        prefix="Rp"
                                        wire:model.live="vouchers.{{ $index }}.amount"
                                        prefix="Rp" locale="pt-ID" money
                                        @change="$wire.countTotalNominal()"
                                    />
                                </td>
                                <td class="px-4 py-2 ">
                                    <x-input
                                        type="number"
                                        class="w-full"
                                        wire:model.live="vouchers.{{ $index }}.qty"
                                        @change="$wire.countTotalNominal()"
                                    />
                                </td>
                                <td class="px-4 py-2  text-right">
                                    <x-button
                                        icon="fas.trash"
                                        @click="$wire.deleteVoucher({{ $index }})"
                                        spinner="deleteVoucher({{ $index }})"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-4 text-center text-gray-500">
                                    <x-icon name="o-cube" label="It is empty." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="px-4 py-2 border-t">
                                Total Nominal Voucher
                            </th>
                            <th class="px-4 py-2 border-t">
                                <x-input

                                        class="w-full"
                                        {{-- prefix="Rp" --}}
                                        :value="number_format($totalNominal, 0, ',', '.')"
                                        prefix="Rp"
                                        {{-- locale="pt-ID"
                                        money --}}
                                        readonly
                                    />
                            </th>
                        </tr>
                    </tfoot>
                </table>

                <x-button label="Tambah Voucher" icon="fas.plus" class="w-full mt-3" @click="$wire.addVoucher" spinner="addVoucher" />
            </x-card>
        </div>
        <x-slot:actions>
            <x-button label="Save" class="btn-primary" type="submit" spinner="save" />
        </x-slot:actions>
    </x-form>

</div>
