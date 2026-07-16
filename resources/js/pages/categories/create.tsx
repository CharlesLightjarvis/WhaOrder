import { Form, Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import CategoryController from '@/actions/App/Http/Controllers/CategoryController';

export default function CategoryCreate() {
    return (
        <>
            <Head title="Créer une catégorie" />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-col space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Créer une catégorie
                    </h1>
                    <p className="text-muted-foreground">
                        Donnez un nom à votre nouvelle catégorie.
                    </p>
                </div>

                <Separator />

                <div className="mx-auto max-w-xl">
                    <Form
                        {...CategoryController.store.form()}
                        className="space-y-6"
                    >
                        {({ processing, errors, clearErrors }) => (
                            <>
                                <div className="space-y-2">
                                    <Label htmlFor="name">Nom *</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        autoFocus
                                        placeholder="Ex : Sacs"
                                        onChange={() => clearErrors('name')}
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="flex justify-end">
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                    >
                                        {processing && (
                                            <Spinner className="mr-2" />
                                        )}
                                        Créer la catégorie
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </>
    );
}

CategoryCreate.layout = (page: React.ReactNode) => (
    <AppLayout
        breadcrumbs={[
            { title: 'Dashboard', href: dashboard() },
            { title: 'Catégories', href: CategoryController.index.url() },
            {
                title: 'Nouvelle catégorie',
                href: CategoryController.create.url(),
            },
        ]}
    >
        {page}
    </AppLayout>
);
