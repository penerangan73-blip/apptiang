"use client";

import { ShieldCheck, Zap } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { GlassPanel } from "@/components/glass-panel";

type LoginScreenProps = {
  error?: string;
  isLoading?: boolean;
  onLogin: (credentials: { email: string; password: string }) => void;
};

export function LoginScreen({ error, isLoading = false, onLogin }: LoginScreenProps) {
  return (
    <main className="flex min-h-screen items-center justify-center px-4 py-8">
      <GlassPanel className="w-full max-w-md p-6 sm:p-8">
        <div className="mb-8 flex items-center gap-3">
          <div className="flex size-12 items-center justify-center rounded-lg bg-primary text-primary-foreground shadow-lg shadow-teal-700/20">
            <Zap className="size-6" />
          </div>
          <div>
            <p className="text-sm font-bold uppercase tracking-wide text-primary">PJU Surveyor</p>
            <h1 className="text-2xl font-bold text-foreground">Pendataan Tiang Lapangan</h1>
          </div>
        </div>

        <div className="mb-6 rounded-lg border bg-background/60 p-4">
          <div className="flex items-start gap-3">
            <ShieldCheck className="mt-0.5 size-5 text-primary" />
            <p className="text-sm leading-6 text-muted-foreground">
              Masuk sebagai surveyor untuk mencatat lokasi, kondisi, daya, dan laporan kerusakan PJU.
            </p>
          </div>
        </div>

        <form
          className="space-y-4"
          onSubmit={(event) => {
            event.preventDefault();
            const formData = new FormData(event.currentTarget);
            onLogin({
              email: String(formData.get("email") ?? ""),
              password: String(formData.get("password") ?? ""),
            });
          }}
        >
          <div className="space-y-2">
            <Label htmlFor="email">Email</Label>
            <Input id="email" name="email" type="email" defaultValue="surveyor@pju.local" autoComplete="email" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="password">Kata sandi</Label>
            <Input id="password" name="password" type="password" defaultValue="password" autoComplete="current-password" />
          </div>
          {error && <p className="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700">{error}</p>}
          <Button type="submit" className="h-12 w-full" disabled={isLoading}>
            {isLoading ? "Memeriksa akun..." : "Masuk Dashboard"}
          </Button>
        </form>
      </GlassPanel>
    </main>
  );
}
