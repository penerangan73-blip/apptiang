"use client";

import { Filter, Search } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Select } from "@/components/ui/select";
import type { PoleCondition } from "@/lib/types";

type SearchFilterProps = {
  streetQuery: string;
  conditionFilter: PoleCondition | "semua";
  onStreetQueryChange: (value: string) => void;
  onConditionFilterChange: (value: PoleCondition | "semua") => void;
};

export function SearchFilter({
  streetQuery,
  conditionFilter,
  onStreetQueryChange,
  onConditionFilterChange,
}: SearchFilterProps) {
  return (
    <Card className="overflow-hidden border-border bg-card shadow-sm">
      <CardContent className="p-4">
        <div className="grid gap-3 lg:grid-cols-[minmax(0,1fr)_180px_auto]">
          <div className="relative">
            <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
            <Input
              value={streetQuery}
              onChange={(event) => onStreetQueryChange(event.target.value)}
              placeholder="Cari ID pelanggan atau nama jalan..."
              className="h-11 bg-background pl-10"
            />
          </div>
          <Select
            value={conditionFilter}
            onChange={(event) => onConditionFilterChange(event.target.value as PoleCondition | "semua")}
            aria-label="Filter kondisi tiang"
            className="h-11 bg-background"
          >
            <option value="semua">Semua kondisi</option>
            <option value="baik">Baik</option>
            <option value="rusak">Rusak</option>
            <option value="perbaikan">Perbaikan</option>
          </Select>
          <Button type="button" variant="outline" className="h-11 justify-start lg:justify-center">
            <Filter className="size-4" />
            Filter
          </Button>
        </div>
      </CardContent>
    </Card>
  );
}
