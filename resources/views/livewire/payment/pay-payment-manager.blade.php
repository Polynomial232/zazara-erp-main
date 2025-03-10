<div>
    @section('title', 'Pay Payments')

    <div class="d-md-flex justify-content-between">
        <h2 class="mb-3"><span class="text-muted fw-light">Data @yield('title')</span></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="javascript:void(0);">Payments</a>
                </li>
                <li class="breadcrumb-item active">@yield('title')</li>
            </ol>
        </nav>
    </div>

    <x-flash-alert />

    <div class="col-md-offset-1 col-md-12">
        <div class="panel">
            <div class="panel-heading">
                <div class="row">
                    <div class="col col-sm-4 col-xs-12 d-flex">
                        <select class="form-select shadow-sm me-2 w-px-75" wire:model="perPage">
                            @foreach ([10, 25, 50, 100] as $val)
                                <option value="{{ $val }}" @if ($val == $perPage) selected @endif>
                                    {{ $val }}</option>
                            @endforeach
                        </select>
                        <input type="text" class="form-control w-100" placeholder="Search"
                            wire:model.debounce.500ms="searchKeyword">
                    </div>
                    <div class="col-sm-8 col-xs-12 text-right">
                        <div class="d-md-flex justify-content-end">
                            <button type="button" wire:click="paymentMultiple()" class="btn btn-success"
                                title="Payment" data-bs-toggle="modal" data-bs-target="#PaymentModal"><i
                                    class="bx bx-money me-2"></i>Payments</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-body table-responsive position-relative">
                <div wire:loading class="position-absolute fs-1 top-50 start-50 z-3 text-info">
                    <i class="fa fa-spin fa-spinner"></i>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-px-75"><input class="form-check-input" type="checkbox" id="checkAll"
                                    wire:model="selectAll">
                            </th>
                            <th class="sort" wire:click="sortOrder('number')">Purchase Number
                                {!! $sortLink !!}
                            </th>
                            <th class="sort" wire:click="sortOrder('date')">Date {!! $sortLink !!}
                            </th>
                            <th class="sort" wire:click="sortOrder('supplier_name')">Supplier
                                {!! $sortLink !!}</th>
                            <th class="sort" wire:click="sortOrder('total')">Total {!! $sortLink !!}
                            </th>
                            <th class="sort" wire:click="sortOrder('payment')">Paid {!! $sortLink !!}
                            </th>
                            <th class="sort" wire:click="sortOrder('rest')">Rest {!! $sortLink !!}
                            </th>
                            <th class="w-px-150">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchases as $purchase)
                            <tr>
                                <td class="text-center">
                                    @if ($purchase->type == 'Tax')
                                        <input class="form-check-input" type="checkbox" value="{{ $purchase->id }}"
                                            wire:model="selected">
                                    @else
                                        <input class="form-check-input" type="checkbox" value="{{ $purchase->id }}"
                                            wire:model="selectedN">
                                    @endif
                                </td>
                                <td class="border-start text-center">{{ $purchase->number }}</td>
                                <td class="border-start text-center">{{ $purchase->date }}</td>
                                <td class="border-start">{{ $purchase->supplier_name }}</td>
                                <td class="border-start text-right">{{ number_format($purchase->total, 2) }}</td>
                                <td class="border-start text-right">{{ number_format($purchase->payment, 2) }}</td>
                                @if ($purchase->payment == 0)
                                    <td class="border-start text-right">{{ number_format($purchase->total, 2) }}</td>
                                @else
                                    <td class="border-start text-right">{{ number_format($purchase->rest, 2) }}</td>
                                @endif
                                <td class="border-start text-center">
                                    <button type="button"
                                        wire:click="view('{{ $purchase->id }}','{{ $purchase->type }}')"
                                        class="btn btn-xs btn-success" title="Open Data"><span
                                            class="bx bx-folder-open"></span></button>
                                </td>
                            </tr>
                        @endforeach

                        @if ($purchases->count() <= 0)
                            <tr>
                                <td colspan="8" class="text-center">No data..</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="panel-footer">
                {{ $purchases->links('admin.custom-pagination-new') }}
            </div>
        </div>
    </div>

    {{-- Payment --}}
    <div wire:ignore.self class="modal fade" id="PaymentModal" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" payment="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@yield('title') Multiple</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>

                <form wire:submit.prevent="store">
                    <div class="modal-body">

                        <x-flash-alert />

                        <div class="accordion" id="accordionExample">
                            @foreach ($purchasesPayMultiple as $key => $val)
                                <div class="card accordion-item active">
                                    <h2 class="accordion-header" id="heading{{ $key }}">
                                        <button type="button" class="accordion-button" data-bs-toggle="collapse"
                                            data-bs-target="#accordion{{ $key }}" aria-expanded="true"
                                            aria-controls="accordionOne{{ $key }}">
                                            {{ $val['number'] }}
                                        </button>
                                    </h2>

                                    <div id="accordion{{ $key }}" class="accordion-collapse collapse show"
                                        data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <div class="d-flex justify-content-between bg-lighter p-2 mb-4">
                                                <p class="mb-0">Purchase Balance:</p>
                                                <p class="fw-medium mb-0">Rp.
                                                    {{ number_format($val['rest'], 2, ',', '.') }}</p>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Payment Amount <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    wire:model="purchasesPayMultiple.{{ $key }}.amount"
                                                    class="form-control @error('purchasesPayMultiple.{{ $key }}.amount') is-invalid @enderror"
                                                    value="Rp. {{ number_format($val['rest'], 2, ',', '.') }}"
                                                    onclick="this.select()">
                                                @error('purchasesPayMultiple.{{ $key }}.amount')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Date <span
                                                        class="text-danger">*</span></label>
                                                <input type="date"
                                                    wire:model="purchasesPayMultiple.{{ $key }}.date"
                                                    class="form-control @error('purchasesPayMultiple.{{ $key }}.date') is-invalid @enderror"
                                                    placeholder="Date">
                                                @error('purchasesPayMultiple.{{ $key }}.date')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                @inject('paymentMethods', 'App\Models\MsPaymentMethods')
                                                <label class="form-label">Payment Method <span
                                                        class="text-danger">*</span></label>
                                                <select
                                                    wire:model="purchasesPayMultiple.{{ $key }}.payment_method_id"
                                                    class="form-select @error('purchasesPayMultiple.{{ $key }}.payment_method_id') is-invalid @enderror">
                                                    @foreach ($paymentMethods::where('is_status', '=', '1')->orderBy('name')->select('id', 'name')->get() as $key => $val)
                                                        <option value="{{ $val['id'] }}">
                                                            {{ $val['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                @error('purchasesPayMultiple.{{ $key }}.payment_method_id')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Notes</label>
                                                <textarea wire:model="purchasesPayMultiple.{{ $key }}.notes"
                                                    class="form-control @error('purchasesPayMultiple.{{ $key }}.notes') is-invalid @enderror"
                                                    placeholder="Notes" rows="3"></textarea>
                                                @error('purchasesPayMultiple.{{ $key }}.notes')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary"
                            wire:click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.addEventListener('checkall-indeterminate', event => {
                $("#checkAll").prop("indeterminate", true);
            });

            window.addEventListener('checkall-indeterminate-false', event => {
                $("#checkAll").prop("indeterminate", false);
            });

            window.addEventListener('checkall-checked', event => {
                $("#checkAll").prop("checked", true);
            });

            window.addEventListener('checkall-checked-false', event => {
                $("#checkAll").prop("checked", false);
            });

            window.addEventListener('close-modal', event => {
                $('#PaymentModal').modal('hide');
            });
        </script>
    @endpush
</div>
