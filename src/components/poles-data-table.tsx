"use client";

import { Bolt, Camera, Edit3, Eye, MapPin, Trash2 } from "lucide-react";
import { useEffect, useMemo, useState } from "react";
import { StatusBadge } from "@/components/status-badge";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Select } from "@/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import type { Pole } from "@/lib/types";

type PolesDataTableProps = {
  poles: Pole[];
  onDetail: (pole: Pole) => void;
  onDelete: (pole: Pole) => void;
  onEdit: (pole: Pole) => void;
  onReport: (pole: Pole) => void;
  showManagementActions?: boolean;
  showReportAction?: boolean;
  showTimestamps?: boolean;
};

export function PolesDataTable({
  poles,
  onDetail,
  onDelete,
  onEdit,
  onReport,
  showManagementActions = true,
  showReportAction = true,
  showTimestamps = false,
}: PolesDataTableProps) {
  const [pageSize, setPageSize] = useState(10);
  const [page, setPage] = useState(1);
  const totalPages = Math.max(1, Math.ceil(poles.length / pageSize));
  const shouldPaginate = poles.length > 10;
  const visiblePoles = useMemo(() => {
    if (!shouldPaginate) {
      return poles;
    }

    const start = (page - 1) * pageSize;
    return poles.slice(start, start + pageSize);
  }, [page, pageSize, poles, shouldPaginate]);

  useEffect(() => {
    setPage(1);
  }, [pageSize, poles]);

  useEffect(() => {
    setPage((current) => Math.min(current, totalPages));
  }, [totalPages]);

  if (!poles.length) {
    return (
      <Card className="border-dashed border-slate-300 bg-card p-8 text-center">
        <p className="font-semibold text-muted-foreground">Tidak ada tiang untuk filter ini.</p>
        <p className="mt-1 text-sm text-muted-foreground">Coba ubah nama jalan atau kondisi.</p>
      </Card>
    );
  }

  return (
    <>
      <Card className="hidden overflow-hidden border-border bg-card shadow-sm lg:block">
        <Table>
          <TableHeader>
            <TableRow className="bg-background/80">
              <TableHead>ID Pelanggan</TableHead>
              <TableHead>Lokasi</TableHead>
              <TableHead>Daya</TableHead>
              <TableHead>Kondisi</TableHead>
              <TableHead>Jenis</TableHead>
              {showTimestamps && (
                <>
                  <TableHead>Diinput</TableHead>
                  <TableHead>Diinput Oleh</TableHead>
                  <TableHead>Update Terakhir</TableHead>
                  <TableHead>Update Oleh</TableHead>
                </>
              )}
              <TableHead className="text-right">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {visiblePoles.map((pole) => (
              <TableRow key={pole.id}>
                <TableCell>
                  <p className="font-bold text-foreground">{pole.customer_id}</p>
                  <p className="text-xs text-muted-foreground">
                    {pole.latitude.toFixed(5)}, {pole.longitude.toFixed(5)}
                  </p>
                </TableCell>
                <TableCell className="font-medium text-muted-foreground">{pole.street_name}</TableCell>
                <TableCell>{pole.power_usage} Watt</TableCell>
                <TableCell>
                  <StatusBadge condition={pole.condition} />
                </TableCell>
                <TableCell className="capitalize text-muted-foreground">{formatOptionLabel(pole.pole_type)}</TableCell>
                {showTimestamps && (
                  <>
                    <TableCell className="text-sm font-semibold text-muted-foreground">{formatDateTime(pole.created_at)}</TableCell>
                    <TableCell className="text-sm font-semibold text-muted-foreground">{formatUserName(pole.created_by)}</TableCell>
                    <TableCell className="text-sm font-semibold text-muted-foreground">{formatDateTime(pole.updated_at)}</TableCell>
                    <TableCell className="text-sm font-semibold text-muted-foreground">{formatUserName(pole.updated_by)}</TableCell>
                  </>
                )}
                <TableCell>
                  <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" size="sm" onClick={() => onDetail(pole)}>
                      <Eye className="size-4" />
                      Detail
                    </Button>
                    {showManagementActions && (
                      <Button type="button" variant="outline" size="sm" onClick={() => onEdit(pole)}>
                        <Edit3 className="size-4" />
                        Edit
                      </Button>
                    )}
                    {showReportAction && (
                      <Button type="button" size="sm" onClick={() => onReport(pole)}>
                        <Camera className="size-4" />
                        Lapor
                      </Button>
                    )}
                    {showManagementActions && (
                      <Button type="button" variant="destructive" size="sm" onClick={() => onDelete(pole)}>
                        <Trash2 className="size-4" />
                        Hapus
                      </Button>
                    )}
                  </div>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
        {shouldPaginate && <PaginationControls page={page} pageSize={pageSize} totalItems={poles.length} totalPages={totalPages} onPageChange={setPage} onPageSizeChange={setPageSize} />}
      </Card>

      <div className="grid gap-3 lg:hidden">
        {visiblePoles.map((pole) => (
          <Card key={pole.id} className="border-border bg-card p-4 shadow-sm">
            <div className="flex items-start justify-between gap-3">
              <div className="min-w-0">
                <p className="font-bold text-foreground">{pole.customer_id}</p>
                <p className="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground">
                  <MapPin className="size-4 text-primary" />
                  {pole.street_name}
                </p>
              </div>
              <StatusBadge condition={pole.condition} />
            </div>
            {showTimestamps && (
              <div className="mt-3 grid gap-2 rounded-md border bg-background/50 p-3 text-xs font-semibold text-muted-foreground sm:grid-cols-2">
                <p>Diinput: {formatDateTime(pole.created_at)}</p>
                <p>Diinput oleh: {formatUserName(pole.created_by)}</p>
                <p>Update terakhir: {formatDateTime(pole.updated_at)}</p>
                <p>Update oleh: {formatUserName(pole.updated_by)}</p>
              </div>
            )}
            <div className="mt-4 flex items-center justify-between gap-3">
              <p className="flex items-center gap-1.5 text-sm font-semibold text-muted-foreground">
                <Bolt className="size-4 text-amber-500" />
                {pole.power_usage} Watt
              </p>
              <div className="flex gap-2">
                <Button type="button" variant="outline" size="sm" onClick={() => onDetail(pole)} aria-label="Detail tiang">
                  <Eye className="size-4" />
                </Button>
                {showManagementActions && (
                  <Button type="button" variant="outline" size="sm" onClick={() => onEdit(pole)} aria-label="Edit tiang">
                    <Edit3 className="size-4" />
                  </Button>
                )}
                {showReportAction && (
                  <Button type="button" size="sm" onClick={() => onReport(pole)} aria-label="Lapor kerusakan">
                    <Camera className="size-4" />
                  </Button>
                )}
                {showManagementActions && (
                  <Button type="button" variant="destructive" size="sm" onClick={() => onDelete(pole)} aria-label="Hapus tiang">
                    <Trash2 className="size-4" />
                  </Button>
                )}
              </div>
            </div>
          </Card>
        ))}
        {shouldPaginate && <PaginationControls page={page} pageSize={pageSize} totalItems={poles.length} totalPages={totalPages} onPageChange={setPage} onPageSizeChange={setPageSize} />}
      </div>
    </>
  );
}

function PaginationControls({
  page,
  pageSize,
  totalItems,
  totalPages,
  onPageChange,
  onPageSizeChange,
}: {
  page: number;
  pageSize: number;
  totalItems: number;
  totalPages: number;
  onPageChange: (page: number) => void;
  onPageSizeChange: (pageSize: number) => void;
}) {
  const startItem = (page - 1) * pageSize + 1;
  const endItem = Math.min(page * pageSize, totalItems);

  return (
    <div className="flex flex-col gap-3 border-t bg-background/40 p-3 sm:flex-row sm:items-center sm:justify-between">
      <p className="text-sm font-semibold text-muted-foreground">
        Menampilkan {startItem}-{endItem} dari {totalItems} data
      </p>
      <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
        <Select
          value={String(pageSize)}
          onChange={(event) => onPageSizeChange(Number(event.target.value))}
          aria-label="Jumlah data per halaman"
          className="h-10 bg-background sm:w-28"
        >
          <option value="10">10 data</option>
          <option value="25">25 data</option>
          <option value="50">50 data</option>
          <option value="100">100 data</option>
        </Select>
        <div className="flex items-center gap-2">
          <Button type="button" variant="outline" size="sm" disabled={page === 1} onClick={() => onPageChange(page - 1)}>
            Prev
          </Button>
          <p className="min-w-20 text-center text-sm font-bold text-muted-foreground">
            {page}/{totalPages}
          </p>
          <Button type="button" variant="outline" size="sm" disabled={page === totalPages} onClick={() => onPageChange(page + 1)}>
            Next
          </Button>
        </div>
      </div>
    </div>
  );
}

function formatOptionLabel(value: string) {
  return value.replaceAll("_", " ");
}

function formatDateTime(value?: string) {
  if (!value) {
    return "-";
  }

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return "-";
  }

  return formatDateParts(date);
}

function formatDateParts(date: Date) {
  const hours = String(date.getHours()).padStart(2, "0");
  const minutes = String(date.getMinutes()).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();

  return `${hours}:${minutes} ${day}-${month}-${year}`;
}

function formatUserName(user?: Pole["created_by"]) {
  return user?.name || "-";
}
