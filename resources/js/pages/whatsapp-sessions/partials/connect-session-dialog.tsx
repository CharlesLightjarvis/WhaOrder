import { useState } from 'react';
import { Form } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import WhatsAppSessionController from '@/actions/App/Http/Controllers/WhatsAppSessionController';

export default function ConnectSessionDialog() {
    const [open, setOpen] = useState(false);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button>
                    <PlusIcon className="size-4" />
                    Connecter un numéro
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Connecter un numéro WhatsApp</DialogTitle>
                    <DialogDescription>
                        Donnez un nom à cette session pour la reconnaître
                        (ex : "Boutique principale"). Un QR code à scanner
                        apparaîtra ensuite.
                    </DialogDescription>
                </DialogHeader>

                <Form
                    {...WhatsAppSessionController.store.form()}
                    onSuccess={() => setOpen(false)}
                >
                    {({ processing, errors, clearErrors }) => (
                        <>
                            <div className="space-y-2">
                                <Label htmlFor="label">Nom de la session</Label>
                                <Input
                                    id="label"
                                    name="label"
                                    autoFocus
                                    placeholder="Boutique principale"
                                    onChange={() => clearErrors('label')}
                                />
                                <InputError message={errors.label} />
                            </div>

                            <DialogFooter className="mt-4">
                                <Button type="submit" disabled={processing}>
                                    {processing && (
                                        <Spinner className="mr-2" />
                                    )}
                                    Connecter
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
