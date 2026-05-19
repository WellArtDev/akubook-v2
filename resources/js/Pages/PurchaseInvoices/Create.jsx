import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Head, useForm, router } from '@inertiajs/react'

export default function Create({ auth, goodsReceipts = [], goodsReceipt = null, lines = [] }) {
  const form = useForm({
    invoice_date: new Date().toISOString().slice(0, 10),
    due_date: new Date().toISOString().slice(0, 10),
    goods_receipt_id: goodsReceipt?.id || '',
    supplier_invoice_number: '',
    tax_invoice_number: '',
    generate_tax_invoice: false,
    notes: '',
    lines: lines.map((line) => ({
      goods_receipt_line_id: line.goods_receipt_line_id,
      invoice_quantity: line.remaining_to_invoice_quantity,
      tax_percentage: 11,
      notes: '',
    })),
  })

  const onSelectGoodsReceipt = (value) => {
    router.get(route('purchase-invoices.create'), { goods_receipt_id: value || undefined }, { preserveState: false, replace: true })
  }

  const updateLine = (index, key, value) => {
    const next = [...form.data.lines]
    next[index] = { ...next[index], [key]: value }
    form.setData('lines', next)
  }

  const availableById = Object.fromEntries(lines.map((line) => [line.goods_receipt_line_id, line]))

  const subtotal = form.data.lines.reduce((sum, line) => {
    const src = availableById[line.goods_receipt_line_id]
    if (!src) return sum
    return sum + (Number(line.invoice_quantity || 0) * Number(src.unit_price || 0))
  }, 0)

  const taxAmount = form.data.lines.reduce((sum, line) => {
    const src = availableById[line.goods_receipt_line_id]
    if (!src) return sum
    const base = Number(line.invoice_quantity || 0) * Number(src.unit_price || 0)
    return sum + base * (Number(line.tax_percentage || 0) / 100)
  }, 0)

  const submit = (e) => {
    e.preventDefault()
    form.post(route('purchase-invoices.store'))
  }

  return (
    <AuthenticatedLayout user={auth.user}>
      <Head title="Create Purchase Invoice" />

      <div className="py-6">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
            <h1 className="text-xl font-semibold">Create Purchase Invoice</h1>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Goods Receipt</label>
                <select
                  value={form.data.goods_receipt_id}
                  onChange={(e) => onSelectGoodsReceipt(e.target.value)}
                  className="w-full border-gray-300 rounded-md"
                >
                  <option value="">Select Goods Receipt</option>
                  {goodsReceipts.map((gr) => (
                    <option key={gr.id} value={gr.id}>{gr.gr_number} - {gr.supplier?.name}</option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Invoice Date</label>
                <input type="date" value={form.data.invoice_date} onChange={(e) => form.setData('invoice_date', e.target.value)} className="w-full border-gray-300 rounded-md" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                <input type="date" value={form.data.due_date} onChange={(e) => form.setData('due_date', e.target.value)} className="w-full border-gray-300 rounded-md" />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <input className="border-gray-300 rounded-md" placeholder="Supplier invoice number" value={form.data.supplier_invoice_number} onChange={(e) => form.setData('supplier_invoice_number', e.target.value)} />
              <input className="border-gray-300 rounded-md" placeholder="Tax invoice number" value={form.data.tax_invoice_number} onChange={(e) => form.setData('tax_invoice_number', e.target.value)} />
              <label className="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" checked={form.data.generate_tax_invoice} onChange={(e) => form.setData('generate_tax_invoice', e.target.checked)} />
                Generate tax invoice
              </label>
            </div>

            <div className="overflow-x-auto border rounded-md">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Product</th>
                    <th className="px-3 py-2 text-right text-xs uppercase text-gray-500">Remaining</th>
                    <th className="px-3 py-2 text-right text-xs uppercase text-gray-500">Qty</th>
                    <th className="px-3 py-2 text-right text-xs uppercase text-gray-500">Unit Price</th>
                    <th className="px-3 py-2 text-right text-xs uppercase text-gray-500">Tax %</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {form.data.lines.map((line, index) => {
                    const src = availableById[line.goods_receipt_line_id]
                    if (!src) return null
                    return (
                      <tr key={line.goods_receipt_line_id}>
                        <td className="px-3 py-2 text-sm">{src.product_name}</td>
                        <td className="px-3 py-2 text-sm text-right">{Number(src.remaining_to_invoice_quantity).toLocaleString('id-ID')}</td>
                        <td className="px-3 py-2">
                          <input
                            type="number"
                            step="0.001"
                            min="0"
                            max={src.remaining_to_invoice_quantity}
                            value={line.invoice_quantity}
                            onChange={(e) => updateLine(index, 'invoice_quantity', e.target.value)}
                            className="w-28 border-gray-300 rounded-md text-right"
                          />
                        </td>
                        <td className="px-3 py-2 text-sm text-right">{Number(src.unit_price || 0).toLocaleString('id-ID')}</td>
                        <td className="px-3 py-2">
                          <input
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            value={line.tax_percentage}
                            onChange={(e) => updateLine(index, 'tax_percentage', e.target.value)}
                            className="w-24 border-gray-300 rounded-md text-right"
                          />
                        </td>
                      </tr>
                    )
                  })}
                  {form.data.lines.length === 0 && <tr><td colSpan={5} className="px-3 py-4 text-center text-sm text-gray-500">Select goods receipt first</td></tr>}
                </tbody>
              </table>
            </div>

            <div className="flex justify-end">
              <div className="w-full md:w-80 space-y-1 text-sm">
                <div className="flex justify-between"><span>Subtotal</span><span>{subtotal.toLocaleString('id-ID')}</span></div>
                <div className="flex justify-between"><span>Tax</span><span>{taxAmount.toLocaleString('id-ID')}</span></div>
                <div className="flex justify-between font-semibold border-t pt-2"><span>Total</span><span>{(subtotal + taxAmount).toLocaleString('id-ID')}</span></div>
              </div>
            </div>

            <div className="flex justify-end">
              <button onClick={submit} disabled={form.processing} className="px-4 py-2 bg-indigo-600 text-white rounded-md">Save Invoice</button>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  )
}
