"use client";

/* eslint-disable @next/next/no-img-element */

import { BarChart3, ChevronDown, Database, Gauge, KeyRound, LogOut, MapPinned, UserCircle, Users, Zap } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import type { User, UserRole } from "@/lib/types";
import { cn } from "@/lib/utils";

export type AppTab = "dashboard" | "data" | "map" | "reports" | "users";

type AppShellProps = {
  activeTab: AppTab;
  user: User;
  userRole: UserRole;
  onEditProfile: () => void;
  onChangePassword: () => void;
  onTabChange: (tab: AppTab) => void;
  onLogout: () => void;
  children: React.ReactNode;
};

const tabs = [
  { id: "dashboard" as const, label: "Dashboard", icon: Gauge },
  { id: "data" as const, label: "Data Tiang", icon: Database },
  { id: "map" as const, label: "Peta", icon: MapPinned },
  { id: "reports" as const, label: "Laporan", icon: BarChart3 },
  { id: "users" as const, label: "Manajemen User", icon: Users, roles: ["superadmin"] as UserRole[] },
];

export function AppShell({
  activeTab,
  user,
  userRole,
  onEditProfile,
  onChangePassword,
  onTabChange,
  onLogout,
  children,
}: AppShellProps) {
  const visibleTabs = tabs.filter((tab) => !tab.roles || tab.roles.includes(userRole));
  const [isProfileMenuOpen, setIsProfileMenuOpen] = useState(false);
  const profileMenuRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    function closeProfileMenu(event: MouseEvent) {
      if (!profileMenuRef.current?.contains(event.target as Node)) {
        setIsProfileMenuOpen(false);
      }
    }

    document.addEventListener("mousedown", closeProfileMenu);
    return () => document.removeEventListener("mousedown", closeProfileMenu);
  }, []);

  return (
    <div className="min-h-screen bg-background pb-24 lg:grid lg:grid-cols-[280px_1fr] lg:pb-0">
      <aside className="fixed inset-y-0 left-0 z-40 hidden w-72 border-r border-border bg-card text-white lg:flex lg:flex-col">
        <div className="border-b border-white/10 p-6">
          <div className="flex items-center gap-3">
            <div className="flex size-11 items-center justify-center rounded-lg bg-primary text-primary-foreground text-foreground shadow-lg shadow-cyan-400/20">
              <Zap className="size-6" />
            </div>
            <div>
              <p className="text-xs font-bold uppercase tracking-wide text-primary">PJU Surveyor</p>
              <h1 className="text-lg font-bold">Asset Command</h1>
            </div>
          </div>
        </div>

        <nav className="flex-1 space-y-1 p-4">
          {visibleTabs.map((tab) => {
            const Icon = tab.icon;
            const isActive = activeTab === tab.id;
            return (
              <button
                key={tab.id}
                type="button"
                onClick={() => onTabChange(tab.id)}
                className={cn(
                  "focus-ring flex h-11 w-full items-center gap-3 rounded-lg px-3 text-sm font-semibold transition",
                  isActive ? "bg-primary text-primary-foreground text-foreground" : "text-slate-300 hover:bg-background/10 hover:text-white",
                )}
              >
                <Icon className="size-5" />
                {tab.label}
              </button>
            );
          })}
        </nav>

      </aside>

      <div className="min-w-0 lg:col-start-2">
        <header className="sticky top-0 z-30 border-b border-border/80 bg-background/85 backdrop-blur-xl">
          <div className="flex items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <div className="flex min-w-0 items-center gap-3">
              <div className="flex size-10 shrink-0 items-center justify-center rounded-lg bg-card text-primary lg:hidden">
                <Zap className="size-5" />
              </div>
              <div className="min-w-0">
                <p className="text-xs font-bold uppercase tracking-wide text-primary">Gov-tech Asset Management</p>
                <h2 className="truncate text-lg font-bold text-foreground">Pendataan Tiang PJU</h2>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <div ref={profileMenuRef} className="relative">
                <button
                  type="button"
                  onClick={() => setIsProfileMenuOpen((current) => !current)}
                  className="focus-ring flex h-10 items-center gap-2 rounded-md border bg-card px-2 pr-3 text-sm font-bold text-foreground transition hover:bg-background/70 sm:min-w-44"
                >
                  {user.photo_url ? (
                    <img src={user.photo_url} alt={user.name} className="size-7 rounded-full object-cover" />
                  ) : (
                    <span className="flex size-7 items-center justify-center rounded-full bg-primary/10 text-primary">
                      <UserCircle className="size-5" />
                    </span>
                  )}
                  <span className="hidden max-w-36 truncate sm:inline">{user.name}</span>
                  <ChevronDown className="size-4 text-muted-foreground" />
                </button>
                {isProfileMenuOpen && (
                  <div className="absolute right-0 top-12 z-50 w-72 overflow-hidden rounded-lg border bg-card shadow-xl">
                    <div className="border-b p-4">
                      <div className="flex items-center gap-3">
                        {user.photo_url ? (
                          <img src={user.photo_url} alt={user.name} className="size-11 rounded-full object-cover" />
                        ) : (
                          <span className="flex size-11 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <UserCircle className="size-7" />
                          </span>
                        )}
                        <div className="min-w-0">
                          <p className="truncate font-bold text-foreground">{user.name}</p>
                          <p className="truncate text-xs font-semibold text-muted-foreground">{user.email}</p>
                        </div>
                      </div>
                    </div>
                    <div className="p-2">
                      <button
                        type="button"
                        onClick={() => {
                          setIsProfileMenuOpen(false);
                          onEditProfile();
                        }}
                        className="flex h-10 w-full items-center gap-2 rounded-md px-3 text-sm font-semibold text-muted-foreground hover:bg-background"
                      >
                        <UserCircle className="size-4 text-primary" />
                        Ubah Profil
                      </button>
                      <button
                        type="button"
                        onClick={() => {
                          setIsProfileMenuOpen(false);
                          onChangePassword();
                        }}
                        className="flex h-10 w-full items-center gap-2 rounded-md px-3 text-sm font-semibold text-muted-foreground hover:bg-background"
                      >
                        <KeyRound className="size-4 text-primary" />
                        Ubah Password
                      </button>
                      <button
                        type="button"
                        onClick={() => {
                          setIsProfileMenuOpen(false);
                          onLogout();
                        }}
                        className="flex h-10 w-full items-center gap-2 rounded-md px-3 text-sm font-semibold text-destructive hover:bg-background"
                      >
                        <LogOut className="size-4" />
                        Keluar
                      </button>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>
        </header>

        <main className="mx-auto max-w-[1500px] px-4 py-5 sm:px-6 lg:px-8">{children}</main>
      </div>

      <nav className="fixed inset-x-0 bottom-0 z-50 border-t border-white/70 bg-background/80 px-3 py-2 backdrop-blur-xl lg:hidden">
        <div className={cn("mx-auto grid max-w-md gap-2", visibleTabs.length === 5 ? "grid-cols-5" : "grid-cols-4")}>
          {visibleTabs.map((tab) => {
            const Icon = tab.icon;
            const isActive = activeTab === tab.id;
            return (
              <button
                key={tab.id}
                type="button"
                onClick={() => onTabChange(tab.id)}
                className={cn(
                  "focus-ring flex h-14 flex-col items-center justify-center gap-1 rounded-md text-xs font-bold transition",
                  isActive ? "bg-primary text-primary-foreground shadow-sm" : "text-muted-foreground hover:bg-card/5",
                )}
              >
                <Icon className="size-5" />
                {tab.label}
              </button>
            );
          })}
        </div>
      </nav>
    </div>
  );
}
