"use client";

import dynamic from "next/dynamic";
import { useState } from "react";
import { Camera, Save } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select } from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import type { FoundationType, Ownership, Pole, PoleCondition, PoleFormInput, PoleType } from "@/lib/types";

type PoleFormProps = {
  initialPole?: Pole | null;
  isSubmitting?: boolean;
  onSubmit: (input: PoleFormInput, id?: number) => void;
};

const defaultCoordinates = {
  latitude: -7.965,
  longitude: 110.603,
};

const CoordinatePicker = dynamic(() => import("@/components/coordinate-picker").then((mod) => mod.CoordinatePicker), {
  ssr: false,
  loading: () => (
    <div className="flex h-64 items-center justify-center rounded-lg border bg-background/55 text-sm font-semibold text-muted-foreground">
      Memuat peta koordinat...
    </div>
  ),
});

export function PoleForm({ initialPole, isSubmitting = false, onSubmit }: PoleFormProps) {
  const [coordinates, setCoordinates] = useState({
    latitude: initialPole?.latitude ?? defaultCoordinates.latitude,
    longitude: initialPole?.longitude ?? defaultCoordinates.longitude,
  });
  const [streetName, setStreetName] = useState(initialPole?.street_name ?? "");
  const [photoPreview, setPhotoPreview] = useState(initialPole?.photo_url ?? "");

  return (
    <form
      className="grid gap-4 sm:grid-cols-2"
      onSubmit={(event) => {
        event.preventDefault();
        const formData = new FormData(event.currentTarget);
        const photo = formData.get("photo");
        const input: PoleFormInput = {
          customer_id: String(formData.get("customer_id") ?? ""),
          street_name: streetName,
          latitude: coordinates.latitude,
          longitude: coordinates.longitude,
          power_usage: Number(formData.get("power_usage") ?? 0),
          condition: String(formData.get("condition") ?? "baik") as PoleCondition,
          pole_type: String(formData.get("pole_type") ?? "lurus") as PoleType,
          height: parseNullableNumber(formData.get("height")),
          span_length: parseNullableNumber(formData.get("span_length")),
          foundation_type: String(formData.get("foundation_type") ?? "beton") as FoundationType,
          ownership: String(formData.get("ownership") ?? "dpu_kp") as Ownership,
          pln_transformer: String(formData.get("pln_transformer") ?? ""),
          notes: String(formData.get("notes") ?? ""),
          photo: photo instanceof File && photo.size > 0 ? photo : null,
        };
        onSubmit(input, initialPole?.id);
      }}
    >
      <div className="space-y-2">
        <Label htmlFor="customer_id">ID Pelanggan</Label>
        <Input id="customer_id" name="customer_id" required defaultValue={initialPole?.customer_id} placeholder="PEL-001" />
      </div>
      <div className="sm:col-span-2">
        <CoordinatePicker
          value={coordinates}
          onSet={(nextCoordinates, address) => {
            setCoordinates(nextCoordinates);
            if (address) {
              setStreetName(address);
            }
          }}
        />
      </div>
      <div className="space-y-2 sm:col-span-2">
        <Label htmlFor="street_name">Lokasi</Label>
        <Input
          id="street_name"
          name="street_name"
          required
          value={streetName}
          onChange={(event) => setStreetName(event.target.value)}
          placeholder="Contoh: Jalan Brigjen Katamso Wonosari - Titik 1"
          className="min-h-14"
        />
      </div>
      <div className="space-y-2">
        <Label htmlFor="latitude">Latitude</Label>
        <Input
          id="latitude"
          name="latitude"
          type="number"
          step="any"
          required
          value={coordinates.latitude}
          onChange={(event) => setCoordinates((current) => ({ ...current, latitude: Number(event.target.value) }))}
        />
      </div>
      <div className="space-y-2">
        <Label htmlFor="longitude">Longitude</Label>
        <Input
          id="longitude"
          name="longitude"
          type="number"
          step="any"
          required
          value={coordinates.longitude}
          onChange={(event) => setCoordinates((current) => ({ ...current, longitude: Number(event.target.value) }))}
        />
      </div>
      <div className="space-y-2">
        <Label htmlFor="power_usage">Daya watt</Label>
        <Input id="power_usage" name="power_usage" type="number" min="1" required defaultValue={initialPole?.power_usage ?? 90} />
      </div>
      <div className="space-y-2">
        <Label htmlFor="condition">Kondisi</Label>
        <Select id="condition" name="condition" defaultValue={initialPole?.condition ?? "baik"}>
          <option value="baik">Baik</option>
          <option value="rusak">Rusak</option>
          <option value="perbaikan">Perbaikan</option>
        </Select>
      </div>
      <div className="space-y-2">
        <Label htmlFor="pole_type">Jenis tiang</Label>
        <Select id="pole_type" name="pole_type" defaultValue={initialPole?.pole_type ?? "lurus"}>
          <option value="lurus">Lurus</option>
          <option value="lengkung">Lengkung</option>
          <option value="tanpa_tiang">Tanpa tiang</option>
          <option value="lainnya">Lainnya</option>
        </Select>
      </div>
      <div className="space-y-2">
        <Label htmlFor="height">Tinggi</Label>
        <Input id="height" name="height" type="number" min="0" step="0.01" defaultValue={initialPole?.height ?? ""} placeholder="Meter" />
      </div>
      <div className="space-y-2">
        <Label htmlFor="span_length">Bentangan</Label>
        <Input
          id="span_length"
          name="span_length"
          type="number"
          min="0"
          step="0.01"
          defaultValue={initialPole?.span_length ?? ""}
          placeholder="Meter"
        />
      </div>
      <div className="space-y-2">
        <Label htmlFor="foundation_type">Pondasi</Label>
        <Select id="foundation_type" name="foundation_type" defaultValue={initialPole?.foundation_type ?? "beton"}>
          <option value="beton">Beton</option>
          <option value="tanam">Tanam</option>
          <option value="base_plate">Base Plate</option>
          <option value="lainnya">Lainnya</option>
        </Select>
      </div>
      <div className="space-y-2">
        <Label htmlFor="ownership">Kepemilikan</Label>
        <Select id="ownership" name="ownership" defaultValue={initialPole?.ownership ?? "dpu_kp"}>
          <option value="dpu_kp">DPU KP</option>
          <option value="dishub">DISHUB</option>
          <option value="pln">PLN</option>
          <option value="lainnya">Lainnya</option>
        </Select>
      </div>
      <div className="space-y-2">
        <Label htmlFor="pln_transformer">Trafo PLN</Label>
        <Input id="pln_transformer" name="pln_transformer" defaultValue={initialPole?.pln_transformer ?? ""} placeholder="Contoh: TR-001" />
      </div>
      <div className="space-y-2 sm:col-span-2">
        <Label htmlFor="photo">Foto</Label>
        <label className="focus-ring flex min-h-36 cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed bg-background/55 p-4 text-center">
          {photoPreview ? (
            // eslint-disable-next-line @next/next/no-img-element
            <img src={photoPreview} alt="Preview tiang" className="max-h-48 rounded-md object-cover" />
          ) : (
            <>
              <Camera className="mb-2 size-8 text-primary" />
              <span className="text-sm font-semibold text-muted-foreground">Ambil atau unggah foto tiang</span>
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
      <div className="space-y-2 sm:col-span-2">
        <Label htmlFor="notes">Keterangan</Label>
        <Textarea id="notes" name="notes" defaultValue={initialPole?.notes ?? ""} placeholder="Catatan tambahan aset..." />
      </div>
      <div className="sm:col-span-2">
        <Button type="submit" className="h-12 w-full sm:w-auto" disabled={isSubmitting}>
          <Save className="size-4" />
          {isSubmitting ? "Menyimpan..." : initialPole ? "Simpan Perubahan" : "Tambah Tiang"}
        </Button>
      </div>
    </form>
  );
}

function parseNullableNumber(value: FormDataEntryValue | null) {
  const numberValue = Number(value);
  return value === null || value === "" || Number.isNaN(numberValue) ? null : numberValue;
}
