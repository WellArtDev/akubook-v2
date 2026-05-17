import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Show({ log }) {
    return (
        <AuthenticatedLayout header={<h2>Detail Log Audit</h2>}>
            <Head title="Detail Log" />
            <div className="py-12">
                <div className="mx-auto max-w-7xl">
                    <div className="bg-white p-6">
                        <dl><dt>Event:</dt><dd>{log.event}</dd><dt>User:</dt><dd>{log.user?.name}</dd><dt>Waktu:</dt><dd>{new Date(log.created_at).toLocaleString('id-ID')}</dd></dl>
                        <Link href={route('audit-logs.index')} className="mt-4 inline-block">Kembali</Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
