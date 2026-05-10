"use client";

import { Camera, KeyRound, Save } from "lucide-react";
import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import type { PasswordFormInput, ProfileFormInput, User } from "@/lib/types";

type ProfileFormProps = {
  user: User;
  isSubmitting?: boolean;
  onSubmit: (input: ProfileFormInput) => void;
};

type PasswordFormProps = {
  isSubmitting?: boolean;
  onSubmit: (input: PasswordFormInput) => void;
};

export function ProfileForm({ user, isSubmitting = false, onSubmit }: ProfileFormProps) {
  const [photoPreview, setPhotoPreview] = useState(user.photo_url ?? "");

  return (
    <form
      className="grid gap-4 sm:grid-cols-2"
      onSubmit={(event) => {
        event.preventDefault();
        const formData = new FormData(event.currentTarget);
        const photo = formData.get("photo");

        onSubmit({
          name: String(formData.get("name") ?? ""),
          email: String(formData.get("email") ?? ""),
          phone: String(formData.get("phone") ?? ""),
          address: String(formData.get("address") ?? ""),
          photo: photo instanceof File && photo.size > 0 ? photo : null,
        });
      }}
    >
      <div className="space-y-2">
        <Label htmlFor="profile_name">Nama</Label>
        <Input id="profile_name" name="name" required defaultValue={user.name} />
      </div>
      <div className="space-y-2">
        <Label htmlFor="profile_email">Email</Label>
        <Input id="profile_email" name="email" type="email" required defaultValue={user.email} />
      </div>
      <div className="space-y-2 sm:col-span-2">
        <Label htmlFor="profile_phone">Nomor Telepon</Label>
        <Input id="profile_phone" name="phone" defaultValue={user.phone ?? ""} placeholder="Contoh: 0812-0000-0000" />
      </div>
      <div className="space-y-2 sm:col-span-2">
        <Label htmlFor="profile_address">Alamat</Label>
        <Textarea id="profile_address" name="address" defaultValue={user.address ?? ""} placeholder="Alamat user" />
      </div>
      <div className="space-y-2 sm:col-span-2">
        <Label htmlFor="profile_photo">Foto Profil</Label>
        <label className="focus-ring flex min-h-36 cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed bg-background/55 p-4 text-center">
          {photoPreview ? (
            // eslint-disable-next-line @next/next/no-img-element
            <img src={photoPreview} alt={`Foto ${user.name}`} className="size-28 rounded-full object-cover" />
          ) : (
            <>
              <Camera className="mb-2 size-8 text-primary" />
              <span className="text-sm font-semibold text-muted-foreground">Unggah foto profil</span>
            </>
          )}
          <Input
            id="profile_photo"
            name="photo"
            type="file"
            accept="image/*"
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
      <div className="sm:col-span-2">
        <Button type="submit" disabled={isSubmitting}>
          <Save className="size-4" />
          {isSubmitting ? "Menyimpan..." : "Simpan Profil"}
        </Button>
      </div>
    </form>
  );
}

export function PasswordForm({ isSubmitting = false, onSubmit }: PasswordFormProps) {
  return (
    <form
      className="space-y-4"
      onSubmit={(event) => {
        event.preventDefault();
        const formData = new FormData(event.currentTarget);

        onSubmit({
          current_password: String(formData.get("current_password") ?? ""),
          password: String(formData.get("password") ?? ""),
          password_confirmation: String(formData.get("password_confirmation") ?? ""),
        });
      }}
    >
      <div className="space-y-2">
        <Label htmlFor="current_password">Password Saat Ini</Label>
        <Input id="current_password" name="current_password" type="password" required />
      </div>
      <div className="space-y-2">
        <Label htmlFor="new_password">Password Baru</Label>
        <Input id="new_password" name="password" type="password" required minLength={6} />
      </div>
      <div className="space-y-2">
        <Label htmlFor="password_confirmation">Konfirmasi Password Baru</Label>
        <Input id="password_confirmation" name="password_confirmation" type="password" required minLength={6} />
      </div>
      <Button type="submit" disabled={isSubmitting}>
        <KeyRound className="size-4" />
        {isSubmitting ? "Menyimpan..." : "Ubah Password"}
      </Button>
    </form>
  );
}
