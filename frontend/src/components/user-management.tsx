"use client";

import { Edit3, PlusCircle, Trash2, UserCog } from "lucide-react";
import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select } from "@/components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import type { User, UserFormInput, UserRole } from "@/lib/types";

type UserManagementProps = {
  currentUser: User;
  users: User[];
  isSubmitting?: boolean;
  onCreate: (input: UserFormInput) => void;
  onUpdate: (id: number, input: UserFormInput) => void;
  onDelete: (user: User) => void;
};

export function UserManagement({ currentUser, users, isSubmitting = false, onCreate, onUpdate, onDelete }: UserManagementProps) {
  const [editingUser, setEditingUser] = useState<User | null>(null);
  const [isDialogOpen, setIsDialogOpen] = useState(false);

  function openCreateDialog() {
    setEditingUser(null);
    setIsDialogOpen(true);
  }

  function openEditDialog(user: User) {
    setEditingUser(user);
    setIsDialogOpen(true);
  }

  return (
    <section className="space-y-5">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 className="text-2xl font-bold text-foreground">Manajemen User</h2>
          <p className="text-sm text-muted-foreground">Kelola akun superadmin, surveyor, dan user read-only.</p>
        </div>
        <Button type="button" onClick={openCreateDialog}>
          <PlusCircle className="size-4" />
          Tambah User
        </Button>
      </div>

      <Card className="hidden overflow-hidden border-border bg-card shadow-sm lg:block">
        <Table>
          <TableHeader>
            <TableRow className="bg-background/80">
                <TableHead>Nama</TableHead>
                <TableHead>Email</TableHead>
                <TableHead>Telepon</TableHead>
                <TableHead>Role</TableHead>
              <TableHead className="text-right">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {users.map((user) => (
              <TableRow key={user.id}>
                <TableCell className="font-bold text-foreground">{user.name}</TableCell>
                <TableCell className="font-semibold text-muted-foreground">{user.email}</TableCell>
                <TableCell className="font-semibold text-muted-foreground">{user.phone || "-"}</TableCell>
                <TableCell className="font-semibold text-muted-foreground">{formatRole(user.role)}</TableCell>
                <TableCell>
                  <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" size="sm" onClick={() => openEditDialog(user)}>
                      <Edit3 className="size-4" />
                      Edit
                    </Button>
                    <Button
                      type="button"
                      variant="destructive"
                      size="sm"
                      disabled={user.id === currentUser.id}
                      onClick={() => onDelete(user)}
                    >
                      <Trash2 className="size-4" />
                      Hapus
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </Card>

      <div className="grid gap-3 lg:hidden">
        {users.map((user) => (
          <Card key={user.id} className="border-border bg-card p-4 shadow-sm">
            <div className="flex items-start justify-between gap-3">
              <div className="min-w-0">
                <p className="font-bold text-foreground">{user.name}</p>
                <p className="mt-1 text-sm font-semibold text-muted-foreground">{user.email}</p>
                <p className="mt-1 text-sm text-muted-foreground">{user.phone || "-"}</p>
                <p className="mt-2 inline-flex rounded-md border bg-background px-2 py-1 text-xs font-bold text-muted-foreground">{formatRole(user.role)}</p>
              </div>
              <UserCog className="size-5 shrink-0 text-primary" />
            </div>
            <div className="mt-4 flex justify-end gap-2">
              <Button type="button" variant="outline" size="sm" onClick={() => openEditDialog(user)}>
                <Edit3 className="size-4" />
              </Button>
              <Button type="button" variant="destructive" size="sm" disabled={user.id === currentUser.id} onClick={() => onDelete(user)}>
                <Trash2 className="size-4" />
              </Button>
            </div>
          </Card>
        ))}
      </div>

      <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{editingUser ? "Edit User" : "Tambah User"}</DialogTitle>
            <DialogDescription>{editingUser ? "Ubah data user atau reset password." : "Buat akun baru untuk akses aplikasi."}</DialogDescription>
          </DialogHeader>
          <UserForm
            initialUser={editingUser}
            isSubmitting={isSubmitting}
            onSubmit={(input) => {
              if (editingUser) {
                onUpdate(editingUser.id, input);
              } else {
                onCreate(input);
              }
              setIsDialogOpen(false);
            }}
          />
        </DialogContent>
      </Dialog>
    </section>
  );
}

function UserForm({ initialUser, isSubmitting, onSubmit }: { initialUser: User | null; isSubmitting: boolean; onSubmit: (input: UserFormInput) => void }) {
  return (
    <form
      className="space-y-4"
      onSubmit={(event) => {
        event.preventDefault();
        const formData = new FormData(event.currentTarget);
        onSubmit({
          name: String(formData.get("name") ?? ""),
          email: String(formData.get("email") ?? ""),
          phone: String(formData.get("phone") ?? ""),
          address: String(formData.get("address") ?? ""),
          role: String(formData.get("role") ?? "user") as UserRole,
          password: String(formData.get("password") ?? ""),
        });
      }}
    >
      <div className="space-y-2">
        <Label htmlFor="user_name">Nama</Label>
        <Input id="user_name" name="name" required defaultValue={initialUser?.name} placeholder="Nama user" />
      </div>
      <div className="space-y-2">
        <Label htmlFor="user_email">Email</Label>
        <Input id="user_email" name="email" type="email" required defaultValue={initialUser?.email} placeholder="user@pju.local" />
      </div>
      <div className="space-y-2">
        <Label htmlFor="user_phone">Nomor Telepon</Label>
        <Input id="user_phone" name="phone" defaultValue={initialUser?.phone ?? ""} placeholder="0812-0000-0000" />
      </div>
      <div className="space-y-2">
        <Label htmlFor="user_address">Alamat</Label>
        <Input id="user_address" name="address" defaultValue={initialUser?.address ?? ""} placeholder="Alamat user" />
      </div>
      <div className="space-y-2">
        <Label htmlFor="user_role">Role</Label>
        <Select id="user_role" name="role" defaultValue={initialUser?.role ?? "user"}>
          <option value="superadmin">Superadmin</option>
          <option value="surveyor">Surveyor</option>
          <option value="user">User</option>
        </Select>
      </div>
      <div className="space-y-2">
        <Label htmlFor="user_password">{initialUser ? "Password baru" : "Password"}</Label>
        <Input
          id="user_password"
          name="password"
          type="password"
          required={!initialUser}
          minLength={6}
          placeholder={initialUser ? "Kosongkan jika tidak diganti" : "Minimal 6 karakter"}
        />
      </div>
      <Button type="submit" disabled={isSubmitting}>
        {isSubmitting ? "Menyimpan..." : "Simpan User"}
      </Button>
    </form>
  );
}

function formatRole(role: UserRole) {
  const labels: Record<UserRole, string> = {
    superadmin: "Superadmin",
    surveyor: "Surveyor",
    user: "User",
  };

  return labels[role];
}
