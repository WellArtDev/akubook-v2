import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ auth, suppliers }) {
    const { data, setData, post, processing, errors } = useForm({
        payment_date: new Date().toISOString().slice(0, 10),
        supplier_id: '',
        payment_method: 'bank_transfer',
        reference_number: '',
        total_amount: '',
        notes: '',
        allocations: [],
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('supplier-payments.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Create Supplier Payment</h2>}
        >
            <Head title="Create Supplier Payment" />

            <div className="py-6">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="space-y-4 rounded bg-white p-6 shadow">
                        <input type="date" className="w-full rounded border-gray-300" value={data.payment_date} onChange={(e) => setData('payment_date', e.target.value)} />
                        <select className="w-full rounded border-gray-300" value={data.supplier_id} onChange={(e) => setData('supplier_id', e.target.value)}>
                            <option value="">Select supplier</option>
                            {suppliers.map((supplier) => <option key={supplier.id} value={supplier.id}>{supplier.name}</option>)}
                        </select>
                        <select className="w-full rounded border-gray-300" value={data.payment_method} onChange={(e) => setData('payment_method', e.target.value)}>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                            <option value="giro">Giro</option>
                        </select>
                        <input className="w-full rounded border-gray-300" placeholder="Reference" value={data.reference_number} onChange={(e) => setData('reference_number', e.target.value)} />
                        <input type="number" step="0.01" className="w-full rounded border-gray-300" placeholder="Total amount" value={data.total_amount} onChange={(e) => setData('total_amount', e.target.value)} />
                        <textarea className="w-full rounded border-gray-300" placeholder="Notes" value={data.notes} onChange={(e) => setData('notes', e.target.value)} />
                        {Object.values(errors).length > 0 && <div className="text-sm text-red-600">{Object.values(errors)[0]}</div>}
                        <button disabled={processing} className="rounded bg-indigo-600 px-4 py-2 text-white">Save Payment</button>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
