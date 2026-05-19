import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, voucher, cashBankAccount }) {
    const postVoucher = () => { if (confirm('Post voucher?')) router.post(route('vouchers.post', voucher.id)); };
    const cancelVoucher = () => { if (confirm('Cancel voucher?')) router.post(route('vouchers.cancel', voucher.id)); };
    const destroy = () => { if (confirm('Hapus voucher?')) router.delete(route('vouchers.destroy', voucher.id)); };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Voucher ${voucher.voucher_number}`} />
            <div className="py-12"><div className="max-w-5xl mx-auto sm:px-6 lg:px-8"><div className="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                <div className="flex justify-between items-center"><h1 className="text-2xl font-semibold">Voucher {voucher.voucher_number}</h1><div className="space-x-2"><Link href={route('vouchers.index')} className="text-gray-600">Back</Link>{voucher.status === 'draft' && <button onClick={postVoucher} className="text-green-600">Post</button>}{['draft', 'posted'].includes(voucher.status) && <button onClick={cancelVoucher} className="text-orange-600">Cancel</button>}{voucher.status === 'draft' && <button onClick={destroy} className="text-red-600">Delete</button>}</div></div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <Info label="Type" value={voucher.voucher_type} />
                    <Info label="Date" value={voucher.voucher_date} />
                    <Info label="Status" value={voucher.status} />
                    <Info label="Cash/Bank" value={`${cashBankAccount?.code || '-'} - ${cashBankAccount?.name || '-'}`} />
                    <Info label="Counterpart" value={`${voucher.counterpart_account?.code || '-'} - ${voucher.counterpart_account?.name || '-'}`} />
                    <Info label="Amount" value={Number(voucher.amount).toLocaleString()} />
                    <Info label="Reference" value={voucher.reference_number || '-'} />
                    <Info label="Journal" value={voucher.journal_entry?.journal_number || '-'} />
                </div>
                <div><div className="text-sm font-medium text-gray-700">Notes</div><div className="mt-1 text-sm text-gray-600">{voucher.notes || '-'}</div></div>
                {voucher.journal_entry?.lines?.length > 0 && <table className="min-w-full text-sm"><thead><tr className="border-b"><th className="text-left py-2">Account</th><th className="text-right py-2">Debit</th><th className="text-right py-2">Credit</th></tr></thead><tbody>{voucher.journal_entry.lines.map((line) => <tr key={line.id} className="border-b"><td className="py-2">{line.account?.code} - {line.account?.name}</td><td className="py-2 text-right">{Number(line.debit).toLocaleString()}</td><td className="py-2 text-right">{Number(line.credit).toLocaleString()}</td></tr>)}</tbody></table>}
            </div></div></div>
        </AuthenticatedLayout>
    );
}

function Info({ label, value }) { return <div><div className="text-xs text-gray-500">{label}</div><div className="font-medium capitalize">{value}</div></div>; }
