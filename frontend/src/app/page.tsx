"use client";

/* eslint-disable @next/next/no-img-element */

import dynamic from "next/dynamic";
import { useEffect, useMemo, useState } from "react";
import {
  Activity,
  AlertTriangle,
  CheckCircle2,
  ClipboardList,
  Download,
  Gauge,
  MapPinned,
  PlusCircle,
  Radar,
  RefreshCw,
  Route,
  ShieldCheck,
  Zap,
} from "lucide-react";
import { AppShell, type AppTab } from "@/components/app-shell";
import { DamageReportForm } from "@/components/damage-report-form";
import { LoginScreen } from "@/components/login-screen";
import { PoleForm } from "@/components/pole-form";
import { PolesDataTable } from "@/components/poles-data-table";
import { PasswordForm, ProfileForm } from "@/components/profile-forms";
import { ReportSummary } from "@/components/report-summary";
import { SearchFilter } from "@/components/search-filter";
import { StatCard } from "@/components/stat-card";
import { StatusBadge } from "@/components/status-badge";
import { UserManagement } from "@/components/user-management";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import {
  ApiError,
  clearStoredToken,
  createDamageReport,
  createPole,
  createUser,
  deletePole,
  deleteUser,
  getPoles,
  getSnapshot,
  getStoredToken,
  login,
  logout,
  type LoginCredentials,
  type PjuSnapshot,
  updatePassword,
  updatePole,
  updateProfile,
  updateUser,
} from "@/lib/pju-service";
import type { PasswordFormInput, Pole, PoleCondition, PoleFormInput, ProfileFormInput, ReportInput, User, UserFormInput } from "@/lib/types";

const PoleMap = dynamic(() => import("@/components/pole-map").then((mod) => mod.PoleMap), {
  ssr: false,
  loading: () => (
    <div className="flex min-h-[360px] items-center justify-center rounded-lg bg-background/60 text-sm font-semibold text-muted-foreground">
      Memuat peta...
    </div>
  ),
});

