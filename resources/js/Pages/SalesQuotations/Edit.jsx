import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

const emptyLine = { item_id: '', description: '', quantity: 1, unit: '', unit_price: 0, discount_percentage: 0, discount_amount: 0, tax_percentage: 11, notes: '' };

export default function Edit({ auth, quotation, customers, items }) {
    const { data, setData, put, processing, errors } = useForm({
        quotation_date: quotation.quotation_date,
        valid_until: quotation.valid_until,
        customer_id: quotation.customer_id,
        customer_contact_id: quotation.customer_contact_id || '',
        reference: quotation.reference || '',
        payment_terms: quotation.payment_terms || '',
        delivery_terms: quotation.delivery_terms || '',
        notes: quotation.notes || '',
        discount_type: quotation.discount_type,
        discount_value: quotation.discount_value,
        lines: quotation.lines.map((line) => ({ item_id: line.item_id, description: line.description || '', quantity: line.quantity, unit: line.unit, unit_price: line.unit_price, discount_percentage: line.discount_percentage, discount_amount: line.discount_amount, tax_percentage: line.tax_percentage, notes: line.notes || '' })),
    });

    const selectedCustomer = customers.find((customer) => customer.id === parseInt(data.customer_id));
    const contacts = selectedCustomer?.contacts || [];
    const money = (value) => new Intl.NumberFormat('id-ID').format(value || 0);
    const lineTotal = (line) => Math.max((parseFloat(line.quantity || 0) * parseFloat(line.unit_price || 0)) - parseFloat(line.discount_amount || 0), 0);
    const subtotal = data.lines.reduce((sum, line) => sum + lineTotal(line), 0);
    const discountAmount = data.discount_type === 'percentage' ? subtotal * parseFloat(data.discount_value || 0) / 100 : parseFloat(data.discount_value || 0);
    const subtotalAfterDiscount = Math.max(subtotal - discountAmount, 0);
    const taxAmount = subtotalAfterDiscount * 0.11;

    const updateLine = (index, field, value) => {
        const lines = [...data.lines];
        lines[index][field] = value;
        if (field === 'item_id') {
            const item = items.find((item) => item.id === parseInt(value));
            if (item) {
                lines[index].description = item.description || item.name;
                lines[index].unit = item.unit || '';
                lines[index].unit_price = item.selling_price || 0;
            }
        }
        if (field === 'discount_percentage') {
            lines[index].discount_amount = (parseFloat(lines[index].quantity || 0) * parseFloat(lines[index].unit_price || 0)) * parseFloat(value || 0) / 100;
        }
        setData('lines', lines);
    };

    const setCustomer = (value) => {
        const customer = customers.find((customer) => customer.id === parseInt(value));
        setData({ ...data, customer_id: value, customer_contact_id: '', payment_terms: customer?.payment_terms ? `Net ${customer.payment_terms}` : data.payment_terms });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Edit Sales Quotation - {quotation.quotation_number}</h2>}>
            <Head title={`Edit Sales Quotation - ${quotation.quotation_number}`} />
            <div className="py-12"><div className="mx-auto max-w-7xl sm:px-6 lg:px-8"><form onSubmit={(e) => { e.preventDefault(); put(route('sales-quotations.update', quotation.id)); }} className="bg-white p-6 shadow-sm sm:rounded-lg space-y-6">
                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <label className="block text-sm font-medium text-gray-700">Tanggal<input type="date" value={data.quotation_date} onChange={(e) => setData('quotation_date', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300" /></label>
                    <label className="block text-sm font-medium text-gray-700">Valid Until<input type="date" value={data.valid_until} onChange={(e) => setData('valid_until', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300" /></label>
                    <label className="block text-sm font-medium text-gray-700">Reference<input value={data.reference} onChange={(e) => setData('reference', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300" /></label>
                    <label className="block text-sm font-medium text-gray-700">Customer<select value={data.customer_id} onChange={(e) => setCustomer(e.target.value)} className="mt-1 block w-full rounded-md border-gray-300"><option value="">Pilih Customer</option>{customers.map((customer) => <option key={customer.id} value={customer.id}>{customer.code} - {customer.name}</option>)}</select></label>
                    <label className="block text-sm font-medium text-gray-700">Contact<select value={data.customer_contact_id || ''} onChange={(e) => setData('customer_contact_id', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300"><option value="">Pilih Contact</option>{contacts.map((contact) => <option key={contact.id} value={contact.id}>{contact.name} - {contact.email || contact.phone || '-'}</option>)}</select></label>
                    <label className="block text-sm font-medium text-gray-700">Payment Terms<input value={data.payment_terms} onChange={(e) => setData('payment_terms', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300" /></label>
                    <label className="block text-sm font-medium text-gray-700">Delivery Terms<input value={data.delivery_terms} onChange={(e) => setData('delivery_terms', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300" /></label>
                    <label className="block text-sm font-medium text-gray-700">Discount Type<select value={data.discount_type} onChange={(e) => setData('discount_type', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300"><option value="percentage">Percentage</option><option value="amount">Amount</option></select></label>
                    <label className="block text-sm font-medium text-gray-700">Discount Value<input type="number" step="0.01" value={data.discount_value} onChange={(e) => setData('discount_value', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300" /></label>
                </div>
                {Object.keys(errors).length > 0 && <p className="text-sm text-red-600">Validasi gagal. Cek field.</p>}
                <label className="block text-sm font-medium text-gray-700">Notes<textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300" rows="3" /></label>
                <div className="overflow-x-auto"><div className="mb-3 flex justify-between"><h3 className="font-semibold">Line Items</h3><button type="button" onClick={() => setData('lines', [...data.lines, { ...emptyLine }])} className="rounded bg-green-600 px-3 py-1 text-white">Tambah Baris</button></div><table className="min-w-full divide-y divide-gray-200"><thead><tr><th>Item</th><th>Description</th><th>Qty</th><th>Unit</th><th>Price</th><th>Disc %</th><th>Disc Amt</th><th>Tax %</th><th>Total</th><th></th></tr></thead><tbody>{data.lines.map((line, index) => <tr key={index} className="border-t"><td><select value={line.item_id} onChange={(e) => updateLine(index, 'item_id', e.target.value)} className="w-48 rounded-md border-gray-300 text-sm"><option value="">Pilih Item</option>{items.map((item) => <option key={item.id} value={item.id}>{item.code} - {item.name}</option>)}</select></td><td><input value={line.description || ''} onChange={(e) => updateLine(index, 'description', e.target.value)} className="w-56 rounded-md border-gray-300 text-sm" /></td><td><input type="number" step="0.001" value={line.quantity} onChange={(e) => updateLine(index, 'quantity', e.target.value)} className="w-20 rounded-md border-gray-300 text-sm" /></td><td><input value={line.unit} onChange={(e) => updateLine(index, 'unit', e.target.value)} className="w-20 rounded-md border-gray-300 text-sm" /></td><td><input type="number" step="0.01" value={line.unit_price} onChange={(e) => updateLine(index, 'unit_price', e.target.value)} className="w-28 rounded-md border-gray-300 text-sm" /></td><td><input type="number" step="0.01" value={line.discount_percentage} onChange={(e) => updateLine(index, 'discount_percentage', e.target.value)} className="w-20 rounded-md border-gray-300 text-sm" /></td><td><input type="number" step="0.01" value={line.discount_amount} onChange={(e) => updateLine(index, 'discount_amount', e.target.value)} className="w-24 rounded-md border-gray-300 text-sm" /></td><td><input type="number" step="0.01" value={line.tax_percentage} onChange={(e) => updateLine(index, 'tax_percentage', e.target.value)} className="w-20 rounded-md border-gray-300 text-sm" /></td><td className="px-2 text-right">{money(lineTotal(line))}</td><td><button type="button" onClick={() => data.lines.length > 1 && setData('lines', data.lines.filter((_, i) => i !== index))} className="text-red-600">Hapus</button></td></tr>)}</tbody></table></div>
                <div className="ml-auto w-72 space-y-1 text-sm"><div className="flex justify-between"><span>Subtotal</span><span>Rp {money(subtotal)}</span></div><div className="flex justify-between"><span>Discount</span><span>Rp {money(discountAmount)}</span></div><div className="flex justify-between"><span>Tax 11%</span><span>Rp {money(taxAmount)}</span></div><div className="flex justify-between border-t pt-2 text-lg font-bold"><span>Grand Total</span><span>Rp {money(subtotalAfterDiscount + taxAmount)}</span></div></div>
                <div className="flex justify-end gap-2"><Link href={route('sales-quotations.show', quotation.id)} className="rounded bg-gray-200 px-4 py-2">Batal</Link><button disabled={processing} className="rounded bg-blue-600 px-4 py-2 text-white">Update</button></div>
            </form></div></div>
        </AuthenticatedLayout>
    );
}
