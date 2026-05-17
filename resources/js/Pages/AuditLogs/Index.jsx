import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Index({ logs }) {
    return (
        <AuthenticatedLayout header={<h2>Log Audit</h2>}>
            <Head title="Log Audit" />
            <div className="py-12">
                <div className="mx-auto max-w-7xl">
                    <div className="bg-white overflow-hidden shadow-sm">
                        <table className="min-w-full">
                            <thead><tr><th>Tanggal</th><th>User</th><th>Event</th><th>Aksi</th></tr></thead>
                            <tbody>
                                {logs.data.map(log => (
                                    <tr key={log.id}>
                                        <td>{new Date(log.created_at).toLocaleString('id-ID')}</td>
                                        <td>{log.user?.name || '-'}</td>
                                        <td>{log.event}</td>
                                        <td><Link href={route('audit-logs.show', log.id)}>Detail</Link></td>
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