export default function Home() {
  const [activeTab, setActiveTab] = useState<AppTab>("dashboard");
  const [token, setToken] = useState<string | null>(null);
  const [snapshot, setSnapshot] = useState<PjuSnapshot | null>(null);
  const [streetQuery, setStreetQuery] = useState("");
  const [conditionFilter, setConditionFilter] = useState<PoleCondition | "semua">("semua");
  const [selectedPole, setSelectedPole] = useState<Pole | null>(null);
  const [detailPole, setDetailPole] = useState<Pole | null>(null);
  const [editingPole, setEditingPole] = useState<Pole | null>(null);
  const [notice, setNotice] = useState("Terhubung ke Laravel API. Data diambil dari backend.");
  const [loginError, setLoginError] = useState("");
  const [isBooting, setIsBooting] = useState(true);
  const [isLoginLoading, setIsLoginLoading] = useState(false);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isSubmittingPole, setIsSubmittingPole] = useState(false);
  const [isSubmittingReport, setIsSubmittingReport] = useState(false);
  const [isSubmittingUser, setIsSubmittingUser] = useState(false);
  const [isSubmittingProfile, setIsSubmittingProfile] = useState(false);
  const [isSubmittingPassword, setIsSubmittingPassword] = useState(false);
  const [isPoleDialogOpen, setIsPoleDialogOpen] = useState(false);
  const [isReportDialogOpen, setIsReportDialogOpen] = useState(false);
  const [isDetailDialogOpen, setIsDetailDialogOpen] = useState(false);
  const [isProfileDialogOpen, setIsProfileDialogOpen] = useState(false);
  const [isPasswordDialogOpen, setIsPasswordDialogOpen] = useState(false);

  useEffect(() => {
    const storedToken = getStoredToken();

    if (!storedToken) {
      setIsBooting(false);
      return;
    }

    setToken(storedToken);
    setIsRefreshing(true);
    void getSnapshot(storedToken)
      .then(setSnapshot)
      .catch((error) => {
        clearStoredToken();
        setToken(null);
        setNotice(getErrorMessage(error));
      })
      .finally(() => {
        setIsRefreshing(false);
        setIsBooting(false);
      });
  }, []);

  useEffect(() => {
    if (!snapshot?.poles.length) {
      setSelectedPole(null);
      return;
    }

    setSelectedPole((current) => {
      const updated = current ? snapshot.poles.find((pole) => pole.id === current.id) : null;
      return updated ?? snapshot.poles[0] ?? null;
    });
  }, [snapshot]);

  const filteredPoles = useMemo(() => {
    if (!snapshot) {
      return [];
    }

    const streetFiltered = getPoles(snapshot, streetQuery);
    if (conditionFilter === "semua") {
      return streetFiltered;
    }
    return streetFiltered.filter((pole) => pole.condition === conditionFilter);
  }, [snapshot, conditionFilter, streetQuery]);

  const todayReports = useMemo(() => {
    const today = new Date().toISOString().slice(0, 10);
    return (snapshot?.reports ?? []).filter((report) => report.report_date === today);
  }, [snapshot?.reports]);

  const dashboardPoles = useMemo(() => (snapshot?.poles ?? []).slice(0, 5), [snapshot?.poles]);
  const canManageUsers = snapshot?.user.role === "superadmin";
  const canWritePoles = snapshot?.user.role === "superadmin" || snapshot?.user.role === "surveyor";
  const canCreateReports = canWritePoles;

  useEffect(() => {
    if (activeTab === "users" && snapshot && !canManageUsers) {
      setActiveTab("dashboard");
    }
  }, [activeTab, canManageUsers, snapshot]);

  async function refreshSnapshot(authToken = token, options: { quiet?: boolean } = {}) {
    if (!authToken) {
      return;
    }

    setIsRefreshing(true);

    try {
      const nextSnapshot = await getSnapshot(authToken);
      setSnapshot(nextSnapshot);
      if (!options.quiet) {
        setNotice("Data terbaru berhasil disinkronkan dari Laravel API.");
      }
    } catch (error) {
      if (error instanceof ApiError && error.status === 401) {
        clearStoredToken();
        setToken(null);
        setSnapshot(null);
        setNotice("Sesi berakhir. Silakan login ulang.");
      } else {
        setNotice(getErrorMessage(error));
      }
    } finally {
      setIsRefreshing(false);
    }
  }

  async function handleLogin(credentials: LoginCredentials) {
    setIsLoginLoading(true);
    setLoginError("");

    try {
      const session = await login(credentials);
      setToken(session.token);
      await refreshSnapshot(session.token, { quiet: true });
      setNotice(`Halo, ${session.user.name}. Data backend siap digunakan.`);
    } catch (error) {
      setLoginError(getErrorMessage(error));
      clearStoredToken();
    } finally {
      setIsLoginLoading(false);
    }
  }

  async function handleLogout() {
    if (token) {
      try {
        await logout(token);
      } catch {
        clearStoredToken();
      }
    }

    setToken(null);
    setSnapshot(null);
    setActiveTab("dashboard");
  }

  async function handleProfileSubmit(input: ProfileFormInput) {
    if (!token) {
      return;
    }

    setIsSubmittingProfile(true);

    try {
      const response = await updateProfile(token, input);
      setSnapshot((current) => (current ? { ...current, user: response.user } : current));
      await refreshSnapshot(token, { quiet: true });
      setNotice("Profil berhasil diperbarui.");
      setIsProfileDialogOpen(false);
    } catch (error) {
      setNotice(getErrorMessage(error));
    } finally {
      setIsSubmittingProfile(false);
    }
  }

  async function handlePasswordSubmit(input: PasswordFormInput) {
    if (!token) {
      return;
    }

    setIsSubmittingPassword(true);

    try {
      await updatePassword(token, input);
      setNotice("Password berhasil diperbarui.");
      setIsPasswordDialogOpen(false);
    } catch (error) {
      setNotice(getErrorMessage(error));
    } finally {
      setIsSubmittingPassword(false);
    }
  }

  async function handlePoleSubmit(input: PoleFormInput, id?: number) {
    if (!token || !canWritePoles) {
      return;
    }

    setIsSubmittingPole(true);

    try {
      const savedPole = id ? await updatePole(token, id, input) : await createPole(token, input);
      await refreshSnapshot(token, { quiet: true });
      setSelectedPole(savedPole);
      setNotice(id ? `Perubahan ${savedPole.customer_id} berhasil disimpan.` : `${savedPole.customer_id} berhasil ditambahkan.`);
      setEditingPole(null);
      setIsPoleDialogOpen(false);
    } catch (error) {
      setNotice(getErrorMessage(error));
    } finally {
      setIsSubmittingPole(false);
    }
  }

  async function handleDamageReport(input: ReportInput) {
    if (!token || !canCreateReports) {
      return;
    }

    setIsSubmittingReport(true);

    try {
      await createDamageReport(token, input);
      await refreshSnapshot(token, { quiet: true });
      const pole = snapshot?.poles.find((item) => item.id === input.pole_id);
      setNotice(`Laporan kerusakan ${pole?.customer_id ?? "tiang"} berhasil disimpan.`);
      setIsReportDialogOpen(false);
      setActiveTab("reports");
    } catch (error) {
      setNotice(getErrorMessage(error));
    } finally {
      setIsSubmittingReport(false);
    }
  }

  function handleEdit(pole: Pole) {
    if (!canWritePoles) {
      return;
    }

    setEditingPole(pole);
    setIsPoleDialogOpen(true);
  }

  async function handleDelete(pole: Pole) {
    if (!token || !canWritePoles) {
      return;
    }

    const confirmed = window.confirm(`Hapus data ${pole.customer_id}? Data laporan terkait juga akan ikut terhapus.`);

    if (!confirmed) {
      return;
    }

    try {
      await deletePole(token, pole.id);
      await refreshSnapshot(token, { quiet: true });
      setSelectedPole((current) => (current?.id === pole.id ? null : current));
      setDetailPole((current) => (current?.id === pole.id ? null : current));
      setIsDetailDialogOpen(false);
      setNotice(`Data ${pole.customer_id} berhasil dihapus.`);
    } catch (error) {
      setNotice(getErrorMessage(error));
    }
  }

  function handleDetail(pole: Pole) {
    setDetailPole(pole);
    setIsDetailDialogOpen(true);
  }

  function handleReport(pole: Pole) {
    if (!canCreateReports) {
      return;
    }

    setSelectedPole(pole);
    setIsReportDialogOpen(true);
  }

  async function handleUserCreate(input: UserFormInput) {
    if (!token || !canManageUsers) {
      return;
    }

    setIsSubmittingUser(true);

    try {
      const savedUser = await createUser(token, input);
      await refreshSnapshot(token, { quiet: true });
      setNotice(`${savedUser.name} berhasil ditambahkan.`);
    } catch (error) {
      setNotice(getErrorMessage(error));
    } finally {
      setIsSubmittingUser(false);
    }
  }

  async function handleUserUpdate(id: number, input: UserFormInput) {
    if (!token || !canManageUsers) {
      return;
    }

    setIsSubmittingUser(true);

    try {
      const savedUser = await updateUser(token, id, input);
      await refreshSnapshot(token, { quiet: true });
      setNotice(`${savedUser.name} berhasil diperbarui.`);
    } catch (error) {
      setNotice(getErrorMessage(error));
    } finally {
      setIsSubmittingUser(false);
    }
  }

  async function handleUserDelete(user: User) {
    if (!token || !canManageUsers) {
      return;
    }

    const confirmed = window.confirm(`Hapus user ${user.name}?`);

    if (!confirmed) {
      return;
    }

    setIsSubmittingUser(true);

    try {
      await deleteUser(token, user.id);
      await refreshSnapshot(token, { quiet: true });
      setNotice(`${user.name} berhasil dihapus.`);
    } catch (error) {
      setNotice(getErrorMessage(error));
    } finally {
      setIsSubmittingUser(false);
    }
  }

  if (isBooting) {
    return (
      <main className="flex min-h-screen items-center justify-center px-4">
        <Card className="w-full max-w-md border-border bg-card shadow-sm">
          <CardContent className="flex items-center gap-3 p-5">
            <RefreshCw className="size-5 animate-spin text-primary" />
            <p className="text-sm font-semibold text-muted-foreground">Menghubungkan ke Laravel API...</p>
          </CardContent>
        </Card>
      </main>
    );
  }

  if (!token || !snapshot) {
    return <LoginScreen error={loginError} isLoading={isLoginLoading} onLogin={handleLogin} />;
  }

  return (
    <AppShell
      activeTab={activeTab}
      user={snapshot.user}
      userRole={snapshot.user.role}
      onEditProfile={() => setIsProfileDialogOpen(true)}
      onChangePassword={() => setIsPasswordDialogOpen(true)}
      onTabChange={setActiveTab}
      onLogout={handleLogout}
    >
      <div className="space-y-5">
        {activeTab !== "dashboard" && (
          <Card className="border-border bg-card shadow-sm">
            <CardContent className="p-4">
              <div className="flex items-start gap-3">
                <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary ring-1 ring-primary/30">
                  <CheckCircle2 className="size-5" />
                </div>
                <div>
                  <p className="text-sm font-bold text-foreground">{notice}</p>
                  <p className="mt-1 text-xs text-muted-foreground">Status terakhir dari aksi data aset.</p>
                </div>
              </div>
            </CardContent>
          </Card>
        )}

        {activeTab === "dashboard" && (
          <>
            <section className="grid gap-4 xl:grid-cols-[1fr_380px]">
              <Card className="overflow-hidden border-border bg-card text-white shadow-sm">
                <CardContent className="p-5 sm:p-6">
                  <div className="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div className="max-w-3xl">
                      <div className="mb-4 inline-flex items-center gap-2 rounded-full border border-primary/30 bg-cyan-300/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-primary">
                        <ShieldCheck className="size-4" />
                        Backend connected
                      </div>
                      <h2 className="text-2xl font-bold sm:text-3xl">Halo, {snapshot.user.name}</h2>
                      <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        Monitor lokasi, kondisi, daya, dan laporan kerusakan tiang PJU dari database MySQL melalui Laravel API.
                      </p>
                    </div>
                    <div className="flex flex-col gap-2 sm:flex-row lg:flex-col xl:flex-row">
                      {canWritePoles && (
                        <Button
                          type="button"
                          className="bg-primary text-primary-foreground text-foreground hover:bg-cyan-300"
                          onClick={() => {
                            setEditingPole(null);
                            setIsPoleDialogOpen(true);
                          }}
                        >
                          <PlusCircle className="size-4" />
                          Tambah Tiang
                        </Button>
                      )}
                      <Button
                        type="button"
                        variant="outline"
                        className="border-white/20 bg-background/10 text-white hover:bg-background/15"
                        disabled={isRefreshing}
                        onClick={() => refreshSnapshot()}
                      >
                        <RefreshCw className={isRefreshing ? "size-4 animate-spin" : "size-4"} />
                        Sync API
                      </Button>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card className="border-border bg-card shadow-sm">
                <CardContent className="p-5">
                  <div className="flex items-start gap-3">
                    <div className="flex size-11 items-center justify-center rounded-lg bg-primary/10 text-primary ring-1 ring-primary/30">
                      <CheckCircle2 className="size-5" />
                    </div>
                    <div>
                      <p className="text-sm font-bold text-foreground">{notice}</p>
                      <p className="mt-1 text-sm leading-6 text-muted-foreground">
                        Tambah, edit, dan laporan kerusakan sekarang dikirim ke backend Laravel.
                      </p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </section>

            <ReportSummary poles={snapshot.poles} reports={snapshot.reports} />

            <section className="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
              <div className="min-w-0 space-y-5">
                <Card className="border-border bg-card shadow-sm">
                  <CardHeader className="flex-row items-center justify-between gap-4 space-y-0">
                    <div>
                      <CardTitle className="flex items-center gap-2">
                        <Gauge className="size-5 text-primary" />
                        Dashboard Operasional
                      </CardTitle>
                      <CardDescription>Ringkasan aset, aktivitas harian, dan prioritas tindak lanjut.</CardDescription>
                    </div>
                    <Button type="button" onClick={() => setActiveTab("map")} className="hidden sm:inline-flex">
                      <MapPinned className="size-4" />
                      Buka Peta
                    </Button>
                  </CardHeader>
                  <CardContent>
                    <div className="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
                      <div className="glass-panel h-[300px] overflow-hidden rounded-lg sm:h-[360px]">
                        <PoleMap poles={snapshot.poles} onSelect={setSelectedPole} />
                      </div>
                      <div className="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                        <StatCard label="Produktivitas" value={todayReports.length} description="laporan hari ini" icon={Activity} tone="cyan" />
                        <StatCard
                          label="Prioritas"
                          value={snapshot.poles.filter((pole) => pole.condition === "rusak").length}
                          description="tiang rusak"
                          icon={AlertTriangle}
                          tone="rose"
                        />
                        <StatCard
                          label="Beban Daya"
                          value={snapshot.poles.reduce((total, pole) => total + pole.power_usage, 0)}
                          description="total watt terdata"
                          icon={Zap}
                          tone="amber"
                        />
                      </div>
                    </div>
                  </CardContent>
                </Card>

                <Card className="border-border bg-card shadow-sm">
                  <CardHeader>
                    <CardTitle>Aset Terbaru</CardTitle>
                    <CardDescription>Data tiang yang siap divalidasi atau diperbarui.</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <PolesDataTable
                      poles={dashboardPoles}
                      showManagementActions={false}
                      showReportAction={false}
                      onDetail={handleDetail}
                      onDelete={handleDelete}
                      onEdit={handleEdit}
                      onReport={handleReport}
                    />
                  </CardContent>
                </Card>
              </div>

              <div className="min-w-0 space-y-5">
                <Card className="border-border bg-card shadow-sm">
                  <CardHeader>
                    <CardTitle>Kondisi Lapangan</CardTitle>
                    <CardDescription>Distribusi kondisi aset PJU.</CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-3">
                    {(["baik", "rusak", "perbaikan"] as PoleCondition[]).map((condition) => {
                      const count = snapshot.poles.filter((pole) => pole.condition === condition).length;
                      const percent = Math.round((count / Math.max(snapshot.poles.length, 1)) * 100);
                      return (
                        <div key={condition} className="space-y-2">
                          <div className="flex items-center justify-between gap-3">
                            <StatusBadge condition={condition} />
                            <p className="text-sm font-bold text-muted-foreground">{percent}%</p>
                          </div>
                          <div className="h-2 overflow-hidden rounded-full bg-secondary">
                            <div className="h-full rounded-full bg-cyan-600" style={{ width: `${percent}%` }} />
                          </div>
                        </div>
                      );
                    })}
                  </CardContent>
                </Card>

                <Card className="border-border bg-card shadow-sm">
                  <CardHeader>
                    <CardTitle>Aktivitas Hari Ini</CardTitle>
                    <CardDescription>Rekap singkat pekerjaan surveyor.</CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-3">
                    {todayReports.slice(0, 5).map((report) => {
                      const pole = snapshot.poles.find((item) => item.id === report.pole_id);
                      return (
                        <div key={report.id} className="rounded-lg border bg-background p-3">
                          <p className="font-bold text-foreground">{pole?.customer_id ?? "Tiang tidak ditemukan"}</p>
                          <p className="text-sm text-muted-foreground">{pole?.street_name}</p>
                          <p className="mt-2 text-sm leading-6 text-muted-foreground">{report.notes}</p>
                        </div>
                      );
                    })}
                  </CardContent>
                </Card>
              </div>
            </section>
          </>
        )}

        {activeTab === "map" && (
          <section className="space-y-5">
            <div className="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
              <Card className="overflow-hidden border-border bg-card shadow-sm">
                  <CardHeader className="flex-row items-center justify-between gap-4 space-y-0">
                    <div>
                      <CardTitle className="flex items-center gap-2">
                        <MapPinned className="size-5 text-primary" />
                        Peta Lokasi Tiang
                      </CardTitle>
                      <CardDescription>Titik aset berdasarkan filter jalan dan kondisi.</CardDescription>
                    </div>
                    <div className="hidden items-center gap-2 rounded-full bg-secondary px-3 py-1 text-xs font-bold text-muted-foreground sm:flex">
                      <Radar className="size-4 text-primary" />
                      Data API
                    </div>
                  </CardHeader>
                  <CardContent>
                    <div className="glass-panel h-[360px] overflow-hidden rounded-lg sm:h-[460px] xl:h-[560px]">
                      <PoleMap poles={filteredPoles} onSelect={setSelectedPole} />
                    </div>
                  </CardContent>
                </Card>

              <Card className="border-border bg-card shadow-sm">
                <CardHeader>
                  <CardTitle>Inspeksi Terpilih</CardTitle>
                  <CardDescription>Detail cepat untuk surveyor lapangan.</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  {selectedPole ? (
                    <>
                      <div className="rounded-lg border border-border bg-background p-4">
                        <div className="flex items-start justify-between gap-3">
                          <div>
                            <p className="text-xl font-bold text-foreground">{selectedPole.customer_id}</p>
                            <p className="mt-1 flex items-center gap-2 text-sm text-muted-foreground">
                              <Route className="size-4 text-primary" />
                              {selectedPole.street_name}
                            </p>
                          </div>
                          <StatusBadge condition={selectedPole.condition} />
                        </div>
                      </div>
                      <div className="grid grid-cols-2 gap-3">
                        <div className="rounded-lg border bg-card p-3">
                          <p className="text-xs font-bold uppercase tracking-wide text-muted-foreground">Daya</p>
                          <p className="mt-1 text-lg font-bold text-foreground">{selectedPole.power_usage} W</p>
                        </div>
                        <div className="rounded-lg border bg-card p-3">
                          <p className="text-xs font-bold uppercase tracking-wide text-muted-foreground">Koordinat</p>
                          <p className="mt-1 text-sm font-bold text-foreground">{selectedPole.latitude.toFixed(4)}</p>
                        </div>
                        <div className="rounded-lg border bg-card p-3">
                          <p className="text-xs font-bold uppercase tracking-wide text-muted-foreground">Jenis</p>
                          <p className="mt-1 text-sm font-bold capitalize text-foreground">{formatOptionLabel(selectedPole.pole_type)}</p>
                        </div>
                        <div className="rounded-lg border bg-card p-3">
                          <p className="text-xs font-bold uppercase tracking-wide text-muted-foreground">Tinggi</p>
                          <p className="mt-1 text-sm font-bold text-foreground">{formatMeasurement(selectedPole.height)}</p>
                        </div>
                        <div className="rounded-lg border bg-card p-3">
                          <p className="text-xs font-bold uppercase tracking-wide text-muted-foreground">Bentangan</p>
                          <p className="mt-1 text-sm font-bold text-foreground">{formatMeasurement(selectedPole.span_length)}</p>
                        </div>
                        <div className="rounded-lg border bg-card p-3">
                          <p className="text-xs font-bold uppercase tracking-wide text-muted-foreground">Pondasi</p>
                          <p className="mt-1 text-sm font-bold capitalize text-foreground">{formatOptionLabel(selectedPole.foundation_type)}</p>
                        </div>
                        <div className="rounded-lg border bg-card p-3">
                          <p className="text-xs font-bold uppercase tracking-wide text-muted-foreground">Kepemilikan</p>
                          <p className="mt-1 text-sm font-bold uppercase text-foreground">{formatOwnership(selectedPole.ownership)}</p>
                        </div>
                        <div className="rounded-lg border bg-card p-3">
                          <p className="text-xs font-bold uppercase tracking-wide text-muted-foreground">Trafo PLN</p>
                          <p className="mt-1 text-sm font-bold text-foreground">{selectedPole.pln_transformer || "-"}</p>
                        </div>
                      </div>
                      {selectedPole.notes && (
                        <div className="rounded-lg border bg-card p-3">
                          <p className="text-xs font-bold uppercase tracking-wide text-muted-foreground">Keterangan</p>
                          <p className="mt-1 text-sm leading-6 text-muted-foreground">{selectedPole.notes}</p>
                        </div>
                      )}
                      {selectedPole.photo_url && (
                        <img src={selectedPole.photo_url} alt={`Foto ${selectedPole.customer_id}`} className="max-h-48 rounded-md border object-cover" />
                      )}
                      <div className="grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                        {canWritePoles && (
                          <Button type="button" onClick={() => handleEdit(selectedPole)}>
                            Edit Data
                          </Button>
                        )}
                        {canCreateReports && (
                          <Button type="button" variant="outline" onClick={() => handleReport(selectedPole)}>
                            Lapor Kerusakan
                          </Button>
                        )}
                      </div>
                    </>
                  ) : (
                    <p className="text-sm text-muted-foreground">Pilih titik tiang pada peta atau tabel.</p>
                  )}
                </CardContent>
              </Card>
            </div>

            <SearchFilter
              streetQuery={streetQuery}
              conditionFilter={conditionFilter}
              onStreetQueryChange={setStreetQuery}
              onConditionFilterChange={setConditionFilter}
            />

            <PolesDataTable
              poles={filteredPoles}
              showManagementActions={canWritePoles}
              showReportAction={canCreateReports}
              onDetail={handleDetail}
              onDelete={handleDelete}
              onEdit={handleEdit}
              onReport={handleReport}
            />
          </section>
        )}

        {activeTab === "data" && (
          <section className="space-y-5">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 className="text-2xl font-bold text-foreground">Data Tiang PJU</h2>
                <p className="text-sm text-muted-foreground">Kelola inventaris tiang PJU dari database backend.</p>
              </div>
              <div className="flex flex-col gap-2 sm:flex-row">
                <Button type="button" variant="outline" onClick={() => downloadPolesAsExcel(filteredPoles)}>
                  <Download className="size-4" />
                  Download Data
                </Button>
                {canWritePoles && (
                  <Button
                    type="button"
                    className="bg-primary text-primary-foreground text-foreground hover:bg-cyan-300"
                    onClick={() => {
                      setEditingPole(null);
                      setIsPoleDialogOpen(true);
                    }}
                  >
                    <PlusCircle className="mr-2 size-4" />
                    Tambah Tiang Baru
                  </Button>
                )}
              </div>
            </div>

            <SearchFilter
              streetQuery={streetQuery}
              conditionFilter={conditionFilter}
              onStreetQueryChange={setStreetQuery}
              onConditionFilterChange={setConditionFilter}
            />

            <PolesDataTable
              poles={filteredPoles}
              showTimestamps
              showManagementActions={canWritePoles}
              showReportAction={canCreateReports}
              onDetail={handleDetail}
              onDelete={handleDelete}
              onEdit={handleEdit}
              onReport={handleReport}
            />
          </section>
        )}

        {activeTab === "users" && canManageUsers && (
          <UserManagement
            currentUser={snapshot.user}
            users={snapshot.users ?? []}
            isSubmitting={isSubmittingUser}
            onCreate={handleUserCreate}
            onUpdate={handleUserUpdate}
            onDelete={handleUserDelete}
          />
        )}

        {activeTab === "reports" && (
          <Card className="border-border bg-card shadow-sm">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <ClipboardList className="size-5 text-primary" />
                Laporan Harian
              </CardTitle>
              <CardDescription>Rekap aktivitas pendataan dan kerusakan hari ini.</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="hidden lg:block">
                <Table>
                  <TableHeader>
                    <TableRow className="bg-background">
                      <TableHead>Tanggal</TableHead>
                      <TableHead>ID Pelanggan</TableHead>
                      <TableHead>Jalan</TableHead>
                      <TableHead>Catatan</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {snapshot.reports.map((report) => {
                      const pole = snapshot.poles.find((item) => item.id === report.pole_id);
                      return (
                        <TableRow key={report.id}>
                          <TableCell className="font-semibold">{report.report_date}</TableCell>
                          <TableCell>{pole?.customer_id ?? "Tiang tidak ditemukan"}</TableCell>
                          <TableCell>{pole?.street_name}</TableCell>
                          <TableCell>{report.notes}</TableCell>
                        </TableRow>
                      );
                    })}
                  </TableBody>
                </Table>
              </div>
              <div className="space-y-3 lg:hidden">
                {snapshot.reports.map((report) => {
                  const pole = snapshot.poles.find((item) => item.id === report.pole_id);
                  return (
                    <article key={report.id} className="rounded-lg border bg-background/65 p-4">
                      <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                          <p className="font-bold text-foreground">{pole?.customer_id ?? "Tiang tidak ditemukan"}</p>
                          <p className="text-sm text-muted-foreground">{pole?.street_name}</p>
                          <p className="mt-2 text-sm leading-6 text-muted-foreground">{report.notes}</p>
                        </div>
                        <p className="rounded-full border bg-card px-3 py-1 text-xs font-bold text-muted-foreground">{report.report_date}</p>
                      </div>
                      {report.photo_url && (
                        <img
                          src={report.photo_url}
                          alt={`Foto laporan ${pole?.customer_id ?? ""}`}
                          className="mt-3 max-h-44 rounded-md border object-cover"
                        />
                      )}
                    </article>
                  );
                })}
              </div>
            </CardContent>
          </Card>
        )}
      </div>

      <Dialog open={isPoleDialogOpen} onOpenChange={setIsPoleDialogOpen}>
        <DialogContent className="max-w-4xl">
          <DialogHeader>
            <DialogTitle>{editingPole ? "Edit Data Tiang" : "Tambah Data Tiang"}</DialogTitle>
            <DialogDescription>Isi ID pelanggan, lokasi, spesifikasi, foto, dan keterangan aset PJU.</DialogDescription>
          </DialogHeader>
          <PoleForm key={editingPole?.id ?? "new"} initialPole={editingPole} isSubmitting={isSubmittingPole} onSubmit={handlePoleSubmit} />
        </DialogContent>
      </Dialog>

      <Dialog open={isDetailDialogOpen} onOpenChange={setIsDetailDialogOpen}>
        <DialogContent className="max-w-4xl">
          <DialogHeader>
            <DialogTitle>Detail Data Tiang</DialogTitle>
            <DialogDescription>Informasi lengkap aset PJU, termasuk foto jika tersedia.</DialogDescription>
          </DialogHeader>
          {detailPole && (
            <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_280px]">
              <div className="grid gap-3 sm:grid-cols-2">
                <DetailItem label="ID Pelanggan" value={detailPole.customer_id} />
                <DetailItem label="Kondisi" value={formatOptionLabel(detailPole.condition)} />
                <DetailItem label="Nama Jalan" value={detailPole.street_name} className="sm:col-span-2" />
                <DetailItem label="Latitude" value={detailPole.latitude.toFixed(7)} />
                <DetailItem label="Longitude" value={detailPole.longitude.toFixed(7)} />
                <DetailItem label="Daya" value={`${detailPole.power_usage} Watt`} />
                <DetailItem label="Jenis Tiang" value={formatOptionLabel(detailPole.pole_type)} />
                <DetailItem label="Tinggi" value={formatMeasurement(detailPole.height)} />
                <DetailItem label="Bentangan" value={formatMeasurement(detailPole.span_length)} />
                <DetailItem label="Pondasi" value={formatOptionLabel(detailPole.foundation_type)} />
                <DetailItem label="Kepemilikan" value={formatOwnership(detailPole.ownership)} />
                <DetailItem label="Trafo PLN" value={detailPole.pln_transformer || "-"} />
                <DetailItem label="Pertama Diinput" value={formatDateTime(detailPole.created_at)} />
                <DetailItem label="Diinput Oleh" value={formatUserName(detailPole.created_by)} />
                <DetailItem label="Update Terakhir" value={formatDateTime(detailPole.updated_at)} />
                <DetailItem label="Update Oleh" value={formatUserName(detailPole.updated_by)} />
                <DetailItem label="Keterangan" value={detailPole.notes || "-"} className="sm:col-span-2" />
              </div>
              <div className="rounded-lg border bg-background/55 p-3">
                <p className="mb-3 text-xs font-bold uppercase tracking-wide text-muted-foreground">Foto</p>
                {detailPole.photo_url ? (
                  <img src={detailPole.photo_url} alt={`Foto ${detailPole.customer_id}`} className="max-h-80 w-full rounded-md object-cover" />
                ) : (
                  <div className="flex min-h-48 items-center justify-center rounded-md border border-dashed text-sm font-semibold text-muted-foreground">
                    Belum ada foto
                  </div>
                )}
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>

      <Dialog open={isReportDialogOpen} onOpenChange={setIsReportDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Laporan Kerusakan</DialogTitle>
            <DialogDescription>Unggah foto dan tambahkan catatan kerusakan dari lapangan.</DialogDescription>
          </DialogHeader>
          <DamageReportForm selectedPole={selectedPole} isSubmitting={isSubmittingReport} onSubmit={handleDamageReport} />
        </DialogContent>
      </Dialog>

      <Dialog open={isProfileDialogOpen} onOpenChange={setIsProfileDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Ubah Profil</DialogTitle>
            <DialogDescription>Perbarui nama, email, telepon, foto, dan alamat profil.</DialogDescription>
          </DialogHeader>
          <ProfileForm user={snapshot.user} isSubmitting={isSubmittingProfile} onSubmit={handleProfileSubmit} />
        </DialogContent>
      </Dialog>

      <Dialog open={isPasswordDialogOpen} onOpenChange={setIsPasswordDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Ubah Password</DialogTitle>
            <DialogDescription>Masukkan password saat ini lalu password baru.</DialogDescription>
          </DialogHeader>
          <PasswordForm isSubmitting={isSubmittingPassword} onSubmit={handlePasswordSubmit} />
        </DialogContent>
      </Dialog>
    </AppShell>
  );
}

function getErrorMessage(error: unknown) {
  if (error instanceof Error) {
    return error.message;
  }

  return "Terjadi kesalahan saat menghubungi API.";
}

function DetailItem({ label, value, className = "" }: { label: string; value: string | number; className?: string }) {
  return (
    <div className={`rounded-lg border bg-background/55 p-3 ${className}`}>
      <p className="text-xs font-bold uppercase tracking-wide text-muted-foreground">{label}</p>
      <p className="mt-1 break-words text-sm font-semibold text-foreground">{value}</p>
    </div>
  );
}

function downloadPolesAsExcel(poles: Pole[]) {
  const headers = [
    "ID Pelanggan",
    "Nama Jalan",
    "Latitude",
    "Longitude",
    "Daya Watt",
    "Kondisi",
    "Jenis Tiang",
    "Tinggi",
    "Bentangan",
    "Pondasi",
    "Kepemilikan",
    "Trafo PLN",
    "Pertama Diinput",
    "Diinput Oleh",
    "Update Terakhir",
    "Update Oleh",
    "Foto URL",
    "Keterangan",
  ];

  const rows = poles.map((pole) => [
    pole.customer_id,
    pole.street_name,
    pole.latitude,
    pole.longitude,
    pole.power_usage,
    formatOptionLabel(pole.condition),
    formatOptionLabel(pole.pole_type),
    pole.height ?? "",
    pole.span_length ?? "",
    formatOptionLabel(pole.foundation_type),
    formatOwnership(pole.ownership),
    pole.pln_transformer ?? "",
    formatDateTime(pole.created_at),
    formatUserName(pole.created_by),
    formatDateTime(pole.updated_at),
    formatUserName(pole.updated_by),
    pole.photo_url ?? "",
    pole.notes ?? "",
  ]);

  const html = `
    <html>
      <head>
        <meta charset="UTF-8" />
      </head>
      <body>
        <table border="1">
          <thead>
            <tr>${headers.map((header) => `<th>${escapeHtml(header)}</th>`).join("")}</tr>
          </thead>
          <tbody>
            ${rows
              .map((row) => `<tr>${row.map((cell) => `<td>${escapeHtml(String(cell))}</td>`).join("")}</tr>`)
              .join("")}
          </tbody>
        </table>
      </body>
    </html>
  `;

  const blob = new Blob([html], {
    type: "application/vnd.ms-excel;charset=utf-8",
  });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");

  link.href = url;
  link.download = `data-tiang-pju-${new Date().toISOString().slice(0, 10)}.xls`;
  document.body.appendChild(link);
  link.click();
  link.remove();
  URL.revokeObjectURL(url);
}

function escapeHtml(value: string) {
  return value
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function formatOptionLabel(value: string) {
  return value.replaceAll("_", " ");
}

function formatMeasurement(value?: number | null) {
  return value == null ? "-" : `${value} m`;
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

function formatOwnership(value: string) {
  const labels: Record<string, string> = {
    dpu_kp: "DPU KP",
    dishub: "DISHUB",
    pln: "PLN",
    lainnya: "Lainnya",
  };

  return labels[value] ?? value;
}
