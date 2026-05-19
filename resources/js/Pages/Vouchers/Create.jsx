import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Create({ auth, cashAccounts, bankAccounts, counterpartAccounts }) {
    const { data, setData, post, processing, errors } = useForm({
        voucher_type: 'payment',
        voucher_date: new Date().toISOString().slice(0, 10),
        cash_bank_type: 'cash',
        cash_bank_account_id: cashAccounts[0]?.id || '',
        counterpart_account_id: counterpartAccounts[0]?.id || '',
        amount: 0,
        reference_number: '',
        notes: '',
    });

    const accounts = data.cash_bank_type === 'cash' ? cashAccounts : bankAccounts;
    const submit = (e) => { e.preventDefault(); post(route('vouchers.store')); };
    const setCashBankType = (type) => {
        setData((values) => ({ ...values, cash_bank_type: type, cash_bank_account_id: (type === 'cash' ? cashAccounts[0]?.id : bankAccounts[0]?.id) || '' }));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Create Voucher" />
            <div className="py-12"><div className="max-w-4xl mx-auto sm:px-6 lg:px-8"><form onSubmit={submit} className="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <div className="flex justify-between items-center"><h1 className="text-2xl font-semibold">Create Voucher</h1><Link href={route('vouchers.index')} className="text-gray-600">Back</Link></div>
                {errors.error && <div className="text-red-600 text-sm">{errors.error}</div>}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Select label="Voucher Type" value={data.voucher_type} onChange={(v) => setData('voucher_type', v)} error={errors.voucher_type} options={[{ id: 'payment', label: 'Payment' }, { id: 'receipt', label: 'Receipt' }]} />
                    <Input label="Voucher Date" type="date" value={data.voucher_date} onChange={(v) => setData('voucher_date', v)} error={errors.voucher_date} />
                    <Select label="Cash/Bank Type" value={data.cash_bank_type} onChange={setCashBankType} error={errors.cash_bank_type} options={[{ id: 'cash', label: 'Cash' }, { id: 'bank', label: 'Bank' }]} />
                    <Select label="Cash/Bank Account" value={data.cash_bank_account_id} onChange={(v) => setData('cash_bank_account_id', v)} error={errors.cash_bank_account_id} options={accounts.map((a) => ({ id: a.id, label: `${a.code} - ${a.name}` }))} />
                    <Select label="Counterpart Account" value={data.counterpart_account_id} onChange={(v) => setData('counterpart_account_id', v)} error={errors.counterpart_account_id} options={counterpartAccounts.map((a) => ({ id: a.id, label: `${a.code} - ${a.name}` }))} />
                    <Input label="Amount" type="number" value={data.amount} onChange={(v) => setData('amount', v)} error={errors.amount} />
                    <Input label="Reference" value={data.reference_number} onChange={(v) => setData('reference_number', v)} error={errors.reference_number} />
                </div>
                <textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="border rounded px-3 py-2 w-full" placeholder="Notes" />
                <button disabled={processing} className="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
            </form></div></div>
        </AuthenticatedLayout>
    );
}

function Input({ label, value, onChange, error, type = 'text' }) { return <div><label className="block text-sm font-medium">{label}</label><input type={type} value={value} onChange={(e) => onChange(e.target.value)} className="border rounded px-3 py-2 w-full" />{error && <div className="text-red-600 text-sm">{error}</div>}</div>; }
function Select({ label, value, onChange, error, options }) { return <div><label className="block text-sm font-medium">{label}</label><select value={value} onChange={(e) => onChange(e.target.value)} className="border rounded px-3 py-2 w-full">{options.map((option) => <option key={option.id} value={option.id}>{option.label}</option>)}</select>{error && <div className="text-red-600 text-sm">{error}</div>}</div>; }
