import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Badge } from '@/Components/ui/badge';
import { useState } from 'react';

const statusColors = {
    draft: 'bg-gray-500',
    pending_approval: 'bg-yellow-500',
    approved: 'bg-green-500',
    rejected: 'bg-red-500',
    converted: 'bg-blue-500',
    cancelled: 'bg-gray-400',
};

const statusLabels = {
    draft: 'Draft',
    pending_approval: 'Pending Approval',
    approved: 'Approved',
    rejected: 'Rejected',
    converted: 'Converted to PO',
    cancelled: 'Cancelled',
};

export default function Index({ auth, purchaseRequests, filters }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');

    const handleFilter = () => {
        router.get(route('purchase-requests.index'), {
            search,
            status,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleReset = () => {
        setSearch('');
        setStatus('');
        router.get(route('purchase-requests.index'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Purchase Requests</h2>}
        >
            <Head title="Purchase Requests" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Header */}
                            <div className="flex justify-between items-center mb-6">
                                <h3 className="text-lg font-medium">Purchase Requests</h3>
                                <Link href={route('purchase-requests.create')}>
                                    <Button>Create Purchase Request</Button>
                                </Link>
                            </div>

                            {/* Filters */}
                            <div className="flex gap-4 mb-6">
                                <Input
                                    placeholder="Search PR number or justification..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="max-w-xs"
                                />
                                <select
                                    value={status}
                                    onChange={(e) => setStatus(e.target.value)}
                                    className="border-gray-300 rounded-md shadow-sm"
                                >
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="pending_approval">Pending Approval</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="converted">Converted</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <Button onClick={handleFilter} variant="outline">Filter</Button>
                                <Button onClick={handleReset} variant="ghost">Reset</Button>
                            </div>

                            {/* Table */}
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                PR Number
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Department
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Required Date
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Total Amount
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {purchaseRequests.data.map((pr) => (
                                            <tr key={pr.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {pr.pr_number}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {new Date(pr.pr_date).toLocaleDateString('id-ID')}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {pr.department?.name || '-'}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {new Date(pr.required_date).toLocaleDateString('id-ID')}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    Rp {parseFloat(pr.total_estimated_amount).toLocaleString('id-ID')}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <Badge className={statusColors[pr.status]}>
                                                        {statusLabels[pr.status]}
                                                    </Badge>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <Link
                                                        href={route('purchase-requests.show', pr.id)}
                                                        className="text-indigo-600 hover:text-indigo-900 mr-4"
                                                    >
                                                        View
                                                    </Link>
                                                    {pr.status === 'draft' && (
                                                        <>
                                                            <Link
                                                                href={route('purchase-requests.edit', pr.id)}
                                                                className="text-blue-600 hover:text-blue-900 mr-4"
                                                            >
                                                                Edit
                                                            </Link>
                                                            <button
                                                                onClick={() => {
                                                                    if (confirm('Are you sure?')) {
                                                                        router.delete(route('purchase-requests.destroy', pr.id));
                                                                    }
                                                                }}
                                                                className="text-red-600 hover:text-red-900"
                                                            >
                                                                Delete
                                                            </button>
                                                        </>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {purchaseRequests.links && (
                                <div className="mt-4 flex justify-between items-center">
                                    <div className="text-sm text-gray-700">
                                        Showing {purchaseRequests.from} to {purchaseRequests.to} of {purchaseRequests.total} results
                                    </div>
                                    <div className="flex gap-2">
                                        {purchaseRequests.links.map((link, index) => (
                                            <button
                                                key={index}
                                                onClick={() => link.url && router.get(link.url)}
                                                disabled={!link.url}
                                                className={`px-3 py-1 rounded ${
                                                    link.active
                                                        ? 'bg-indigo-600 text-white'
                                                        : 'bg-white text-gray-700 hover:bg-gray-50'
                                                } ${!link.url && 'opacity-50 cursor-not-allowed'}`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
