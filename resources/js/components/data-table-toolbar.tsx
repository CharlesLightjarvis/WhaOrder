import { useState, useCallback } from 'react';
import { type Table } from '@tanstack/react-table';
import { X } from 'lucide-react';
import type React from 'react';

import { DataTableFacetedFilter } from './data-table-faceted-filter';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { DataTableViewOptions } from './data-table-view-options';

export interface FacetedFilterConfig {
    columnId: string;
    title: string;
    options: readonly {
        readonly label: string;
        readonly value: string;
        readonly icon?: React.ComponentType<{ className?: string }>;
    }[];
}

export interface SearchFilterConfig {
    columnIds: string[];
    placeholder: string;
}

export interface ActionButtonConfig {
    label: string;
    onClick: () => void;
}

interface DataTableToolbarProps<TData> {
    table: Table<TData>;
    searchFilter?: SearchFilterConfig;
    facetedFilters?: FacetedFilterConfig[];
    actionButton?: ActionButtonConfig;
}

export function DataTableToolbar<TData>({
    table,
    searchFilter,
    facetedFilters = [],
    actionButton,
}: DataTableToolbarProps<TData>) {
    const [searchValue, setSearchValue] = useState('');
    const [filterCounts, setFilterCounts] = useState<Record<string, number>>({});
    const [filterResetKey, setFilterResetKey] = useState(0);

    const handleSelectionChange = useCallback((columnId: string, count: number) => {
        setFilterCounts((prev) => ({ ...prev, [columnId]: count }));
    }, []);

    const hasActiveFilters = Object.values(filterCounts).some((c) => c > 0);
    const hasSearch = searchValue !== '';

    function handleSearch(value: string) {
        setSearchValue(value);
        table.setGlobalFilter(value);
    }

    function handleResetSearch() {
        setSearchValue('');
        table.setGlobalFilter('');
    }

    function handleResetFilters() {
        setFilterCounts({});
        setFilterResetKey((k) => k + 1);
        table.resetColumnFilters();
    }

    return (
        <div className="flex items-center justify-between">
            <div className="flex flex-1 items-center gap-2">
                {searchFilter && (
                    <Input
                        placeholder={searchFilter.placeholder}
                        value={searchValue}
                        onChange={(e) => handleSearch(e.target.value)}
                        className="h-8 w-[150px] lg:w-[250px]"
                    />
                )}
                {facetedFilters.map((filter) => {
                    const column = table.getColumn(filter.columnId);
                    if (!column) return null;
                    return (
                        <DataTableFacetedFilter
                            key={`${filter.columnId}-${filterResetKey}`}
                            column={column}
                            title={filter.title}
                            options={filter.options}
                            onSelectionChange={(count) =>
                                handleSelectionChange(filter.columnId, count)
                            }
                        />
                    );
                })}
                {hasSearch && (
                    <Button variant="ghost" size="sm" onClick={handleResetSearch}>
                        Réinitialiser
                        <X />
                    </Button>
                )}
                {hasActiveFilters && (
                    <Button variant="ghost" size="sm" onClick={handleResetFilters}>
                        Effacer les filtres
                        <X />
                    </Button>
                )}
            </div>
            <div className="flex items-center gap-2">
                <DataTableViewOptions table={table} />
                {actionButton && (
                    <Button size="sm" onClick={actionButton.onClick}>
                        {actionButton.label}
                    </Button>
                )}
            </div>
        </div>
    );
}
