import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState, useEffect } from 'react';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        branch_id: '',
        password: '',
        password_confirmation: '',
    });

    const [branches, setBranches] = useState([]);
    const [loadingBranches, setLoadingBranches] = useState(true);
    const [passwordStrength, setPasswordStrength] = useState({
        score: 0,
        label: 'Lemah',
        color: 'red',
        checks: {
            minLength: false,
            uppercase: false,
            lowercase: false,
            number: false,
            special: false,
        }
    });

    useEffect(() => {
        fetch('/api/branches')
            .then(res => res.json())
            .then(data => {
                setBranches(data);
                setLoadingBranches(false);
            })
            .catch(err => {
                console.error('Failed to load branches:', err);
                setLoadingBranches(false);
            });
    }, []);

    useEffect(() => {
        if (data.password) {
            const checks = {
                minLength: data.password.length >= 8,
                uppercase: /[A-Z]/.test(data.password),
                lowercase: /[a-z]/.test(data.password),
                number: /[0-9]/.test(data.password),
                special: /[!@#$%^&*]/.test(data.password),
            };

            const score = Object.values(checks).filter(Boolean).length;
            let label = 'Lemah';
            let color = 'red';

            if (score >= 4) {
                label = 'Kuat';
                color = 'green';
            } else if (score >= 3) {
                label = 'Sedang';
                color = 'yellow';
            }

            setPasswordStrength({ score, label, color, checks });
        } else {
            setPasswordStrength({
                score: 0,
                label: 'Lemah',
                color: 'red',
                checks: {
                    minLength: false,
                    uppercase: false,
                    lowercase: false,
                    number: false,
                    special: false,
                }
            });
        }
    }, [data.password]);

    const submit = (e) => {
        e.preventDefault();

        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Daftar" />

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="name" value="Nama Lengkap" />

                    <TextInput
                        id="name"
                        name="name"
                        value={data.name}
                        className="mt-1 block w-full"
                        autoComplete="name"
                        isFocused={true}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />

                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        onChange={(e) => setData('email', e.target.value)}
                        required
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="branch_id" value="Cabang" />

                    <select
                        id="branch_id"
                        name="branch_id"
                        value={data.branch_id}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        onChange={(e) => setData('branch_id', e.target.value)}
                        required
                        disabled={loadingBranches}
                    >
                        <option value="">
                            {loadingBranches ? 'Memuat...' : 'Pilih Cabang'}
                        </option>
                        {branches.map((branch) => (
                            <option key={branch.id} value={branch.id}>
                                {branch.name}
                            </option>
                        ))}
                    </select>

                    <InputError message={errors.branch_id} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) => setData('password', e.target.value)}
                        required
                    />

                    <InputError message={errors.password} className="mt-2" />

                    {data.password && (
                        <div className="mt-3 space-y-2">
                            <div className="flex items-center justify-between">
                                <span className="text-sm font-medium text-gray-700">
                                    Kekuatan Password
                                </span>
                                <span className={`text-sm font-semibold text-${passwordStrength.color}-600`}>
                                    {passwordStrength.label}
                                </span>
                            </div>
                            <div className="h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                                <div
                                    className={`h-full bg-${passwordStrength.color}-500 transition-all duration-300`}
                                    style={{ width: `${(passwordStrength.score / 5) * 100}%` }}
                                />
                            </div>
                            <div className="mt-2 space-y-1">
                                <p className="text-xs font-medium text-gray-700">Persyaratan Password:</p>
                                <div className="space-y-1">
                                    <div className={`flex items-center text-xs ${passwordStrength.checks.minLength ? 'text-green-600' : 'text-gray-500'}`}>
                                        <span className="mr-2">{passwordStrength.checks.minLength ? '✓' : '○'}</span>
                                        Minimal 8 karakter
                                    </div>
                                    <div className={`flex items-center text-xs ${passwordStrength.checks.uppercase ? 'text-green-600' : 'text-gray-500'}`}>
                                        <span className="mr-2">{passwordStrength.checks.uppercase ? '✓' : '○'}</span>
                                        Minimal 1 huruf besar
                                    </div>
                                    <div className={`flex items-center text-xs ${passwordStrength.checks.lowercase ? 'text-green-600' : 'text-gray-500'}`}>
                                        <span className="mr-2">{passwordStrength.checks.lowercase ? '✓' : '○'}</span>
                                        Minimal 1 huruf kecil
                                    </div>
                                    <div className={`flex items-center text-xs ${passwordStrength.checks.number ? 'text-green-600' : 'text-gray-500'}`}>
                                        <span className="mr-2">{passwordStrength.checks.number ? '✓' : '○'}</span>
                                        Minimal 1 angka
                                    </div>
                                    <div className={`flex items-center text-xs ${passwordStrength.checks.special ? 'text-green-600' : 'text-gray-500'}`}>
                                        <span className="mr-2">{passwordStrength.checks.special ? '✓' : '○'}</span>
                                        Minimal 1 karakter khusus (!@#$%^&*)
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                <div className="mt-4">
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Konfirmasi Password"
                    />

                    <TextInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                        required
                    />

                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                </div>

                <div className="mt-4 flex items-center justify-end">
                    <Link
                        href={route('login')}
                        className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Sudah punya akun?
                    </Link>

                    <PrimaryButton className="ms-4" disabled={processing}>
                        Daftar
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
