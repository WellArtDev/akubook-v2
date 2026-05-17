import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ auth, journals, filters }) {
    const [search, setSearch] = useState(filters.search || '');

    const getStatusBadge = (status) => {
        const badges = {
            draft: <span className="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Draft</span>,
            posted: <span className="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Posted</span>,
            reversed: <span className="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Reversed</span>,
        };
        return badges[status] || status;
    };

    const handlePost = (journal) => {
        if (confirm(`Post journal entry ${journal.journal_number}?`)) {
            router.post(route('journal-entries.post', journal.id));
        }
    };

    const handleDelete = (journal) => {
        if (confirm(`Hapus journal entry ${journal.journal_number}?`)) {
            router.delete(route('journal-entries.destroy', journal.id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Journal Entries</h2>}
        >
            <Head title="Journal Entries" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-6">
                                <Link
                                    href={route('journal-entries.create')}
                                    className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                >
                                    Tambah Journal Entry
                                </Link>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Journal Number</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Date</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Description</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Total</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                            <th className="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {journals.data.map((journal) => (
                                            <tr key={journal.id}>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <Link href={route('journal-entries.show', journal.id)} className="text-blue-600 hover:text-blue-900">
                                                        {journal.journal_number}
                                                    </Link>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">{journal.journal_date}</td>
                                                <td className="px-6 py-4">{journal.description}</td>
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    {new Intl.NumberFormat('id-ID').format(journal.total_debit)}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">{getStatusBadge(journal.status)}</td>
                                                <td className="px-6 py-4 text-right whitespace-nowrap">
                                                    <div className="flex justify-end gap-2">
                                                        {journal.status === 'draft' && (
                                                            <>
                                                                <Link href={route('journal-entries.edit', journal.id)} className="text-blue-600 hover:text-blue-900">
                                                                    Edit
                                                                </Link>
                                                                <button onClick={() => handlePost(journal)} className="text-green-600 hover:text-green-900">
                                                                    Post
                                                                </button>
                                                                <button onClick={() => handleDelete(journal)} className="text-red-600 hover:text-red-900">
                                                                    Hapus
                                                                </button>
                                                            </>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {journals.links.length > 3 && (
                                <div className="flex justify-center mt-6 gap-2">
                                    {journals.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            className={`px-3 py-1 rounded ${
                                                link.active ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                            } ${!link.url && 'opacity-50 cursor-not-allowed'}`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
