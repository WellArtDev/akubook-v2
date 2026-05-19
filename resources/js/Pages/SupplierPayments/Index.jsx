import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Index({ auth, payments, filters }) {
    const { data, setData, get } = useForm({
        status: filters?.status ?? '',
        search: filters?.search ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        get(route('supplier-payments.index'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Supplier Payments</h2>}
        >
            <Head title="Supplier Payments" />

            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4">
                    <div className="flex items-center justify-between">
                        <form onSubmit={submit} className="flex gap-2">
                            <input className="rounded border-gray-300" placeholder="Search" value={data.search} onChange={(e) => setData('search', e.target.value)} />
                            <select className="rounded border-gray-300" value={data.status} onChange={(e) => setData('status', e.target.value)}>
                                <option value="">All status</option>
                                <option value="draft">Draft</option>
                                <option value="posted">Posted</option>
                                <option value="voided">Voided</option>
                            </select>
                            <button className="rounded bg-gray-800 px-3 py-2 text-white">Filter</button>
                        </form>
                        <Link href={route('supplier-payments.create')} className="rounded bg-indigo-600 px-3 py-2 text-white">New Payment</Link>
                    </div>

                    <div className="overflow-hidden rounded bg-white shadow">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-2 text-left text-xs font-medium uppercase">Number</th>
                                    <th className="px-4 py-2 text-left text-xs font-medium uppercase">Date</th>
                                    <th className="px-4 py-2 text-left text-xs font-medium uppercase">Supplier</th>
                                    <th className="px-4 py-2 text-left text-xs font-medium uppercase">Amount</th>
                                    <th className="px-4 py-2 text-left text-xs font-medium uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 bg-white">
                                {payments.data.map((payment) => (
                                    <tr key={payment.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-2"><Link className="text-indigo-600" href={route('supplier-payments.show', payment.id)}>{payment.payment_number}</Link></td>
                                        <td className="px-4 py-2">{payment.payment_date}</td>
                                        <td className="px-4 py-2">{payment.supplier?.name}</td>
                                        <td className="px-4 py-2">{payment.total_amount}</td>
                                        <td className="px-4 py-2">{payment.status}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
