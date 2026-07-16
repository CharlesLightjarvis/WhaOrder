import { ImageIcon, XIcon } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

type ExistingImage = {
    id: number;
    url: string;
};

type Props = {
    name?: string;
    keepFieldName?: string;
    existingImages?: ExistingImage[];
};

export function ImageUpload({
    name = 'images',
    keepFieldName = 'keep_image_ids',
    existingImages = [],
}: Props) {
    const [files, setFiles] = useState<File[]>([]);
    const [removedIds, setRemovedIds] = useState<number[]>([]);

    const keptImages = existingImages.filter(
        (image) => !removedIds.includes(image.id),
    );

    function handleSelect(event: React.ChangeEvent<HTMLInputElement>) {
        const selected = Array.from(event.target.files ?? []);
        setFiles((prev) => [...prev, ...selected]);
        event.target.value = '';
    }

    return (
        <div className="space-y-3">
            <div className="flex flex-wrap gap-3">
                {keptImages.map((image) => (
                    <Thumbnail
                        key={`existing-${image.id}`}
                        src={image.url}
                        onRemove={() =>
                            setRemovedIds((prev) => [...prev, image.id])
                        }
                    />
                ))}

                {files.map((file, index) => (
                    <FilePreview
                        key={index}
                        file={file}
                        name={`${name}[]`}
                        onRemove={() =>
                            setFiles((prev) =>
                                prev.filter((_, i) => i !== index),
                            )
                        }
                    />
                ))}

                <label className="flex size-20 cursor-pointer flex-col items-center justify-center gap-1 rounded-md border border-dashed border-border text-muted-foreground hover:bg-muted/50">
                    <ImageIcon className="size-5" aria-hidden="true" />
                    <span className="text-[10px]">Ajouter</span>
                    <input
                        type="file"
                        accept="image/png,image/jpeg,image/webp"
                        multiple
                        className="hidden"
                        onChange={handleSelect}
                    />
                </label>
            </div>

            {keptImages.map((image) => (
                <input
                    key={`keep-${image.id}`}
                    type="hidden"
                    name={`${keepFieldName}[]`}
                    value={image.id}
                />
            ))}
        </div>
    );
}

function Thumbnail({ src, onRemove }: { src: string; onRemove: () => void }) {
    return (
        <div className="group relative size-20 overflow-hidden rounded-md border border-border">
            <img src={src} alt="" className="size-full object-cover" />
            <button
                type="button"
                onClick={onRemove}
                className="absolute top-1 right-1 flex size-5 items-center justify-center rounded-full bg-background/90 text-foreground opacity-0 shadow transition-opacity group-hover:opacity-100"
            >
                <XIcon className="size-3" />
            </button>
        </div>
    );
}

function FilePreview({
    file,
    name,
    onRemove,
}: {
    file: File;
    name: string;
    onRemove: () => void;
}) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);

    useEffect(() => {
        const url = URL.createObjectURL(file);
        setPreviewUrl(url);

        return () => URL.revokeObjectURL(url);
    }, [file]);

    useEffect(() => {
        if (!inputRef.current) return;

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        inputRef.current.files = dataTransfer.files;
    }, [file]);

    return (
        <div className="group relative size-20 overflow-hidden rounded-md border border-border">
            {previewUrl && (
                <img src={previewUrl} alt="" className="size-full object-cover" />
            )}
            <input ref={inputRef} type="file" name={name} className="hidden" />
            <button
                type="button"
                onClick={onRemove}
                className="absolute top-1 right-1 flex size-5 items-center justify-center rounded-full bg-background/90 text-foreground opacity-0 shadow transition-opacity group-hover:opacity-100"
            >
                <XIcon className="size-3" />
            </button>
        </div>
    );
}
