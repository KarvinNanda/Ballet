@extends('layouts.app')

@section('title', 'Student · '.$detail->LongName)

@section('content')
    @php
        $tab = in_array(request('tab'), ['classes', 'transactions'], true) ? request('tab') : 'profile';
        if ($errors->any() || session()->has('error')) {
            $tab = 'profile'; // the rejected values live in the Profile form
        }
        $tabs = [
            'profile' => 'Profile',
            'classes' => 'Classes ('.count($courses_taken).')',
            'transactions' => 'Transactions ('.count($transactions).')',
        ];
        $unpaid = collect($transactions)->where('payment_status', 'Unpaid')->count();
        $meta = array_filter([
            $detail->nis ? 'NIS '.$detail->nis : null,
            $detail->age !== null ? $detail->age.' yrs' : null,
            $detail->nama_orang_tua ? 'Parent: '.$detail->nama_orang_tua : null,
        ]);
    @endphp

    <div class="card summary-card">
        <div class="summary-head">
            <div>
                <h1 class="page-title summary-name">{{ $detail->LongName }}</h1>
                <p class="summary-meta">{{ implode(' · ', $meta) }}</p>
            </div>
            <x-status-badge :status="$detail->Status" />
        </div>
        <div class="stat-grid">
            <div class="stat"><span class="stat-label">Quota</span><span class="stat-value">{{ $detail->Quota ?? 0 }}</span></div>
            <div class="stat"><span class="stat-label">Classes</span><span class="stat-value">{{ count($courses_taken) }}</span></div>
            <div class="stat"><span class="stat-label">Unpaid</span><span class="stat-value">{{ $unpaid }}</span></div>
        </div>
    </div>

    <x-tabs :tabs="$tabs" :active="$tab" />

    <div class="tab-content">
        <x-tab-pane name="profile" :active="$tab === 'profile'">
            <form class="card card-tabbed" method="post" action="{{ staff_route('student.update', $detail->id) }}">
                @csrf
                <input type="hidden" name="return_url" value="{{ $return_url }}">
                <div class="card-body">
                    <x-form.error-summary />

                    <x-form.section title="Identity">
                        <x-form.field name="nis" label="NIS" :value="$detail->nis" />
                        <x-form.field name="LongName" label="Long name" :value="$detail->LongName" required />
                        <x-form.field name="ShortName" label="Nick name" :value="$detail->ShortName" />
                        <x-form.field name="dob" label="Date of birth" type="date" :value="$detail->dob" required />
                    </x-form.section>

                    <x-form.section title="Contact">
                        <x-form.field name="nama_orang_tua" label="Parent name" :value="$detail->nama_orang_tua" required />
                        <x-form.field name="Email" label="Email" type="email" :value="$detail->Email" required />
                        <x-form.field name="Phone1" label="First phone" type="tel" :value="$detail->Phone1" required />
                        <x-form.field name="Phone2" label="Second phone" type="tel" :value="$detail->Phone2" />
                        <x-form.field name="Whatsapp" label="WhatsApp" type="tel" :value="$detail->Whatsapp" required />
                        <x-form.field name="Instagram" label="Instagram" :value="$detail->Instagram" />
                        <x-form.field name="Line" label="Line" :value="$detail->Line" />
                        <x-form.field name="city" label="City" :value="$detail->City" required />
                        <x-form.field name="kode_pos" label="Postal code" :value="$detail->kode_pos" required />
                        <x-form.field name="Address" label="Address" type="textarea" :value="$detail->Address" required wide />
                    </x-form.section>

                    <x-form.section title="Payment">
                        <x-form.field name="bank" label="Bank name" :value="$detail->bank" />
                        <x-form.field name="sender" label="Sender name" :value="$detail->pengirim" required />
                        <x-form.field name="accountno" label="Account number" :value="$detail->rek" required />
                    </x-form.section>

                    <x-form.section title="Status & quota">
                        <x-form.field name="status" label="Status" type="select" :value="$detail->Status" required
                                      :options="['aktif' => 'Active', 'non-aktif' => 'Inactive', 'trial' => 'Trial']" />
                        <x-form.field name="is_new" label="New student" type="select" :value="$detail->is_new == 1 ? 'Yes' : 'No'" required
                                      :options="['Yes' => 'Yes', 'No' => 'No']" />
                        <div class="form-field">
                            <x-form.field name="Quota" label="Quota" type="number" min="0" :value="$detail->Quota ?? 0" required />
                            {{-- Quota when this page was opened: the server refuses a manual Quota edit if attendance changed it meanwhile. --}}
                            <input type="hidden" name="Quota_original" value="{{ old('Quota_original', $detail->Quota ?? 0) }}">
                        </div>
                        <x-form.field name="EnrollDate" label="Enroll date" type="date" :value="$detail->EnrollDate" />
                    </x-form.section>

                    <div class="save-bar">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </form>
        </x-tab-pane>

        <x-tab-pane name="classes" :active="$tab === 'classes'">
            <div class="card card-tabbed">
                <div class="card-body">
                    <div class="d-flex justify-content-end mb-2">
                        <a href="{{ staff_route('student.class.create', $detail->id) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add class</a>
                    </div>
                    @if (count($courses_taken) === 0)
                        <x-empty-state icon="journal" title="Not enrolled in any class yet" />
                    @else
                        <table class="table">
                            <thead><tr><th scope="col">Course</th><th scope="col">Price</th><th scope="col">Quota</th></tr></thead>
                            <tbody>
                            @foreach ($courses_taken as $data)
                                @php
                                    if($data->class_name == 'Pointe Class') $quota_pay = 4;
                                    else if($data->class_name == 'Intensive Kids' || $data->class_name == 'Intensive Class')$quota_pay = 12;
                                    else $quota_pay = 3;
                                @endphp
                                <tr>
                                    <td>{{ $data->class_name }}</td>
                                    <td>Rp{{ number_format($data->class_price) }}</td>
                                    <td>{{ $data->quota == 0 ? $quota_pay : $data->quota }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </x-tab-pane>

        <x-tab-pane name="transactions" :active="$tab === 'transactions'">
            <div class="card card-tabbed">
                <div class="card-body">
                    @if (count($transactions) === 0)
                        <x-empty-state icon="receipt" title="No transactions yet" />
                    @else
                        <table class="table">
                            <thead>
                            <tr><th scope="col">Date</th><th scope="col">Class</th><th scope="col">Total</th><th scope="col">Paid on</th><th scope="col">Status</th><th scope="col">Quota</th></tr>
                            </thead>
                            <tbody>
                            @foreach ($transactions as $trans)
                                @php($total = \App\Support\Discount::total($trans->price, $trans->discount))
                                <tr>
                                    <td><a href="{{ staff_route('transaction.show', $trans->id) }}">{{ $trans->transaction_date }}</a></td>
                                    <td>{{ $trans->class_name }}</td>
                                    <td>
                                        Rp{{ number_format($total ?? $trans->price) }}
                                        @if ($total === null)
                                            <span class="status-badge status-badge-warning">Invalid discount</span>
                                        @endif
                                    </td>
                                    <td>{{ $trans->transaction_payment ?? 'Waiting for payment' }}</td>
                                    <td><x-status-badge :status="$trans->payment_status" /></td>
                                    <td>{{ $trans->transaction_quota ?? 0 }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </x-tab-pane>
    </div>
@endsection
