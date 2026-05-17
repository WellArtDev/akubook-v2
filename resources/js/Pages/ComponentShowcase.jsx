import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Alert from '@/Components/Alert';
import Loading from '@/Components/Loading';
import Breadcrumb from '@/Components/Breadcrumb';
import Card from '@/Components/Card';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import DangerButton from '@/Components/DangerButton';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import Checkbox from '@/Components/Checkbox';
import { Head } from '@inertiajs/react';
import { useState } from 'react';

export default function ComponentShowcase({ auth }) {
    const [showAlert, setShowAlert] = useState(true);

    const breadcrumbItems = [
        { label: 'Dashboard', href: '/dashboard' },
        { label: 'Components', href: '/components' },
        { label: 'Showcase' },
    ];

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-secondary-800">
                        Component Showcase
                    </h2>
                    <Breadcrumb items={breadcrumbItems} className="mt-2" />
                </div>
            }
        >
            <Head title="Component Showcase" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    {/* Alerts */}
                    <Card title="Alert Components">
                        <div className="space-y-4">
                            <Alert type="success" title="Success!">
                                Your changes have been saved successfully.
                            </Alert>

                            <Alert type="error" title="Error!">
                                There was a problem processing your request.
                            </Alert>

                            <Alert type="warning" title="Warning!">
                                This action cannot be undone.
                            </Alert>

                            <Alert type="info" title="Information">
                                This is an informational message.
                            </Alert>

                            {showAlert && (
                                <Alert
                                    type="info"
                                    title="Dismissible Alert"
                                    dismissible
                                    onDismiss={() => setShowAlert(false)}
                                >
                                    Click the X button to dismiss this alert.
                                </Alert>
                            )}
                        </div>
                    </Card>

                    {/* Loading */}
                    <Card title="Loading Components">
                        <div className="flex flex-wrap gap-8 items-center">
                            <div>
                                <p className="text-sm text-secondary-600 mb-2">Small</p>
                                <Loading size="sm" />
                            </div>
                            <div>
                                <p className="text-sm text-secondary-600 mb-2">Medium</p>
                                <Loading size="md" />
                            </div>
                            <div>
                                <p className="text-sm text-secondary-600 mb-2">Large</p>
                                <Loading size="lg" />
                            </div>
                            <div>
                                <p className="text-sm text-secondary-600 mb-2">Extra Large</p>
                                <Loading size="xl" />
                            </div>
                            <div>
                                <p className="text-sm text-secondary-600 mb-2">With Text</p>
                                <Loading size="md" text="Loading data..." />
                            </div>
                        </div>
                    </Card>

                    {/* Buttons */}
                    <Card title="Button Components">
                        <div className="flex flex-wrap gap-4">
                            <PrimaryButton>Primary Button</PrimaryButton>
                            <SecondaryButton>Secondary Button</SecondaryButton>
                            <DangerButton>Danger Button</DangerButton>
                            <PrimaryButton disabled>Disabled Button</PrimaryButton>
                        </div>
                    </Card>

                    {/* Form Components */}
                    <Card title="Form Components">
                        <div className="space-y-4 max-w-md">
                            <div>
                                <InputLabel htmlFor="name" value="Name" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    className="mt-1 block w-full"
                                    placeholder="Enter your name"
                                />
                            </div>

                            <div>
                                <InputLabel htmlFor="email" value="Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    className="mt-1 block w-full"
                                    placeholder="Enter your email"
                                />
                            </div>

                            <div className="flex items-center">
                                <Checkbox id="remember" />
                                <InputLabel
                                    htmlFor="remember"
                                    value="Remember me"
                                    className="ml-2"
                                />
                            </div>
                        </div>
                    </Card>

                    {/* Colors */}
                    <Card title="AkuBook Brand Colors">
                        <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
                            <div>
                                <div className="h-20 bg-primary-500 rounded-lg mb-2"></div>
                                <p className="text-sm font-medium">Primary</p>
                                <p className="text-xs text-secondary-500">#3b82f6</p>
                            </div>
                            <div>
                                <div className="h-20 bg-secondary-500 rounded-lg mb-2"></div>
                                <p className="text-sm font-medium">Secondary</p>
                                <p className="text-xs text-secondary-500">#64748b</p>
                            </div>
                            <div>
                                <div className="h-20 bg-accent-500 rounded-lg mb-2"></div>
                                <p className="text-sm font-medium">Accent</p>
                                <p className="text-xs text-secondary-500">#10b981</p>
                            </div>
                            <div>
                                <div className="h-20 bg-danger-500 rounded-lg mb-2"></div>
                                <p className="text-sm font-medium">Danger</p>
                                <p className="text-xs text-secondary-500">#ef4444</p>
                            </div>
                            <div>
                                <div className="h-20 bg-warning-500 rounded-lg mb-2"></div>
                                <p className="text-sm font-medium">Warning</p>
                                <p className="text-xs text-secondary-500">#f59e0b</p>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
