"use client";

/* eslint-disable @next/next/no-img-element */

import { useState } from "react";
import { Camera, Send } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import type { Pole, ReportInput } from "@/lib/types";

type DamageReportFormProps = {
  isSubmitting?: boolean;
  selectedPole?: Pole | null;
  onSubmit: (input: ReportInput) => void;
};

export function DamageReportForm({ isSubmitting = false, selectedPole, onSubmit }: DamageReportFormProps) {
  const [photoPreview, setPhotoPreview] = useState("");

  return (
    <form
      className="space-y-4"
      onSubmit={(event) => {
        event.preventDefault();
        if (!selectedPole) {
          return;
        }

        const formData = new FormData(event.currentTarget);
        const photo = formData.get("photo");
        onSubmit({
          pole_id: selectedPole.id,
          notes: String(formData.get("notes") ?? ""),
          photo: photo instanceof File && photo.size > 0 ? photo : null,
        });
        setPhotoPreview("");
        event.currentTarget.reset();
      }}
    >
      <div className="rounded-lg border bg-background/55 p-4">
        <p className="text-xs font-bold uppercase tracking-wide text-muted-foreground">Tiang dipilih</p>
        <p className="mt-1 font-bold text-foreground">{selectedPole?.customer_id ?? "Pilih tiang dari daftar"}</p>
        <p className="text-sm text-muted-foreground">{selectedPole?.street_name ?? "Belum ada tiang aktif"}</p>
      </div>

      <div className="space-y-2">
        <Label htmlFor="photo">Foto kerusakan</Label>
        <label className="focus-ring flex min-h-36 cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed bg-background/55 p-4 text-center">
          {photoPreview ? (
            <img src={photoPreview} alt="Preview kerusakan" className="max-h-48 rounded-md object-cover" />
          ) : (
            <>
              <Camera className="mb-2 size-8 text-primary" />
              <span className="text-sm font-semibold text-muted-foreground">Ambil atau unggah foto</span>
            </>
          )}
          <Input
            id="photo"
            name="photo"
            type="file"
            accept="image/*"
            capture="environment"
            className="sr-only"
            onChange={(event) => {
              const file = event.target.files?.[0];
              if (!file) {
                return;
              }
              setPhotoPreview(URL.createObjectURL(file));
            }}
          />
        </label>
      </div>

      <div className="space-y-2">
        <Label htmlFor="notes">Catatan kerusakan</Label>
        <Textarea id="notes" name="notes" required placeholder="Contoh: lampu mati, kabel terbuka, panel rusak..." />
      </div>

      <Button type="submit" className="h-12 w-full" disabled={!selectedPole || isSubmitting}>
        <Send className="size-4" />
        {isSubmitting ? "Mengirim laporan..." : "Kirim Laporan"}
      </Button>
    </form>
  );
}
