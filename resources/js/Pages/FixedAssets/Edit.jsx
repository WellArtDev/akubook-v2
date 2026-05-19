import Create from './Create'
import { useForm } from '@inertiajs/react'

export default function Edit({ auth, asset, accounts, statuses }) {
  const { data, setData, put, processing, errors } = useForm({
    asset_code: asset.asset_code,
    name: asset.name,
    category: asset.category || '',
    acquisition_date: asset.acquisition_date,
    acquisition_cost: asset.acquisition_cost,
    useful_life_months: asset.useful_life_months,
    residual_value: asset.residual_value,
    status: asset.status,
    asset_account_id: asset.asset_account_id,
    accumulated_depreciation_account_id: asset.accumulated_depreciation_account_id,
    depreciation_expense_account_id: asset.depreciation_expense_account_id,
    notes: asset.notes || '',
  })

  const submit = (e) => {
    e.preventDefault()
    put(route('fixed-assets.update', asset.id))
  }

  return <Create auth={auth} accounts={accounts} statuses={statuses} formOverride={{ data, setData, submit, processing, errors }} />
}
