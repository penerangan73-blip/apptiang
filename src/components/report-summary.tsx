import { Activity, AlertTriangle, CheckCircle2, Wrench } from "lucide-react";
import { StatCard } from "@/components/stat-card";
import type { DailyReport, Pole } from "@/lib/types";

type ReportSummaryProps = {
  poles: Pole[];
  reports: DailyReport[];
};

export function ReportSummary({ poles, reports }: ReportSummaryProps) {
  const today = new Date().toISOString().slice(0, 10);
  const todayReports = reports.filter((report) => report.report_date === today);

  const stats = [
    {
      label: "Didata hari ini",
      value: todayReports.length,
      description: "Aktivitas surveyor",
      icon: Activity,
      tone: "cyan" as const,
    },
    {
      label: "Kondisi baik",
      value: poles.filter((pole) => pole.condition === "baik").length,
      description: "Aset aktif normal",
      icon: CheckCircle2,
      tone: "emerald" as const,
    },
    {
      label: "Rusak",
      value: poles.filter((pole) => pole.condition === "rusak").length,
      description: "Butuh tindak lanjut",
      icon: AlertTriangle,
      tone: "rose" as const,
    },
    {
      label: "Perbaikan",
      value: poles.filter((pole) => pole.condition === "perbaikan").length,
      description: "Dalam penanganan",
      icon: Wrench,
      tone: "amber" as const,
    },
  ];

  return (
    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
      {stats.map((stat) => (
        <StatCard key={stat.label} {...stat} />
      ))}
    </div>
  );
}
