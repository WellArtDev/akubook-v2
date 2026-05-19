import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

const modules = [
    { title: 'Role Dashboard', route: 'role-dashboard.index', desc: 'KPI operasional sesuai role' },
    { title: 'Governance Dashboard', route: 'governance-dashboard-v2.index', desc: 'Retention, alert, export compliance' },
    { title: 'Sales Orders', route: 'sales-orders.index', desc: 'Kelola pesanan penjualan' },
    { title: 'Purchase Orders', route: 'purchase-orders.index', desc: 'Kelola pesanan pembelian' },
    { title: 'Customers', route: 'customers.index', desc: 'Master customer' },
    { title: 'Suppliers', route: 'suppliers.index', desc: 'Master supplier' },
];

export default function Dashboard({ auth }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>}
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 className="text-lg font-semibold text-gray-900">Selamat datang, {auth.user.name}</h3>
                        <p className="text-sm text-gray-600 mt-1">Pilih modul kerja dari shortcut di bawah.</p>
                    </div>
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {modules.map((module) => (
                            <Link key={module.route} href={route(module.route)} className="bg-white p-5 rounded-lg shadow-sm border border-gray-100 hover:border-indigo-300 hover:shadow transition">
                                <p className="text-base font-semibold text-gray-900">{module.title}</p>
                                <p className="text-sm text-gray-600 mt-1">{module.desc}</p>
                            </Link>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
