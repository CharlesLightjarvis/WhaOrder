import { Form, Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import CategoryController from '@/actions/App/Http/Controllers/CategoryController';
import type { Category } from '@/types/category';

type PageProps = {
    category: Category;
};

export default function CategoryEdit() {
    const { category } = usePage<PageProps>().props;

    return (
        <>
            <Head title={`Modifier — ${category.name}`} />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-col space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Modifier la catégorie
                    </h1>
                    <p className="text-muted-foreground">
                        Modifiez le nom de la catégorie.
                    </p>
                </div>

                <Separator />

                <div className="mx-auto max-w-xl">
                    <Form
                        {...CategoryController.update.form(category)}
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
                                        defaultValue={category.name}
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
                                        Enregistrer les modifications
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

function CategoryEditLayout({ children }: { children: React.ReactNode }) {
    const { category } = usePage<PageProps>().props;
    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Dashboard', href: dashboard() },
                {
                    title: 'Catégories',
                    href: CategoryController.index.url(),
                },
                { title: category.name, href: '#' },
            ]}
        >
            {children}
        </AppLayout>
    );
}

CategoryEdit.layout = (page: React.ReactNode) => (
    <CategoryEditLayout>{page}</CategoryEditLayout>
);
