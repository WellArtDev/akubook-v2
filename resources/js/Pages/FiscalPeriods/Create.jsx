import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Create({ auth }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        period_type: 'monthly',
        start_date: '',
        end_date: '',
        fiscal_year: new Date().getFullYear(),
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('fiscal-periods.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Tambah Periode Fiskal</h2>}
        >
            <Head title="Tambah Periode Fiskal" />

            <div className="py-12">
                <div className="mx-auto max-w-2xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <form onSubmit={handleSubmit} className="p-6">
                            <div className="space-y-6">
                                {/* Name */}
                                <div>
                                    <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                        Nama Periode <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="e.g., Januari 2026"
                                    />
                                    {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                                </div>

                                {/* Period Type */}
                                <div>
                                    <label htmlFor="period_type" className="block text-sm font-medium text-gray-700">
                                        Tipe Periode <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="period_type"
                                        value={data.period_type}
                                        onChange={(e) => setData('period_type', e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="monthly">Bulanan</option>
                                        <option value="quarterly">Kuartalan</option>
                                        <option value="yearly">Tahunan</option>
                                    </select>
                                    {errors.period_type && <p className="mt-1 text-sm text-red-600">{errors.period_type}</p>}
                                </div>

                                {/* Start Date */}
                                <div>
                                    <label htmlFor="start_date" className="block text-sm font-medium text-gray-700">
                                        Tanggal Mulai <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        id="start_date"
                                        value={data.start_date}
                                        onChange={(e) => setData('start_date', e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    {errors.start_date && <p className="mt-1 text-sm text-red-600">{errors.start_date}</p>}
                                </div>

                                {/* End Date */}
                                <div>
                                    <label htmlFor="end_date" className="block text-sm font-medium text-gray-700">
                                        Tanggal Akhir <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        id="end_date"
                                        value={data.end_date}
                                        onChange={(e) => setData('end_date', e.target.value)}
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    {errors.end_date && <p className="mt-1 text-sm text-red-600">{errors.end_date}</p>}
                                </div>

                                {/* Fiscal Year */}
                                <div>
                                    <label htmlFor="fiscal_year" className="block text-sm font-medium text-gray-700">
                                        Tahun Fiskal <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        id="fiscal_year"
                                        value={data.fiscal_year}
                                        onChange={(e) => setData('fiscal_year', e.target.value)}
                                        min="2000"
                                        max="2100"
                                        className="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    {errors.fiscal_year && <p className="mt-1 text-sm text-red-600">{errors.fiscal_year}</p>}
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="flex items-center justify-end mt-6 gap-3">
                                <Link
                                    href={route('fiscal-periods.index')}
                                    className="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                                >
                                    Batal
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
                                >
                                    {processing ? 'Menyimpan...' : 'Simpan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
