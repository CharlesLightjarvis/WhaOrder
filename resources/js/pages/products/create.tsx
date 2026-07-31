import { Form, Head } from '@inertiajs/react';
import { PlusIcon, Trash2Icon } from 'lucide-react';
import { useState } from 'react';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import { ImageUpload } from '@/components/image-upload';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import type { Category } from '@/types/category';

type VariantDraft = {
    name: string;
    price: string;
    stock: string;
};

type PageProps = {
    categories: Category[];
};

export default function ProductCreate({ categories }: PageProps) {
    const [categoryId, setCategoryId] = useState('');
    const [isActive, setIsActive] = useState(true);
    const [variants, setVariants] = useState<VariantDraft[]>([]);

    const addVariant = () =>
        setVariants((prev) => [...prev, { name: '', price: '', stock: '' }]);
    const removeVariant = (index: number) =>
        setVariants((prev) => prev.filter((_, i) => i !== index));
    const updateVariant = (
        index: number,
        field: keyof VariantDraft,
        value: string,
    ) =>
        setVariants((prev) =>
            prev.map((variant, i) =>
                i === index ? { ...variant, [field]: value } : variant,
            ),
        );

    const hasVariants = variants.length > 0;
    const variantsStockTotal = variants.reduce(
        (sum, variant) => sum + (Number(variant.stock) || 0),
        0,
    );

    return (
        <>
            <Head title="Créer un produit" />

            <div className="container mx-auto space-y-6 p-4">
                <div className="flex flex-col space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Créer un produit
                    </h1>
                    <p className="text-muted-foreground">
                        Renseignez les informations du produit, ses variantes et
                        ses images.
                    </p>
                </div>

                <Separator />

                <Form {...ProductController.store.form()} className="space-y-8">
                    {({ processing, errors, clearErrors }) => (
                        <>
                            <input
                                type="hidden"
                                name="category_id"
                                value={categoryId}
                            />
                            <input
                                type="hidden"
                                name="is_active"
                                value={isActive ? '1' : '0'}
                            />

                            <div className="space-y-6">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Nom *</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        autoFocus
                                        placeholder="Ex : Sac à main cuir"
                                        onChange={() => clearErrors('name')}
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="description">
                                        Description
                                    </Label>
                                    <textarea
                                        id="description"
                                        name="description"
                                        rows={4}
                                        placeholder="Décrivez le produit..."
                                        onChange={() =>
                                            clearErrors('description')
                                        }
                                        className="flex min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    />
                                    <InputError message={errors.description} />
                                </div>

                                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-12">
                                    <div className="space-y-2 sm:col-span-1 lg:col-span-4">
                                        <Label>Catégorie</Label>
                                        <Select
                                            value={categoryId}
                                            onValueChange={(v) => {
                                                setCategoryId(v);
                                                clearErrors('category_id');
                                            }}
                                        >
                                            <SelectTrigger className="w-full">
                                                <SelectValue placeholder="Choisir une catégorie" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {categories.map((cat) => (
                                                    <SelectItem
                                                        key={cat.id}
                                                        value={String(cat.id)}
                                                    >
                                                        {cat.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError
                                            message={errors.category_id}
                                        />
                                    </div>

                                    <div className="space-y-2 sm:col-span-1 lg:col-span-3">
                                        <Label htmlFor="price">Prix *</Label>
                                        <Input
                                            id="price"
                                            name="price"
                                            type="number"
                                            min={0}
                                            step={0.01}
                                            placeholder="15000"
                                            onChange={() =>
                                                clearErrors('price')
                                            }
                                        />
                                        <InputError message={errors.price} />
                                    </div>

                                    <div className="space-y-2 sm:col-span-1 lg:col-span-3">
                                        <Label htmlFor="stock">Stock *</Label>
                                        <Input
                                            id="stock"
                                            name={
                                                hasVariants
                                                    ? undefined
                                                    : 'stock'
                                            }
                                            type="number"
                                            min={0}
                                            placeholder="20"
                                            disabled={hasVariants}
                                            value={
                                                hasVariants
                                                    ? variantsStockTotal
                                                    : undefined
                                            }
                                            onChange={() =>
                                                clearErrors('stock')
                                            }
                                        />
                                        {hasVariants && (
                                            <input
                                                type="hidden"
                                                name="stock"
                                                value={variantsStockTotal}
                                            />
                                        )}
                                        {hasVariants ? (
                                            <p className="text-xs text-muted-foreground">
                                                Calculé automatiquement à partir
                                                du stock des variantes.
                                            </p>
                                        ) : (
                                            <InputError
                                                message={errors.stock}
                                            />
                                        )}
                                    </div>

                                    <div className="sm:col-span-1 lg:col-span-2">
                                        <div
                                            className="h-6"
                                            aria-hidden="true"
                                        />
                                        <div className="flex h-10 items-center gap-3">
                                            <Checkbox
                                                id="is_active"
                                                checked={isActive}
                                                onCheckedChange={(v) =>
                                                    setIsActive(!!v)
                                                }
                                                className="h-8 w-8 shrink-0"
                                            />
                                            <Label
                                                htmlFor="is_active"
                                                className="cursor-pointer text-sm leading-none font-normal"
                                            >
                                                Actif
                                            </Label>
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label>Images</Label>
                                    <ImageUpload name="images" />
                                    <InputError message={errors.images} />
                                </div>
                            </div>

                            <Separator />

                            {/* ─── Variantes ─── */}
                            <div className="space-y-4">
                                <h2 className="text-lg font-semibold">
                                    Variantes
                                </h2>

                                {variants.length === 0 && (
                                    <p className="text-sm text-muted-foreground">
                                        Aucune variante. Utile pour les tailles,
                                        couleurs, etc.
                                    </p>
                                )}

                                <div className="space-y-3">
                                    {variants.map((variant, index) => (
                                        <div
                                            key={index}
                                            className="space-y-3 rounded-lg border border-border/60 bg-muted/30 p-3"
                                        >
                                            <div className="flex flex-wrap items-end gap-2">
                                                <div className="min-w-0 flex-1 space-y-1">
                                                    <Label className="text-xs">
                                                        Nom *
                                                    </Label>
                                                    <Input
                                                        name={`variants[${index}][name]`}
                                                        value={variant.name}
                                                        onChange={(e) =>
                                                            updateVariant(
                                                                index,
                                                                'name',
                                                                e.target.value,
                                                            )
                                                        }
                                                        placeholder="Ex : Taille M"
                                                    />
                                                </div>
                                                <div className="w-32 space-y-1">
                                                    <Label className="text-xs">
                                                        Prix (optionnel)
                                                    </Label>
                                                    <Input
                                                        name={`variants[${index}][price]`}
                                                        type="number"
                                                        min={0}
                                                        step={0.01}
                                                        value={variant.price}
                                                        onChange={(e) =>
                                                            updateVariant(
                                                                index,
                                                                'price',
                                                                e.target.value,
                                                            )
                                                        }
                                                    />
                                                </div>
                                                <div className="w-24 space-y-1">
                                                    <Label className="text-xs">
                                                        Stock
                                                    </Label>
                                                    <Input
                                                        name={`variants[${index}][stock]`}
                                                        type="number"
                                                        min={0}
                                                        value={variant.stock}
                                                        onChange={(e) =>
                                                            updateVariant(
                                                                index,
                                                                'stock',
                                                                e.target.value,
                                                            )
                                                        }
                                                    />
                                                </div>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() =>
                                                        removeVariant(index)
                                                    }
                                                >
                                                    <Trash2Icon className="size-4 text-destructive" />
                                                </Button>
                                            </div>

                                            <div className="space-y-1">
                                                <Label className="text-xs">
                                                    Photos de cette variante
                                                </Label>
                                                <ImageUpload
                                                    name={`variants[${index}][images]`}
                                                    keepFieldName={`variants[${index}][keep_image_ids]`}
                                                />
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={addVariant}
                                >
                                    <PlusIcon className="mr-1 size-4" />
                                    Ajouter une variante
                                </Button>
                            </div>

                            <div className="flex justify-end">
                                <Button type="submit" disabled={processing}>
                                    {processing && <Spinner className="mr-2" />}
                                    Créer le produit
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

ProductCreate.layout = (page: React.ReactNode) => (
    <AppLayout
        breadcrumbs={[
            { title: 'Dashboard', href: dashboard() },
            { title: 'Produits', href: ProductController.index.url() },
            { title: 'Nouveau produit', href: ProductController.create.url() },
        ]}
    >
        {page}
    </AppLayout>
);
