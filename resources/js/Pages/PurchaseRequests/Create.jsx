import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
const Button = ({ className = '', variant = 'default', ...props }) => (
    <button
        className={`${variant === 'outline' ? 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50' : 'bg-indigo-600 text-white hover:bg-indigo-700'} inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest disabled:opacity-25 transition ${className}`}
        {...props}
    />
);

const Input = ({ className = '', ...props }) => (
    <input className={`border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm ${className}`} {...props} />
);

const Label = ({ className = '', ...props }) => (
    <label className={`block font-medium text-sm text-gray-700 ${className}`} {...props} />
);

const Textarea = ({ className = '', ...props }) => (
    <textarea className={`border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm ${className}`} {...props} />
);

export default function Create({ auth }) {
    const { data, setData, post, processing, errors } = useForm({
        pr_date: new Date().toISOString().split('T')[0],
        department_id: '',
        required_date: '',
        justification: '',
        lines: [
            {
                product_code: '',
                product_name: '',
                description: '',
                quantity: '',
                unit: 'pcs',
                estimated_price: '',
            },
        ],
    });

    const addLine = () => {
        setData('lines', [
            ...data.lines,
            {
                product_code: '',
                product_name: '',
                description: '',
                quantity: '',
                unit: 'pcs',
                estimated_price: '',
            },
        ]);
    };

    const removeLine = (index) => {
        setData(
            'lines',
            data.lines.filter((_, i) => i !== index)
        );
    };

    const updateLine = (index, field, value) => {
        const newLines = [...data.lines];
        newLines[index][field] = value;
        setData('lines', newLines);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('purchase-requests.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Create Purchase Request</h2>}
        >
            <Head title="Create Purchase Request" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <form onSubmit={handleSubmit}>
                                {/* Header Info */}
                                <div className="grid grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <Label htmlFor="pr_date">PR Date *</Label>
                                        <Input
                                            id="pr_date"
                                            type="date"
                                            value={data.pr_date}
                                            onChange={(e) => setData('pr_date', e.target.value)}
                                            required
                                        />
                                        {errors.pr_date && <p className="text-red-500 text-sm mt-1">{errors.pr_date}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="required_date">Required Date *</Label>
                                        <Input
                                            id="required_date"
                                            type="date"
                                            value={data.required_date}
                                            onChange={(e) => setData('required_date', e.target.value)}
                                            required
                                        />
                                        {errors.required_date && <p className="text-red-500 text-sm mt-1">{errors.required_date}</p>}
                                    </div>

                                    <div>
                                        <Label htmlFor="department_id">Department *</Label>
                                        <Input
                                            id="department_id"
                                            type="number"
                                            placeholder="Department ID (temporary)"
                                            value={data.department_id}
                                            onChange={(e) => setData('department_id', e.target.value)}
                                            required
                                        />
                                        {errors.department_id && <p className="text-red-500 text-sm mt-1">{errors.department_id}</p>}
                                    </div>

                                    <div className="col-span-2">
                                        <Label htmlFor="justification">Justification</Label>
                                        <Textarea
                                            id="justification"
                                            value={data.justification}
                                            onChange={(e) => setData('justification', e.target.value)}
                                            rows={3}
                                        />
                                        {errors.justification && <p className="text-red-500 text-sm mt-1">{errors.justification}</p>}
                                    </div>
                                </div>

                                {/* Line Items */}
                                <div className="mb-6">
                                    <div className="flex justify-between items-center mb-4">
                                        <h4 className="text-lg font-medium">Line Items</h4>
                                        <Button type="button" onClick={addLine} variant="outline">
                                            Add Line
                                        </Button>
                                    </div>

                                    {data.lines.map((line, index) => (
                                        <div key={index} className="border p-4 rounded-lg mb-4">
                                            <div className="flex justify-between items-center mb-4">
                                                <h5 className="font-medium">Line {index + 1}</h5>
                                                {data.lines.length > 1 && (
                                                    <Button
                                                        type="button"
                                                        onClick={() => removeLine(index)}
                                                        variant="destructive"
                                                        size="sm"
                                                    >
                                                        Remove
                                                    </Button>
                                                )}
                                            </div>

                                            <div className="grid grid-cols-2 gap-4">
                                                <div>
                                                    <Label>Product Code</Label>
                                                    <Input
                                                        value={line.product_code}
                                                        onChange={(e) => updateLine(index, 'product_code', e.target.value)}
                                                    />
                                                </div>

                                                <div>
                                                    <Label>Product Name *</Label>
                                                    <Input
                                                        value={line.product_name}
                                                        onChange={(e) => updateLine(index, 'product_name', e.target.value)}
                                                        required
                                                    />
                                                </div>

                                                <div className="col-span-2">
                                                    <Label>Description</Label>
                                                    <Textarea
                                                        value={line.description}
                                                        onChange={(e) => updateLine(index, 'description', e.target.value)}
                                                        rows={2}
                                                    />
                                                </div>

                                                <div>
                                                    <Label>Quantity *</Label>
                                                    <Input
                                                        type="number"
                                                        step="0.001"
                                                        value={line.quantity}
                                                        onChange={(e) => updateLine(index, 'quantity', e.target.value)}
                                                        required
                                                    />
                                                </div>

                                                <div>
                                                    <Label>Unit *</Label>
                                                    <Input
                                                        value={line.unit}
                                                        onChange={(e) => updateLine(index, 'unit', e.target.value)}
                                                        required
                                                    />
                                                </div>

                                                <div>
                                                    <Label>Estimated Price *</Label>
                                                    <Input
                                                        type="number"
                                                        step="0.01"
                                                        value={line.estimated_price}
                                                        onChange={(e) => updateLine(index, 'estimated_price', e.target.value)}
                                                        required
                                                    />
                                                </div>

                                                <div>
                                                    <Label>Line Total</Label>
                                                    <Input
                                                        value={
                                                            line.quantity && line.estimated_price
                                                                ? (parseFloat(line.quantity) * parseFloat(line.estimated_price)).toFixed(2)
                                                                : '0.00'
                                                        }
                                                        disabled
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                {/* Actions */}
                                <div className="flex justify-end gap-4">
                                    <Link href={route('purchase-requests.index')}>
                                        <Button type="button" variant="outline">
                                            Cancel
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'Creating...' : 'Create Purchase Request'}
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
