import * as React from 'react';
import { type Column } from '@tanstack/react-table';
import { Check, PlusCircle } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandSeparator,
} from '@/components/ui/command';

interface DataTableFacetedFilterProps<TData, TValue> {
    column?: Column<TData, TValue>;
    title?: string;
    options: readonly {
        readonly label: string;
        readonly value: string;
        readonly icon?: React.ComponentType<{ className?: string }>;
    }[];
    onSelectionChange?: (count: number) => void;
}

export function DataTableFacetedFilter<TData, TValue>({
    column,
    title,
    options,
    onSelectionChange,
}: DataTableFacetedFilterProps<TData, TValue>) {
    const facets = column?.getFacetedUniqueValues();

    const [selectedValues, setSelectedValues] = React.useState<Set<string>>(
        () => new Set(column?.getFilterValue() as string[] | undefined),
    );

    function applyFilter(next: Set<string>) {
        setSelectedValues(next);
        const arr = Array.from(next);
        column?.setFilterValue(arr.length ? arr : undefined);
        onSelectionChange?.(next.size);
    }

    function toggle(value: string) {
        const next = new Set(selectedValues);
        if (next.has(value)) {
            next.delete(value);
        } else {
            next.add(value);
        }
        applyFilter(next);
    }

    function clearAll() {
        applyFilter(new Set());
    }

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button variant="outline" size="sm" className="h-8 border-dashed">
                    <PlusCircle />
                    {title}
                    {selectedValues.size > 0 && (
                        <>
                            <Separator orientation="vertical" className="mx-2 h-4" />
                            <Badge
                                variant="secondary"
                                className="rounded-sm px-1 font-normal lg:hidden"
                            >
                                {selectedValues.size}
                            </Badge>
                            <div className="hidden gap-1 lg:flex">
                                {selectedValues.size > 2 ? (
                                    <Badge
                                        variant="secondary"
                                        className="rounded-sm px-1 font-normal"
                                    >
                                        {selectedValues.size} sélectionné(s)
                                    </Badge>
                                ) : (
                                    options
                                        .filter((o) => selectedValues.has(o.value))
                                        .map((o) => (
                                            <Badge
                                                variant="secondary"
                                                key={o.value}
                                                className="rounded-sm px-1 font-normal"
                                            >
                                                {o.label}
                                            </Badge>
                                        ))
                                )}
                            </div>
                        </>
                    )}
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-[200px] p-0" align="start">
                <Command shouldFilter={false}>
                    <CommandInput placeholder={title} />
                    <CommandList>
                        <CommandEmpty>Aucun résultat.</CommandEmpty>
                        <CommandGroup>
                            {options.map((option) => {
                                const isSelected = selectedValues.has(option.value);
                                return (
                                    <CommandItem
                                        key={option.value}
                                        value={option.value}
                                        onSelect={() => toggle(option.value)}
                                    >
                                        <div
                                            className={cn(
                                                'flex size-4 shrink-0 items-center justify-center rounded-[4px] border',
                                                isSelected
                                                    ? 'border-primary bg-primary text-primary-foreground'
                                                    : 'border-input [&_svg]:invisible',
                                            )}
                                        >
                                            <Check className="size-3.5 text-primary-foreground" />
                                        </div>
                                        {option.icon && (
                                            <option.icon className="size-4 text-muted-foreground" />
                                        )}
                                        <span>{option.label}</span>
                                        {facets?.get(option.value) && (
                                            <span className="ml-auto flex size-4 items-center justify-center font-mono text-xs text-muted-foreground">
                                                {facets.get(option.value)}
                                            </span>
                                        )}
                                    </CommandItem>
                                );
                            })}
                        </CommandGroup>
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    );
}
